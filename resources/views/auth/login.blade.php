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
            <x-text-input id="email" class="w-full p-3 border-2 border-input-border rounded-lg text-base transition-all duration-300 
            ease-in-out focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50" 
                            type="email" 
                            name="email" 
                            :value="old('email')" 
                            required autofocus autocomplete="username" 
                            placeholder="メールアドレス"/>
        </div>
        <!-- Password -->
        <div class="form-group w-full mb-4">
            <x-text-input id="password" class="w-full p-3 border-2 border-input-border rounded-lg text-base transition-all duration-300 
            ease-in-out focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50"
                            type="password"
                            name="password"
                            required autocomplete="current-password" 
                            placeholder="パスワード" />
            <x-input-error :messages="$errors->get('email')" class="error text-red-500 mt-2 mb-2" />                
            <x-input-error :messages="$errors->get('password')" class="error text-red-500 mt-2 mb-2" />
        </div>
        <button class="btn bg-gradient-to-r from-primary to-primary-dark text-white border-none py-3 px-4 rounded-lg cursor-pointer 
        text-base font-bold w-full max-w-xs transition-all duration-300 ease-in-out shadow-md hover:from-primary-light hover:to-primary 
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

    <!-- LINE Login Button -->
    <a href="{{route('linelogin')}}" 
        class="flex items-center justify-center w-full max-w-xs bg-[#06C755] text-white py-3 px-4 rounded-lg mt-4 transition-all duration-300 ease-in-out 
            hover:bg-[#06C755] hover:bg-opacity-90 
            active:bg-[#06C755] active:bg-opacity-70">
        <svg class="w-6 h-6 mr-2" viewBox="0 0 24 24" fill="currentColor">
            <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.348 0 .63.285.63.63 0 .349-.282.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
        </svg>
        <span class="text-base font-semibold">LINEでログイン</span>
    </a>
    
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