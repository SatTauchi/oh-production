@extends('layouts.guest')

@section('title', 'おさかなハぅマっチ？ - パスワード再設定')

@section('content')
    <div id="top_image" class="w-4/5 max-w-xs text-center pb-5">
        <img id="top" src="{{ asset('img/Logo.png') }}" alt="" class="w-full h-auto">
    </div>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('パスワードをお忘れの方は、メールアドレスを入力してください') }}<br>
        {{ __('パスワードリセット用のリンクをメールでお送りします。') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form class="password-reset flex flex-col items-center w-full max-w-xs" method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-group w-full mb-4">
            <x-text-input id="email" class="w-full p-4 border-2 border-input-border rounded-lg text-base transition-all duration-300 
            ease-in-out focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50" 
                            type="email" 
                            name="email" 
                            :value="old('email')" 
                            required autofocus
                            placeholder="メールアドレス"/>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button class="btn bg-gradient-to-r from-primary to-primary-dark text-white border-none py-4 px-5 rounded-lg cursor-pointer 
        text-lg font-bold w-full max-w-xs transition-all duration-300 ease-in-out shadow-md hover:from-primary-light hover:to-primary 
        hover:transform hover:-translate-y-0.5 hover:shadow-lg active:transform active:translate-y-0.5" type="submit">
            {{ __('パスワードリセットリンクを送信') }}
        </button>
    </form>

    <div class="flex items-center justify-center mt-4">
        <a class="text-sm text-blue-600 hover:text-blue-800 hover:underline transition-colors duration-200 rounded-md 
        focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" href="{{ route('login') }}">
            {{ __('ログイン画面に戻る') }}
        </a>
    </div>
@endsection