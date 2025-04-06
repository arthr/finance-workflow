@extends('layouts.app')

@section('title', 'Processos')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Processos</h1>
        <a href="{{ route('processes.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition flex items-center">
            <i class="fas fa-plus mr-2"></i> Novo Processo
        </a>
    </div>

    <!-- Filtros -->
    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
        <form action="{{ route('processes.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="workflow_id" class="block text-sm font-medium text-gray-700 mb-1">Workflow</label>
                <select name="workflow_id" id="workflow_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Todos</option>
                    @foreach ($workflows as $workflow)
                    <option value="{{ $workflow->id }}" {{ request('workflow_id') == $workflow->id ? 'selected' : '' }}>
                        {{ $workflow->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Todos</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Rascunho</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativo</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Concluído</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>

            <div>
                <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Atribuído a</label>
                <select name="assigned_to" id="assigned_to" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Todos</option>
                    <option value="{{ auth()->id() }}" {{ request('assigned_to') == auth()->id() ? 'selected' : '' }}>Meus Processos</option>
                    @foreach ($users as $user)
                    @if ($user->id != auth()->id())
                    <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <div class="flex">
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Título ou descrição" class="flex-1 rounded-l-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-r-md hover:bg-indigo-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Resultados -->
    @if (count($processes) > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ID
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Título
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Workflow
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estágio
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Responsável
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Criado em
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($processes as $process)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        #{{ $process->id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $process->title }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $process->workflow->name ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if ($process->current_stage)
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            {{ $process->current_stage->name }}
                        </span>
                        @else
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            Não iniciado
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $process->assignee->name ?? 'Não atribuído' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if ($process->status == 'draft') bg-gray-100 text-gray-800
                                    @elseif ($process->status == 'active') bg-blue-100 text-blue-800
                                    @elseif ($process->status == 'completed') bg-green-100 text-green-800
                                    @elseif ($process->status == 'cancelled') bg-red-100 text-red-800
                                    @endif">
                            @if ($process->status == 'draft') Rascunho
                            @elseif ($process->status == 'active') Ativo
                            @elseif ($process->status == 'completed') Concluído
                            @elseif ($process->status == 'cancelled') Cancelado
                            @else {{ $process->status }}
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $process->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('processes.show', $process->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('processes.edit', $process->id) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if ($process->status != 'completed' && $process->status != 'cancelled')
                        <form action="{{ route('processes.destroy', $process->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza que deseja excluir este processo?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $processes->links() }}
    </div>
    @else
    <div class="text-center py-10 bg-gray-50 rounded-lg">
        <i class="fas fa-clipboard-list text-gray-300 text-5xl mb-3"></i>
        <p class="text-gray-500 mb-3">Nenhum processo encontrado.</p>
        <a href="{{ route('processes.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 inline-flex items-center">
            <i class="fas fa-plus mr-2"></i> Criar Novo Processo
        </a>
    </div>
    @endif
</div>
@endsection
