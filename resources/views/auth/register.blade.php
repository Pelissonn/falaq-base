@extends('layouts.tailwind')

@section('title', 'Cadastrar — FalaQ')

@section('content')
<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Criar conta</h1>

    <form action="{{ route('register') }}" method="POST" novalidate>
        @csrf

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium mb-1">Nome</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}"
                   class="w-full border rounded-md p-2 @error('name') border-red-500 @else border-gray-300 @enderror">
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium mb-1">E-mail</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                   class="w-full border rounded-md p-2 @error('email') border-red-500 @else border-gray-300 @enderror">
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium mb-1">Senha</label>
            <input type="password" name="password" id="password"
                   class="w-full border rounded-md p-2 @error('password') border-red-500 @else border-gray-300 @enderror">
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirmar senha</label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="w-full border border-gray-300 rounded-md p-2">
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700">
            Cadastrar
        </button>
    </form>

    <p class="text-sm text-gray-600 mt-4">
        Já tem conta?
        <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Entrar</a>
    </p>
</div>
@endsection
