@extends('layouts.app')

@section('title', 'Cadastro / Edição')

@section('content')
    <div class="py-2">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white shadow-sm">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('casos.store') }}">
                        @csrf
                        @include('casos._form', ['submitLabel' => 'Salvar caso'])
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
