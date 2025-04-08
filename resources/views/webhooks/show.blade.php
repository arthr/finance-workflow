@extends('layouts.app')

@section('title', 'Logs de Webhook')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Logs de Webhook</h1>
            <p class="text-gray-600">{{ $webhook->name }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('webhooks.edit', $webhook->id) }}" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition flex items-center">
                <i class="fas fa-edit mr-2"></i> Editar
            </a>
            <a href="{{ route('webhooks.index') }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </div>

    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">URL</h3>
                <p class="mt-1">
                    <a href="{{ $webhook->url }}" target="_blank" class="text-indigo-600 hover:underline flex items-center">
                        <span class="truncate">{{ $webhook->url }}</span>
                        <i class="fas fa-external-link-alt ml-1 text-xs"></i>
                    </a>
                </p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">Status</h3>
                <p class="mt-1">
                    @if($webhook->is_active)
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Ativo
                    </span>
                    @else
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                        Inativo
                    </span>
                    @endif
                </p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">Secret</h3>
                <p class="mt-1 flex items-center">
                    <span class="text-gray-800 font-mono text-sm truncate">{{ substr($webhook->secret, 0, 8) }}•••••••</span>
                    <button type="button" id="showSecret" class="ml-2 text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-eye"></i>
                    </button>
                    <span id="secretValue" class="hidden">{{ $webhook->secret }}</span>
                </p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">Workflow</h3>
                <p class="mt-1">
                    @if($webhook->workflow)
                    <a href="{{ route('workflows.show', $webhook->workflow->id) }}" class="text-indigo-600 hover:underline">
                        {{ $webhook->workflow->name }}
                    </a>
                    @else
                    <span class="text-gray-800">Todos os workflows</span>
                    @endif
                </p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">Máximo de Tentativas</h3>
                <p class="mt-1 text-gray-800">
                    {{ $webhook->max_retries ?: 'Padrão (3)' }}
                </p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">Eventos</h3>
                <div class="mt-1 flex flex-wrap gap-1">
                    @foreach($webhook->events as $event)
                    @php
                    $eventClass = '';
                    $eventText = '';
                    switch($event) {
                    case 'process.created':
                    $eventClass = 'bg-green-100 text-green-800';
                    $eventText = 'Processo Criado';
                    break;
                    case 'process.stage_changed':
                    $eventClass = 'bg-blue-100 text-blue-800';
                    $eventText = 'Mudança de Estágio';
                    break;
                    case 'process.completed':
                    $eventClass = 'bg-purple-100 text-purple-800';
                    $eventText = 'Processo Concluído';
                    break;
                    case 'process.comment_added':
                    $eventClass = 'bg-yellow-100 text-yellow-800';
                    $eventText = 'Comentário Adicionado';
                    break;
                    case 'process.responsible_changed':
                    $eventClass = 'bg-indigo-100 text-indigo-800';
                    $eventText = 'Responsável Alterado';
                    break;
                    case 'process.attachment_added':
                    $eventClass = 'bg-pink-100 text-pink-800';
                    $eventText = 'Anexo Adicionado';
                    break;
                    default:
                    $eventClass = 'bg-gray-100 text-gray-800';
                    $eventText = $event;
                    }
                    @endphp
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $eventClass }}">
                        {{ $eventText }}
                    </span>
                    @endforeach
                </div>
            </div>
        </div>

        @if(!empty($webhook->description))
        <div class="mt-4 border-t border-gray-200 pt-4">
            <h3 class="text-sm font-medium text-gray-500">Descrição</h3>
            <p class="mt-1 text-gray-800">{{ $webhook->description }}</p>
        </div>
        @endif

        @if(!empty($webhook->headers))
        <div class="mt-4 border-t border-gray-200 pt-4">
            <h3 class="text-sm font-medium text-gray-500">Cabeçalhos Personalizados</h3>
            <div class="mt-1 bg-gray-100 p-3 rounded font-mono text-xs">
                @foreach($webhook->headers as $key => $value)
                <div class="mb-1">
                    <span class="text-indigo-600">{{ $key }}</span>: <span class="text-gray-800">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Filtros de logs -->
    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
        <form action="{{ route('webhooks.show', $webhook->id) }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="event" class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
                <select name="event" id="event"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    onchange="this.form.submit()">
                    <option value="">Todos os eventos</option>
                    @foreach($webhook->events as $event)
                    @php
                    $eventText = '';
                    switch($event) {
                    case 'process.created':
                    $eventText = 'Processo Criado';
                    break;
                    case 'process.stage_changed':
                    $eventText = 'Mudança de Estágio';
                    break;
                    case 'process.completed':
                    $eventText = 'Processo Concluído';
                    break;
                    case 'process.comment_added':
                    $eventText = 'Comentário Adicionado';
                    break;
                    case 'process.responsible_changed':
                    $eventText = 'Responsável Alterado';
                    break;
                    case 'process.attachment_added':
                    $eventText = 'Anexo Adicionado';
                    break;
                    default:
                    $eventText = $event;
                    }
                    @endphp
                    <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                        {{ $eventText }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="w-48">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Sucesso</option>
                    <option value="error" {{ request('status') == 'error' ? 'selected' : '' }}>Erro</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Tabela de logs -->
    @if(count($logs) > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Data/Hora
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Evento
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Processo
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tentativa
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                        $eventClass = '';
                        $eventText = '';
                        switch($log->event) {
                        case 'process.created':
                        $eventClass = 'bg-green-100 text-green-800';
                        $eventText = 'Processo Criado';
                        break;
                        case 'process.stage_changed':
                        $eventClass = 'bg-blue-100 text-blue-800';
                        $eventText = 'Mudança de Estágio';
                        break;
                        case 'process.completed':
                        $eventClass = 'bg-purple-100 text-purple-800';
                        $eventText = 'Processo Concluído';
                        break;
                        case 'process.comment_added':
                        $eventClass = 'bg-yellow-100 text-yellow-800';
                        $eventText = 'Comentário Adicionado';
                        break;
                        case 'process.responsible_changed':
                        $eventClass = 'bg-indigo-100 text-indigo-800';
                        $eventText = 'Responsável Alterado';
                        break;
                        case 'process.attachment_added':
                        $eventClass = 'bg-pink-100 text-pink-800';
                        $eventText = 'Anexo Adicionado';
                        break;
                        default:
                        $eventClass = 'bg-gray-100 text-gray-800';
                        $eventText = $log->event;
                        }
                        @endphp
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $eventClass }}">
                            {{ $eventText }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($log->process_id)
                        <a href="{{ route('processes.show', $log->process_id) }}" class="text-indigo-600 hover:underline">
                            #{{ $log->process_id }}
                        </a>
                        @else
                        <span class="text-gray-400">N/A</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($log->success)
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Sucesso ({{ $log->status_code }})
                        </span>
                        @else
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Erro ({{ $log->status_code ?: 'Timeout' }})
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $log->attempt }} / {{ $webhook->max_retries ?: 3 }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button type="button" class="text-indigo-600 hover:text-indigo-900 view-log-details" data-log-id="{{ $log->id }}">
                            <i class="fas fa-search"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div class="mt-4">
        {{ $logs->links() }}
    </div>
    @else
    <div class="text-center py-10 bg-gray-50 rounded-lg">
        <i class="fas fa-history text-gray-300 text-5xl mb-3"></i>
        <p class="text-gray-500 mb-1">Nenhum log encontrado.</p>
        <p class="text-gray-400 text-sm">Os logs serão exibidos aqui quando o webhook for acionado.</p>
    </div>
    @endif

    <!-- Modal de detalhes do log -->
    <div id="logDetailsModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Detalhes do Log</h3>
                <button type="button" id="closeLogDetailsModal" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="px-6 py-4 overflow-y-auto max-h-[calc(90vh-8rem)]">
                <div class="space-y-4">
                    <!-- Status -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Status</h4>
                        <div id="logStatus"></div>
                    </div>

                    <!-- Request -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Request</h4>
                        <div class="bg-gray-50 p-3 rounded font-mono text-xs overflow-x-auto">
                            <div class="mb-2">
                                <span class="text-indigo-600">URL:</span> <span id="logUrl" class="break-all"></span>
                            </div>
                            <div class="mb-2">
                                <span class="text-indigo-600">Método:</span> <span id="logMethod"></span>
                            </div>
                            <div class="mb-2">
                                <span class="text-indigo-600">Headers:</span>
                                <pre id="logRequestHeaders" class="mt-1 whitespace-pre-wrap"></pre>
                            </div>
                            <div>
                                <span class="text-indigo-600">Payload:</span>
                                <pre id="logRequestBody" class="mt-1 whitespace-pre-wrap"></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Response -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Response</h4>
                        <div class="bg-gray-50 p-3 rounded font-mono text-xs overflow-x-auto">
                            <div class="mb-2">
                                <span class="text-indigo-600">Status:</span> <span id="logStatusCode"></span>
                            </div>
                            <div class="mb-2">
                                <span class="text-indigo-600">Headers:</span>
                                <pre id="logResponseHeaders" class="mt-1 whitespace-pre-wrap"></pre>
                            </div>
                            <div>
                                <span class="text-indigo-600">Body:</span>
                                <pre id="logResponseBody" class="mt-1 whitespace-pre-wrap"></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Error -->
                    <div id="logErrorContainer" class="hidden">
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Erro</h4>
                        <div class="bg-red-50 p-3 rounded font-mono text-xs text-red-800 overflow-x-auto">
                            <pre id="logError" class="whitespace-pre-wrap"></pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-gray-50 text-right">
                <button type="button" id="closeLogDetailsBtn" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 transition">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Botão para mostrar o secret
        const showSecretBtn = document.getElementById('showSecret');
        const secretValue = document.getElementById('secretValue');

        if (showSecretBtn) {
            showSecretBtn.addEventListener('click', function() {
                const secretText = secretValue.textContent;
                // Copiar para o clipboard
                navigator.clipboard.writeText(secretText).then(function() {
                    alert('Secret copiado para o clipboard!');
                });
            });
        }

        // Modal de detalhes do log
        const modal = document.getElementById('logDetailsModal');
        const closeModalBtn = document.getElementById('closeLogDetailsModal');
        const closeModalBtn2 = document.getElementById('closeLogDetailsBtn');
        const viewLogBtns = document.querySelectorAll('.view-log-details');

        viewLogBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const logId = this.getAttribute('data-log-id');
                fetchLogDetails(logId);
            });
        });

        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', function() {
                modal.classList.add('hidden');
            });
        }

        if (closeModalBtn2) {
            closeModalBtn2.addEventListener('click', function() {
                modal.classList.add('hidden');
            });
        }

        // Fechar modal ao clicar fora
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        });

        // Função para buscar detalhes do log
        function fetchLogDetails(logId) {
            fetch(`/api/webhook-logs/${logId}`)
                .then(response => response.json())
                .then(data => {
                    populateLogDetails(data);
                    modal.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Erro ao buscar detalhes do log:', error);
                    alert('Erro ao buscar detalhes do log. Por favor, tente novamente.');
                });
        }

        // Função para preencher os detalhes do log no modal
        function populateLogDetails(log) {
            // Status
            const logStatus = document.getElementById('logStatus');
            if (log.success) {
                logStatus.innerHTML = `<span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    Sucesso (${log.status_code})
                </span>`;
            } else {
                logStatus.innerHTML = `<span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                    Erro (${log.status_code || 'Timeout'})
                </span>`;
            }

            // Request
            document.getElementById('logUrl').textContent = log.url;
            document.getElementById('logMethod').textContent = log.method || 'POST';

            try {
                const requestHeaders = JSON.parse(log.request_headers || '{}');
                document.getElementById('logRequestHeaders').textContent = JSON.stringify(requestHeaders, null, 2);
            } catch (e) {
                document.getElementById('logRequestHeaders').textContent = log.request_headers || 'N/A';
            }

            try {
                const requestBody = JSON.parse(log.request_body || '{}');
                document.getElementById('logRequestBody').textContent = JSON.stringify(requestBody, null, 2);
            } catch (e) {
                document.getElementById('logRequestBody').textContent = log.request_body || 'N/A';
            }

            // Response
            document.getElementById('logStatusCode').textContent = log.status_code || 'N/A';

            try {
                const responseHeaders = JSON.parse(log.response_headers || '{}');
                document.getElementById('logResponseHeaders').textContent = JSON.stringify(responseHeaders, null, 2);
            } catch (e) {
                document.getElementById('logResponseHeaders').textContent = log.response_headers || 'N/A';
            }

            try {
                const responseBody = JSON.parse(log.response_body || '{}');
                document.getElementById('logResponseBody').textContent = JSON.stringify(responseBody, null, 2);
            } catch (e) {
                document.getElementById('logResponseBody').textContent = log.response_body || 'N/A';
            }

            // Erro
            const errorContainer = document.getElementById('logErrorContainer');
            const errorElement = document.getElementById('logError');

            if (log.error) {
                errorContainer.classList.remove('hidden');
                errorElement.textContent = log.error;
            } else {
                errorContainer.classList.add('hidden');
            }
        }
    });
</script>
@endsection
