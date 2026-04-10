<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-slate-900">Acessar o sistema</h2>
        <p class="mt-2 text-sm text-slate-600">
            Entre com suas credenciais para continuar no painel.
        </p>
    </div>

    <x-auth-session-status class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-600" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-600" />
        </div>

        <div class="flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center gap-2">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="text-sm text-gray-600">Lembrar-me</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-blue-700 transition hover:text-blue-800" href="{{ route('password.request') }}">
                    Esqueci minha senha
                </a>
            @endif
        </div>

        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
            Entrar
        </button>
    </form>
</x-guest-layout>
