@extends('layouts.guest')

@section('title', 'おさかなハぅマっチ？ - ログイン')

@section('content')
    <div id="top_image" class="w-4/5 max-w-xs text-center pb-5">
        <img id="top" src="{{ asset('img/Logo.png') }}" alt="" class="w-full h-auto">
    </div>
    <form class="login flex flex-col items-center w-full max-w-xs" name="form1" action="{{ route('login') }}" method="post">
        @csrf
        <!-- Email Address -->
        <div class="form-group w-full mb-4">
            <x-text-input id="email" class="w-full p-4 border-2 border-input-border rounded-lg text-base transition-all duration-300 
            ease-in-out focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50" 
                            type="email" 
                            name="email" 
                            :value="old('email')" 
                            required autofocus autocomplete="username" 
                            placeholder="メールアドレス"/>
        </div>
        <!-- Password -->
        <div class="form-group w-full mb-4">
            <x-text-input id="password" class="w-full p-4 border-2 border-input-border rounded-lg text-base transition-all duration-300 
            ease-in-out focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50"
                            type="password"
                            name="password"
                            required autocomplete="current-password" 
                            placeholder="パスワード" />
            <x-input-error :messages="$errors->get('email')" class="error text-red-500 mt-2 mb-2" />                
            <x-input-error :messages="$errors->get('password')" class="error text-red-500 mt-2 mb-2" />
        </div>
        <button class="btn bg-gradient-to-r from-primary to-primary-dark text-white border-none py-4 px-5 rounded-lg cursor-pointer 
        text-lg font-bold w-full max-w-xs transition-all duration-300 ease-in-out shadow-md hover:from-primary-light hover:to-primary 
        hover:transform hover:-translate-y-0.5 hover:shadow-lg active:transform active:translate-y-0.5" type="submit">ログイン</button>
        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="text-sm text-blue-600 hover:text-blue-800 hover:underline transition-colors duration-200 rounded-md 
                focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" href="{{ route('password.request') }}">
                    {{ __('パスワードを忘れた方はこちら') }}
                </a>
            @endif
        </div>
    </form> 
    <div class="flex items-center justify-center mt-4">
        <p class="text-sm">はじめての方はこちら</p>
        <a class="text-sm text-blue-600 hover:text-blue-800 hover:underline transition-colors duration-200 rounded-md 
        focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" href="{{ route('register') }}">
            {{ __('今すぐアカウント作成') }}
        </a>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/fish_price_checker01.js') }}"></script>
@endpush
