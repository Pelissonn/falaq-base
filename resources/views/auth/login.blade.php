@extends('layouts.tailwind')

@section('title', 'Entrar — FalaQ')

@section('content')
<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Entrar</h1>

    <form action="{{ route('login') }}" method="POST" novalidate>
        @csrf

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

        <label class="flex items-center gap-2 text-sm mb-6">
            <input type="checkbox" name="remember" value="1"> Manter conectado
        </label>

        <button type="submit"
                class="w-full bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700">
            Entrar
        </button>
    </form>

    <p class="text-sm text-gray-600 mt-4">
        Ainda não tem conta?
        <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">Cadastre-se</a>
    </p>
</div>
@endsection
