<!DOCTYPE html>
<html lang="ja">
<meta name="csrf-token" content="{{ csrf_token() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'おさかなハぅマっチ？')</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/Logo2.webp') }}" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e88e5',
                        'primary-light': '#6ab7ff',
                        'primary-dark': '#005cb2',
                        secondary: '#64b5f6',
                        'text-color': '#333333',
                        'background-color': '#f5f5f5',
                        'card-background': '#ffffff',
                        'input-border': '#bbdefb',
                    },
                    fontFamily: {
                        sans: ['Noto Sans JP', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    @yield('additional_styles')
</head>

<body class="font-sans bg-background-color text-text-color min-h-screen flex flex-col">
    <header class="bg-gradient-to-r from-primary to-primary-dark text-white p-3 sm:p-5 sticky top-0 z-50 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex-1">
                <!-- Empty div for layout balance -->
            </div>
            <div class="flex items-center justify-center flex-grow">
                <img src="{{ asset('img/Logo.png') }}" alt="ロゴ" class="w-8 h-8 sm:w-10 sm:h-10 mr-2 object-contain">
                <span class="text-lg sm:text-xl font-bold whitespace-nowrap">おさかなハぅマっチ？</span>
            </div>
            <div class="flex-1 flex justify-end">
                <div class="hamburger-menu relative">
                    <input type="checkbox" id="menu-btn-check" class="hidden">
                    <label for="menu-btn-check" class="menu-btn cursor-pointer flex flex-col justify-center items-center w-8 h-8 sm:w-10 sm:h-10">
                        <span class="block w-5 sm:w-6 h-0.5 bg-white mb-1"></span>
                        <span class="block w-5 sm:w-6 h-0.5 bg-white mb-1"></span>
                        <span class="block w-5 sm:w-6 h-0.5 bg-white"></span>
                    </label>
                    <div class="menu-content hidden absolute right-0 top-full mt-2 w-48 bg-white rounded-md shadow-lg">
                        <ul class="py-2">
                            @auth
                            <li>
                                <p class="user_name px-4 py-2 text-primary-light font-bold">{{ Auth::user()->name }}</p>
                            </li>
                            <li>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fa-solid fa-user-gear mr-2"></i>ユーザー設定
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a href="{{ route('logout') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                        <i class="fas fa-arrow-right-from-bracket mr-2"></i>ログアウト
                                    </a>
                                </form>
                            </li>
                            @else
                            <li>
                                <a href="{{ route('login') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">ログイン</a>
                            </li>
                            <li>
                                <a href="{{ route('register') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">新規登録</a>
                            </li>
                            @endauth
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    @auth
        <nav class="bg-primary-light py-2 sticky top-14 sm:top-20 z-40">
            <div class="container mx-auto flex justify-around">
                <a href="{{ route('dashboard') }}" class="nav-item text-white opacity-80 hover:opacity-100 transition-opacity duration-300">
                    <i class="fas fa-home mr-1"></i> ホーム
                </a>
                @if(Auth::user()->admin_flg == 0)
                <a href="{{ route('fish_price.create') }}" class="nav-item text-white opacity-80 hover:opacity-100 transition-opacity duration-300">
                    <i class="fas fa-edit mr-1"></i> 入力
                </a>
                @endif
                <a href="{{ route('data.analysis') }}" class="nav-item text-white opacity-80 hover:opacity-100 transition-opacity duration-300">
                    <i class="fas fa-chart-line mr-1"></i> 分析
                </a>
                <a href="{{ route('data.list') }}" class="nav-item text-white opacity-80 hover:opacity-100 transition-opacity duration-300">
                    <i class="fas fa-database mr-1"></i> データ
                </a>
                @if(Auth::user()->admin_flg == 1)
                    <a href="{{ route('settings.index') }}" class="nav-item text-white opacity-80 hover:opacity-100 transition-opacity duration-300">
                        <i class="fas fa-cog mr-1"></i> 設定
                    </a>
                @endif
            </div>
        </nav>
    @endauth

    <main class="flex-grow container mx-auto px-4 py-8">
        @include('flash::message')
        @yield('content')
    </main>

    <footer class="bg-gradient-to-r from-primary to-primary-dark text-white text-center py-4 mt-auto">
        &copy; {{ date('Y') }} Osakana How much？ All rights reserved.
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @yield('additional_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuBtn = document.getElementById('menu-btn-check');
            const menuContent = document.querySelector('.menu-content');

            menuBtn.addEventListener('change', function() {
                if (this.checked) {
                    menuContent.classList.remove('hidden');
                } else {
                    menuContent.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>