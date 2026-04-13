<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Sistema') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <main class="mx-auto flex min-h-screen max-w-3xl flex-col items-center justify-center px-6 py-10 text-center">
            <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                {{ config('app.name', 'Sistema') }}
            </h1>
            <p class="mt-3 text-sm text-slate-600 sm:text-base">
                Acesse sua conta para continuar.
            </p>

            @if (Route::has('login'))
                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Ir para o painel
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Entrar
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                Cadastrar
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </main>
    </body>
</html>
