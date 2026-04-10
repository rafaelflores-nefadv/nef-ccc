@extends('layouts.app')

@section('title', 'Redefinir Senha')

@section('content')
    <div class="py-2">
        <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Redefinir senha de usuário</h2>
                <p class="mt-1 text-sm text-slate-600">
                    Usuário: <strong>{{ $usuario->name }}</strong><br>
                    E-mail: <strong>{{ $usuario->email }}</strong>
                </p>
            </div>

            <div class="rounded-xl bg-white shadow-sm">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('usuarios.senha.update', $usuario) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Nova senha</label>
                            <input type="password" name="password" id="password" minlength="6" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar nova senha</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" minlength="6" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="pt-2">
                            <button type="submit" data-loading-text="Salvando..." class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                Salvar nova senha
                            </button>
                            <a href="{{ route('usuarios.index') }}" class="ml-3 text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
