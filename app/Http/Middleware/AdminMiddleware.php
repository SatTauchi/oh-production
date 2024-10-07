<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->admin_flg == 1) {
            return $next($request);
        }

        return redirect('/')->with('error', '管理者権限が必要です。');
    }
}