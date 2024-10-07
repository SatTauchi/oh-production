<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('settings', compact('users'));
    }

    public function toggleLife(User $user)
    {
        $user->life_flg = $user->life_flg == 0 ? 1 : 0;
        $user->save();

        $message = $user->life_flg == 0 ? 'ユーザーアカウントが復帰しました。' : 'ユーザーアカウントが退会処理されました。';
        return back()->with('success', $message);
    }

    public function toggleAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', '自分自身の管理者権限は変更できません。');
        }

        if ($user->admin_flg == 1 && User::where('admin_flg', 1)->count() <= 1) {
            return back()->with('error', '最後の管理者の権限は解除できません。');
        }

        $user->admin_flg = $user->admin_flg == 0 ? 1 : 0;
        $user->save();

        return back()->with('success', 'ユーザーの管理者権限が更新されました。');
    }
}