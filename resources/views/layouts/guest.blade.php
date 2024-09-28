<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, viewport-fit=cover">
    <title>@yield('title', 'おさかなハぅマっチ？')</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/Logo2.webp') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('img/Logo2.webp') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e88e5',
                        'primary-light': '#6ab7ff',
                        'primary-dark': '#005cb2',
                        'text-color': '#333333',
                        'background-color': '#f5f5f5',
                        'card-background': '#ffffff',
                        'input-border': '#bbdefb',
                    },
                },
                fontFamily: {
                    'sans': ['Noto Sans JP', 'sans-serif'],
                    'serif': ['Times New Roman', 'Times', 'serif'],
                },
            },
        }
    </script>
</head>
<body class="font-sans bg-background-color text-text-color min-h-screen flex flex-col m-0 p-0">
    <header class="bg-gradient-to-r from-primary to-primary-dark text-white p-3 text-center sticky top-0 z-50 shadow-md">
        <div class="logo flex items-center justify-center">
            <img src="{{ asset('img/Logo.png') }}" alt="ロゴ" class="w-10 h-10 mr-2 object-contain">
            <span class="text-xl font-bold">おさかなハぅマっチ？</span>
        </div>
    </header>

    <main class="flex-grow flex flex-col items-center justify-center p-5">
        @yield('content')
    </main>

    <footer class="bg-gradient-to-r from-primary to-primary-dark text-white text-center py-4 text-sm">
        &copy; {{ date('Y') }} Osakana How much？ All rights reserved.
    </footer>

    @stack('scripts')
</body>
</html>
