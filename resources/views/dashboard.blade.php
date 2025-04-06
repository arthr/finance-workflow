@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total de Processos -->
        <div class="bg-blue-50 rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="rounded-full bg-blue-500 p-3 mr-4">
                    <i class="fas fa-clipboard-list text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total de Processos</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalProcesses }}</p>
                </div>
            </div>
        </div>

        <!-- Processos Atribuídos -->
        <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="rounded-full bg-green-500 p-3 mr-4">
                    <i class="fas fa-user-check text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Processos Atribuídos</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $assignedProcesses }}</p>
                </div>
            </div>
        </div>

        <!-- Workflows Ativos -->
        <div class="bg-purple-50 rounded-lg shadow p-4 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="rounded-full bg-purple-500 p-3 mr-4">
                    <i class="fas fa-project-diagram text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Workflows Ativos</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $activeWorkflows }}</p>
                </div>
            </div>
        </div>

        <!-- Processos Pendentes -->
        <div class="bg-yellow-50 rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="rounded-full bg-yellow-500 p-3 mr-4">
                    <i class="fas fa-clock text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Processos Pendentes</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingProcesses }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Processos Recentes -->
        <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200">
            <h2 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">
                <i class="fas fa-history mr-2"></i>Processos Recentes
            </h2>
            @if(count($recentProcesses) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentProcesses as $process)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">#{{ $process->id }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                <a href="{{ route('processes.show', $process->id) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ Str::limit($process->title, 30) }}
                                </a>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($process->current_stage)
                                        bg-green-100 text-green-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $process->current_stage ? $process->current_stage->name : 'Não iniciado' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $process->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-gray-500 italic py-4 text-center">Nenhum processo registrado ainda.</p>
            @endif
            <div class="mt-4 text-right">
                <a href="{{ route('processes.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Ver todos os processos <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Meus Processos -->
        <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200">
            <h2 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">
                <i class="fas fa-tasks mr-2"></i>Meus Processos
            </h2>
            @if(count($myProcesses) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workflow</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($myProcesses as $process)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">#{{ $process->id }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                <a href="{{ route('processes.show', $process->id) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ Str::limit($process->title, 30) }}
                                </a>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                {{ $process->workflow ? $process->workflow->name : 'N/A' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm">
                                <a href="{{ route('processes.show', $process->id) }}" class="text-blue-600 hover:text-blue-800 mr-3">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('processes.edit', $process->id) }}" class="text-yellow-600 hover:text-yellow-800">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-gray-500 italic py-4 text-center">Você não possui processos atribuídos.</p>
            @endif
            <div class="mt-4 text-right">
                <a href="{{ route('processes.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:shadow-outline-blue transition ease-in-out duration-150">
                    <i class="fas fa-plus mr-2"></i> Novo Processo
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
