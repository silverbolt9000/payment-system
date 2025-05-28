@extends('layouts.app')

@section('title', 'Cadastro - Sistema de Pagamento')

@section('content')
<div class="flex justify-center">
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <h1 class="text-center text-2xl font-bold text-gray-700 mb-6">Cadastre-se</h1>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nome Completo</label>
                        <input id="name" type="text" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                        
                        @error('name')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">E-mail</label>
                        <input id="email" type="email" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                        
                        @error('email')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="cpf_cnpj" class="block text-gray-700 text-sm font-bold mb-2">CPF/CNPJ</label>
                        <input id="cpf_cnpj" type="text" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('cpf_cnpj') border-red-500 @enderror" name="cpf_cnpj" value="{{ old('cpf_cnpj') }}" required>
                        
                        @error('cpf_cnpj')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="user_type" class="block text-gray-700 text-sm font-bold mb-2">Tipo de Usuário</label>
                        <select id="user_type" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('user_type') border-red-500 @enderror" name="user_type" required>
                            <option value="">Selecione...</option>
                            <option value="common" {{ old('user_type') == 'common' ? 'selected' : '' }}>Usuário Comum</option>
                            <option value="shopkeeper" {{ old('user_type') == 'shopkeeper' ? 'selected' : '' }}>Lojista</option>
                        </select>
                        
                        @error('user_type')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Senha</label>
                        <input id="password" type="password" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror" name="password" required autocomplete="new-password">
                        
                        @error('password')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="password-confirm" class="block text-gray-700 text-sm font-bold mb-2">Confirme a Senha</label>
                        <input id="password-confirm" type="password" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="password_confirmation" required autocomplete="new-password">
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                            Cadastrar
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="text-center text-sm">
                    Já tem uma conta? 
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                        Faça login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
