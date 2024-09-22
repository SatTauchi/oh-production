<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LineLoginController extends Controller
{
    public function lineLogin()
    {
        $state = Str::random(32);
        $nonce  = Str::random(32);
      
        $uri = "https://access.line.me/oauth2/v2.1/authorize?";
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => config('services.line.client_id'),
            'redirect_uri' => config('services.line.redirect'),
            'state' => $state,
            'scope' => 'openid profile',
            'prompt' => 'consent',
            'nonce' => $nonce
        ]);

        return redirect($uri . $query);
    }

    public function getAccessToken($code)
    {
        $url = 'https://api.line.me/oauth2/v2.1/token';
        $post_data = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => config('services.line.redirect'),
            'client_id' => config('services.line.client_id'),
            'client_secret' => config('services.line.client_secret'),
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            Log::error('LINE access token request failed: ' . curl_error($ch));
            return null;
        }

        $json = json_decode($response);
        return $json->access_token ?? null;
    }

    public function getProfile($accessToken)
    {
        $url = 'https://api.line.me/v2/profile';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            Log::error('LINE profile request failed: ' . curl_error($ch));
            return null;
        }

        return json_decode($response);
    }

    public function callback(Request $request)
    {
        if (!$request->has('code')) {
            Log::error('LINE callback: No code received');
            return redirect('/login')->with('error', 'LINEログインに失敗しました。');
        }

        $accessToken = $this->getAccessToken($request->code);
        if (!$accessToken) {
            Log::error('LINE callback: Failed to get access token');
            return redirect('/login')->with('error', 'LINEログインに失敗しました。');
        }

        $profile = $this->getProfile($accessToken);
        if (!$profile) {
            Log::error('LINE callback: Failed to get profile');
            return redirect('/login')->with('error', 'LINEログインに失敗しました。');
        }

        $user = User::updateOrCreate(
            ['line_id' => $profile->userId],
            [
                'name' => $profile->displayName,
                'provider' => 'line'
            ]
        );

        Auth::login($user);
        return redirect('/dashboard'); // '/home' から '/dashboard' に変更
    }
}