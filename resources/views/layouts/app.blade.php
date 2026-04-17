<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $nomeSistema ?? config('app.name', 'Sistema'))</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script>
        (function() {
            const key = 'nf-theme-preference';
            const saved = localStorage.getItem(key);
            const theme = (saved === 'dark' || saved === 'light')
                ? saved
                : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            }

            document.documentElement.dataset.theme = theme;
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="theme-shell flex bg-slate-100 font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="nf-top-strip" aria-hidden="true"></div>
    @include('layouts.sidebar')

    <div class="flex-1 min-h-screen md:ml-64">
        @include('layouts.header')

        <main class="px-4 pb-6 pt-24 sm:px-6 lg:px-8">
            @include('layouts.feedback')
            @yield('content')
        </main>
    </div>
</body>
</html>
