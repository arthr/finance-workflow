<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Sistema de Workflow</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind CSS via CDN-->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @yield('styles')
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Cabeçalho -->
    <header class="bg-indigo-700 text-white shadow-md">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-2xl font-bold">
                        WorkflowSystem
                    </a>

                    @auth
                    <nav class="hidden md:flex space-x-4 ml-6">
                        <a href="{{ route('dashboard') }}" class="py-2 px-3 rounded hover:bg-indigo-600 {{ request()->routeIs('dashboard') ? 'bg-indigo-800' : '' }}">
                            <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                        </a>
                        <a href="{{ route('workflows.index') }}" class="py-2 px-3 rounded hover:bg-indigo-600 {{ request()->routeIs('workflows.*') ? 'bg-indigo-800' : '' }}">
                            <i class="fas fa-project-diagram mr-1"></i> Workflows
                        </a>
                        <a href="{{ route('processes.index') }}" class="py-2 px-3 rounded hover:bg-indigo-600 {{ request()->routeIs('processes.*') ? 'bg-indigo-800' : '' }}">
                            <i class="fas fa-tasks mr-1"></i> Processos
                        </a>
                        @can('view users')
                        <a href="{{ route('users.index') }}" class="py-2 px-3 rounded hover:bg-indigo-600 {{ request()->routeIs('users.*') ? 'bg-indigo-800' : '' }}">
                            <i class="fas fa-users mr-1"></i> Usuários
                        </a>
                        @endcan
                    </nav>
                    @endauth
                </div>

                <div class="flex items-center space-x-2">
                    @auth
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center py-2 px-3 rounded hover:bg-indigo-600 focus:outline-none">
                            <span class="mr-1">{{ Auth::user()->name }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>

                        <div x-show="open"
                            @click.away="open = false"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-indigo-100">
                                <i class="fas fa-user-circle mr-1"></i> Meu Perfil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-indigo-100">
                                    <i class="fas fa-sign-out-alt mr-1"></i> Sair
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <a href="{{ route('login') }}" class="py-2 px-3 rounded hover:bg-indigo-600">
                        <i class="fas fa-sign-in-alt mr-1"></i> Entrar
                    </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Menu móvel (aparece apenas em telas pequenas) -->
    @auth
    <div class="md:hidden bg-indigo-800 text-white">
        <div class="container mx-auto px-4 py-2">
            <div class="grid grid-cols-4 gap-1 text-center text-xs">
                <a href="{{ route('dashboard') }}" class="py-2 {{ request()->routeIs('dashboard') ? 'bg-indigo-900 rounded' : '' }}">
                    <i class="fas fa-tachometer-alt block text-lg mb-1"></i> Dashboard
                </a>
                <a href="{{ route('workflows.index') }}" class="py-2 {{ request()->routeIs('workflows.*') ? 'bg-indigo-900 rounded' : '' }}">
                    <i class="fas fa-project-diagram block text-lg mb-1"></i> Workflows
                </a>
                <a href="{{ route('processes.index') }}" class="py-2 {{ request()->routeIs('processes.*') ? 'bg-indigo-900 rounded' : '' }}">
                    <i class="fas fa-tasks block text-lg mb-1"></i> Processos
                </a>
                @can('view users')
                <a href="{{ route('users.index') }}" class="py-2 {{ request()->routeIs('users.*') ? 'bg-indigo-900 rounded' : '' }}">
                    <i class="fas fa-users block text-lg mb-1"></i> Usuários
                </a>
                @endcan
            </div>
        </div>
    </div>
    @endauth

    <!-- Alertas/Notificações -->
    @if(session('success') || session('error') || session('info') || session('warning') || $errors->any())
    <div class="container mx-auto px-4 py-2">
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
        @endif

        @if(session('info'))
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
            <p>{{ session('info') }}</p>
        </div>
        @endif

        @if(session('warning'))
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
            <p>{{ session('warning') }}</p>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    @endif

    <!-- Conteúdo principal -->
    <main class="flex-grow container mx-auto px-4 py-6">
        @yield('content')
    </main>

    <!-- Rodapé -->
    <footer class="bg-gray-800 text-white mt-auto">
        <div class="container mx-auto px-4 py-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-center md:text-left mb-4 md:mb-0">
                    <p>&copy; {{ date('Y') }} WorkflowSystem. Todos os direitos reservados.</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Versão 1.0.0</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Alpine.js via CDN para componentes interativos -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @yield('scripts')
    @stack('scripts')
</body>

</html>