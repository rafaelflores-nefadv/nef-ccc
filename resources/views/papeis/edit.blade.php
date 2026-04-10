@extends('layouts.app')

@section('title', 'Papéis e Acessos')

@section('content')
    <div class="py-2">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white shadow-sm">
                <div class="p-6 text-gray-900">
                    <h2 class="mb-4 text-lg font-semibold text-slate-900">Editar papel: {{ $papel->nome }}</h2>

                    <form method="POST" action="{{ route('papeis.update', $papel) }}">
                        @csrf
                        @method('PUT')
                        @include('papeis._form', [
                            'submitLabel' => 'Atualizar papel',
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
