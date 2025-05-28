@extends('layouts.app')

@section('title', 'Login - Sistema de Pagamento')

@section('content')
<div class="flex justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <h1 class="text-center text-2xl font-bold text-gray-700 mb-6">Login</h1>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">E-mail</label>
                        <input id="email" type="email" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        
                        @error('email')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Senha</label>
                        <input id="password" type="password" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror" name="password" required autocomplete="current-password">
                        
                        @error('password')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center">
                            <input class="mr-2 leading-tight" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="text-sm text-gray-700" for="remember">
                                Lembrar de mim
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                            Entrar
                        </button>
                    </div>

                    @if (Route::has('password.request'))
                        <div class="text-center mt-4">
                            <a class="text-sm text-blue-600 hover:text-blue-800" href="{{ route('password.request') }}">
                                Esqueceu sua senha?
                            </a>
                        </div>
                    @endif
                </form>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="text-center text-sm">
                    Não tem uma conta? 
                    <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                        Cadastre-se
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
