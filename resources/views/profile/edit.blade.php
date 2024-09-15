<!-- resources/views/profile/edit.blade.php -->

@extends('layouts.app')

@section('title', 'プロフィール編集 - おさかなハぅマっチ？')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-card-background rounded-3xl shadow-lg p-7 mb-7 transition-all duration-300 hover:shadow-xl">
        <h2 class="text-2xl font-bold mb-6 text-primary relative pb-2.5 after:content-[''] after:absolute after:left-0 after:bottom-0 after:w-12 after:h-0.75 after:bg-secondary after:rounded">
            プロフィール情報
        </h2>
        
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('PATCH')
            
            <div>
                <label for="name" class="block mb-2 font-bold">名前</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required autofocus
                       class="w-full p-3 border-2 border-input-border rounded-lg focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 transition duration-300">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block mb-2 font-bold">メールアドレス</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                       class="w-full p-3 border-2 border-input-border rounded-lg focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 transition duration-300">
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="bg-primary hover:bg-primary-dark text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                    更新
                </button>
            </div>
        </form>
    </div>

    <div class="bg-card-background rounded-3xl shadow-lg p-7 mb-7 transition-all duration-300 hover:shadow-xl">
        <h2 class="text-2xl font-bold mb-6 text-primary relative pb-2.5 after:content-[''] after:absolute after:left-0 after:bottom-0 after:w-12 after:h-0.75 after:bg-secondary after:rounded">
            パスワード変更
        </h2>
        
        <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block mb-2 font-bold">現在のパスワード</label>
                <input type="password" name="current_password" id="current_password" required
                       class="w-full p-3 border-2 border-input-border rounded-lg focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 transition duration-300">
                @error('current_password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block mb-2 font-bold">新しいパスワード</label>
                <input type="password" name="password" id="password" required
                       class="w-full p-3 border-2 border-input-border rounded-lg focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 transition duration-300">
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block mb-2 font-bold">新しいパスワード（確認）</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="w-full p-3 border-2 border-input-border rounded-lg focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 transition duration-300">
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="bg-primary hover:bg-primary-dark text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                    パスワードを変更
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
