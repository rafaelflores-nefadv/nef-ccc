<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-slate-900">Verificação de e-mail</h2>
        <p class="mt-2 text-sm text-slate-600">
            Antes de continuar, confirme seu e-mail clicando no link que enviamos. Se não recebeu, podemos reenviar.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700">
            Enviamos um novo link de verificação para o seu e-mail.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                    Reenviar e-mail de verificação
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">
                Sair
            </button>
        </form>
    </div>
</x-guest-layout>
