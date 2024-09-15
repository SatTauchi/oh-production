@extends('layouts.guest')

@section('title', 'おさかなハぅマっチ？ - 新規登録')

@section('content')
<div class="container mx-auto px-4 py-8 flex-grow">
    <div class="bg-white rounded-3xl shadow-lg p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <h2 class="text-2xl font-bold mb-6 text-primary relative pb-2 after:content-[''] after:absolute after:left-0 after:bottom-0 
        after:w-12 after:h-1 after:bg-secondary after:rounded">新規登録</h2>
            <form method="POST" action="{{ route('register') }}" class="w-full max-w-md">
            @csrf
            <!-- Name -->
            <div class="mb-4">
                <x-input-label class="block mb-2 text-sm font-bold text-gray-800" for="name" :value="__('お名前')" />
                <x-text-input id="name" class="w-full p-3 border-2 border-blue-100 rounded-lg text-base transition-all duration-300 
                focus:border-primary focus:ring focus:ring-blue-200 focus:ring-opacity-50 placeholder:text-xs" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="お名前を入力"/>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

        <!-- Email Address -->
            <div class="mb-4">
                <x-input-label class="block mb-2 text-sm font-bold text-gray-800" for="email" :value="__('Email')" />
                <x-text-input id="email" class="w-full p-3 border-2 border-blue-100 rounded-lg text-base transition-all duration-300 
                focus:border-primary focus:ring focus:ring-blue-200 focus:ring-opacity-50 placeholder:text-xs" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="Emailを入力"/>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

        <!-- Password -->
        <div class="mb-4">
            <x-input-label class="block mb-2 text-sm font-bold text-gray-800" for="password" :value="__('パスワード入力')" />
            <x-text-input id="password" class="w-full p-3 border-2 border-blue-100 rounded-lg text-base transition-all duration-300 
            focus:border-primary focus:ring focus:ring-blue-200 focus:ring-opacity-50 placeholder:text-xs" type="password" name="password" required autocomplete="new-password" placeholder="パスワードを入力"/>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <x-input-label class="block mb-2 text-sm font-bold text-gray-800" for="password_confirmation" :value="__('パスワード再入力')" />
            <x-text-input id="password_confirmation" class="w-full p-3 border-2 border-blue-100 rounded-lg text-base transition-all duration-300 
            focus:border-primary focus:ring focus:ring-blue-200 focus:ring-opacity-50 placeholder:text-xs" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="確認のためもう一度入力してください"/>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
@endsection