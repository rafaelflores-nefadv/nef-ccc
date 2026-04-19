<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $loginBranding['title'] ?? ($nomeSistema ?? config('app.name', 'Sistema')) }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="nf-auth-shell min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
        <div class="relative min-h-screen overflow-hidden">
            <div class="nf-auth-panel-bg pointer-events-none absolute inset-0 lg:w-[52%]"></div>
            <div class="nf-auth-glow-a pointer-events-none absolute -left-24 top-16 h-72 w-72 rounded-full blur-3xl"></div>
            <div class="nf-auth-glow-b pointer-events-none absolute left-[18%] top-[55%] h-72 w-72 rounded-full blur-3xl"></div>

            <div class="relative mx-auto grid min-h-screen w-full max-w-7xl items-center px-4 py-8 sm:px-8 lg:grid-cols-[1fr_minmax(420px,520px)] lg:gap-16 lg:px-12">
                <div class="hidden text-slate-100 lg:block">
                    <div class="max-w-md space-y-6">
                        <p class="inline-flex items-center rounded-full border border-slate-300/30 bg-slate-800/40 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">
                            {{ $loginBranding['badge_text'] ?? 'Sistema interno' }}
                        </p>
                        <h1 class="text-4xl font-semibold leading-tight text-white">
                            {{ $loginBranding['title'] ?? ($nomeSistema ?? config('app.name', 'Sistema')) }}
                        </h1>
                        <p class="text-base leading-relaxed text-slate-200">
                            {{ $loginBranding['description'] ?? 'Plataforma de gestao e acompanhamento com foco em produtividade, controle e seguranca de acesso.' }}
                        </p>
                    </div>
                </div>

                <div class="nf-auth-card w-full rounded-2xl border border-slate-200 bg-white/95 p-6 shadow-2xl backdrop-blur sm:p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
