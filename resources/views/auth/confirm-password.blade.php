<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-slate-900">Confirmar senha</h2>
        <p class="mt-2 text-sm text-slate-600">
            Esta é uma área protegida. Confirme sua senha para continuar.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
            <input
                id="password"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                type="password"
                name="password"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-600" />
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                Confirmar
            </button>
        </div>
    </form>
</x-guest-layout>
