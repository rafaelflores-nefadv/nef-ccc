<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-slate-900">Redefinir senha</h2>
        <p class="mt-2 text-sm text-slate-600">
            Informe seu e-mail e a nova senha para concluir a redefinição.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
            <input
                id="email"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-600" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Nova senha</label>
            <input
                id="password"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                type="password"
                name="password"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-600" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar nova senha</label>
            <input
                id="password_confirmation"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-rose-600" />
        </div>

        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">
                Voltar ao login
            </a>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                Redefinir senha
            </button>
        </div>
    </form>
</x-guest-layout>
