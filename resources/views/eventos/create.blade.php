@extends('layouts.tailwind')

@section('title', 'Novo evento — FalaQ')

@section('content')
{{-- Sprint 4 · Ticket #007: formulário de criação de eventos com Tailwind --}}
<div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Criar novo evento</h1>

    <form action="{{ route('eventos.store') }}" method="POST" novalidate>
        @csrf

        {{-- Sprint 4 · Ticket #008: erros com @error, old() e borda vermelha condicional --}}
        <div class="mb-4">
            <label for="titulo" class="block text-sm font-medium mb-1">Título</label>
            <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}"
                   class="w-full border rounded-md p-2 @error('titulo') border-red-500 @else border-gray-300 @enderror"
                   placeholder="Ex.: Palestra sobre computação em nuvem">
            @error('titulo')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="descricao" class="block text-sm font-medium mb-1">Descrição</label>
            <textarea name="descricao" id="descricao" rows="5"
                      class="w-full border rounded-md p-2 @error('descricao') border-red-500 @else border-gray-300 @enderror"
                      placeholder="Conte do que se trata o evento">{{ old('descricao') }}</textarea>
            @error('descricao')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-800">
            Criar evento
        </button>
    </form>
</div>
@endsection
