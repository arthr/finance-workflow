@extends('layouts.app')

@section('title', 'Webhooks')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Webhooks</h1>
        <a href="{{ route('webhooks.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition flex items-center">
            <i class="fas fa-plus mr-2"></i> Novo Webhook
        </a>
    </div>

    <!-- Filtros -->
    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
        <form action="{{ route('webhooks.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <div class="flex">
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nome ou URL" class="w-full rounded-l-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-r-md hover:bg-indigo-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <div class="w-40">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 h-[42px]"
                    onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Ativos</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inativos</option>
                </select>
            </div>

            <div class="w-64">
                <label for="workflow_id" class="block text-sm font-medium text-gray-700 mb-1">Workflow</label>
                <select name="workflow_id" id="workflow_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 h-[42px]"
                    onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @foreach($workflows as $workflow)
                    <option value="{{ $workflow->id }}" {{ request('workflow_id') == $workflow->id ? 'selected' : '' }}>
                        {{ $workflow->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="w-64">
                <label for="event" class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
                <select name="event" id="event"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 h-[42px]"
                    onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="process.created" {{ request('event') == 'process.created' ? 'selected' : '' }}>Processo Criado</option>
                    <option value="process.stage_changed" {{ request('event') == 'process.stage_changed' ? 'selected' : '' }}>Mudança de Estágio</option>
                    <option value="process.completed" {{ request('event') == 'process.completed' ? 'selected' : '' }}>Processo Concluído</option>
                    <option value="process.comment_added" {{ request('event') == 'process.comment_added' ? 'selected' : '' }}>Comentário Adicionado</option>
                    <option value="process.responsible_changed" {{ request('event') == 'process.responsible_changed' ? 'selected' : '' }}>Responsável Alterado</option>
                    <option value="process.attachment_added" {{ request('event') == 'process.attachment_added' ? 'selected' : '' }}>Anexo Adicionado</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Resultados -->
    @if (count($webhooks) > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ID
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nome
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        URL
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Eventos
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Workflow
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Última Execução
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($webhooks as $webhook)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        #{{ $webhook->id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $webhook->name }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                        <a href="{{ $webhook->url }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                            {{ $webhook->url }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @foreach($webhook->events as $event)
                        @php
                        $eventClass = '';
                        $eventText = '';
                        switch($event) {
                        case 'process.created':
                        $eventClass = 'bg-green-100 text-green-800';
                        $eventText = 'Criado';
                        break;
                        case 'process.stage_changed':
                        $eventClass = 'bg-blue-100 text-blue-800';
                        $eventText = 'Estágio';
                        break;
                        case 'process.completed':
                        $eventClass = 'bg-purple-100 text-purple-800';
                        $eventText = 'Concluído';
                        break;
                        case 'process.comment_added':
                        $eventClass = 'bg-yellow-100 text-yellow-800';
                        $eventText = 'Comentário';
                        break;
                        case 'process.responsible_changed':
                        $eventClass = 'bg-indigo-100 text-indigo-800';
                        $eventText = 'Responsável';
                        break;
                        case 'process.attachment_added':
                        $eventClass = 'bg-pink-100 text-pink-800';
                        $eventText = 'Anexo';
                        break;
                        default:
                        $eventClass = 'bg-gray-100 text-gray-800';
                        $eventText = $event;
                        }
                        @endphp
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $eventClass }} mr-1 mb-1">
                            {{ $eventText }}
                        </span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($webhook->workflow)
                        <a href="{{ route('workflows.show', $webhook->workflow->id) }}" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                            {{ $webhook->workflow->name }}
                        </a>
                        @else
                        <span class="text-gray-500">Todos</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if ($webhook->is_active)
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Ativo
                        </span>
                        @else
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Inativo
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @php
                        $lastLog = $webhook->logs()->latest()->first();
                        @endphp
                        @if($lastLog)
                        <span class="text-xs {{ $lastLog->success ? 'text-green-600' : 'text-red-600' }}">
                            {{ $lastLog->created_at->diffForHumans() }}
                            ({{ $lastLog->status_code }})
                        </span>
                        @else
                        <span class="text-xs text-gray-500">Nunca executado</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('webhooks.show', $webhook->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Visualizar Logs">
                            <i class="fas fa-history"></i>
                        </a>
                        <a href="{{ route('webhooks.edit', $webhook->id) }}" class="text-yellow-600 hover:text-yellow-900 mr-3" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('webhooks.destroy', $webhook->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este webhook?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div class="mt-4">
        {{ $webhooks->links() }}
    </div>

    @else
    <div class="text-center py-10 bg-gray-50 rounded-lg">
        <i class="fas fa-link text-gray-300 text-5xl mb-3"></i>
        <p class="text-gray-500 mb-3">Nenhum webhook encontrado.</p>
        <a href="{{ route('webhooks.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 inline-flex items-center">
            <i class="fas fa-plus mr-2"></i> Criar Novo Webhook
        </a>
    </div>
    @endif
</div>
@endsection
