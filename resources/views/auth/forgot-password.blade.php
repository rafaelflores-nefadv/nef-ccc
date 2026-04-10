<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-slate-900">Recuperar senha</h2>
        <p class="mt-2 text-sm text-slate-600">
            Esqueceu sua senha? Informe seu e-mail para enviarmos um link de redefinição.
        </p>
    </div>

    <x-auth-session-status class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
            <input
                id="email"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-600" />
        </div>

        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">
                Voltar ao login
            </a>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                Enviar link de redefinição
            </button>
        </div>
    </form>
</x-guest-layout>
