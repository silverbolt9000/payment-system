<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Sistema de Pagamento')</title>
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    
    <!-- Alpine.js para interatividade -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tailwind CSS via CDN (para desenvolvimento rápido) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div id="app">
        <nav class="bg-blue-600 text-white shadow-lg">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center py-4">
                    <div>
                        <a href="{{ url('/') }}" class="text-xl font-bold">
                            Sistema de Pagamento
                        </a>
                    </div>
                    
                    <div class="hidden md:flex items-center space-x-4">
                        @guest
                            <a href="{{ route('login') }}" class="hover:text-blue-200">Login</a>
                            <a href="{{ route('register') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-100">Cadastre-se</a>
                        @else
                            <span class="mr-2">Olá, {{ Auth::user()->name }}</span>
                            <a href="{{ route('home') }}" class="hover:text-blue-200">Dashboard</a>
                            
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="flex items-center hover:text-blue-200">
                                    <span>Menu</span>
                                    <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                    <a href="{{ route('home') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</a>
                                    <a href="{{ route('transfers.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Nova Transferência</a>
                                    <hr class="my-1">
                                    <a href="{{ route('logout') }}" 
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Sair
                                    </a>
                                    
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                        @csrf
                                    </form>
                                </div>
                            </div>
                        @endguest
                    </div>
                    
                    <!-- Mobile menu button -->
                    <div class="md:hidden flex items-center">
                        <button class="mobile-menu-button" x-data="{open: false}" @click="open = !open; $dispatch('mobile-menu-toggle', {open})">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Mobile menu -->
            <div class="mobile-menu hidden md:hidden" x-data="{open: false}" @mobile-menu-toggle.window="open = $event.detail.open" x-show="open">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                    @guest
                        <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Login</a>
                        <a href="{{ route('register') }}" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Cadastre-se</a>
                    @else
                        <span class="block px-3 py-2">Olá, {{ Auth::user()->name }}</span>
                        <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Dashboard</a>
                        <a href="{{ route('transfers.create') }}" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Nova Transferência</a>
                        <a href="{{ route('logout') }}" 
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                           class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">
                            Sair
                        </a>
                    @endguest
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container mx-auto px-4">
                @yield('content')
            </div>
        </main>
        
        <footer class="bg-white py-4 mt-8 border-t">
            <div class="container mx-auto px-4">
                <div class="text-center text-gray-500 text-sm">
                    &copy; {{ date('Y') }} Sistema de Pagamento Simplificado. Todos os direitos reservados.
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Script para mobile menu -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.querySelector('.mobile-menu-button');
            const menu = document.querySelector('.mobile-menu');
            
            btn.addEventListener('click', function() {
                menu.classList.toggle('hidden');
            });
        });
    </script>
</body>
</html>
