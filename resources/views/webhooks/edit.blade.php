@extends('layouts.app')

@section('title', 'Editar Webhook')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Editar Webhook</h1>
        <a href="{{ route('webhooks.index') }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Voltar para lista
        </a>
    </div>

    <form action="{{ route('webhooks.update', $webhook->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome do Webhook <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $webhook->name) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="Ex: Notificação de novo processo">
            </div>

            <div>
                <label for="url" class="block text-sm font-medium text-gray-700 mb-1">URL do Webhook <span class="text-red-500">*</span></label>
                <input type="url" name="url" id="url" value="{{ old('url', $webhook->url) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="https://exemplo.com/webhook">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="Descreva o propósito deste webhook...">{{ old('description', $webhook->description) }}</textarea>
            </div>

            <div>
                <label for="secret" class="block text-sm font-medium text-gray-700 mb-1">Secret (para assinatura HMAC)</label>
                <div class="relative">
                    <input type="text" name="secret" id="secret" value="{{ old('secret', $webhook->secret) }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <button type="button" id="generate-secret" class="absolute right-2 top-2 text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-key"></i>
                    </button>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Use este secret para verificar a autenticidade do webhook.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="workflow_id" class="block text-sm font-medium text-gray-700 mb-1">Workflow (opcional)</label>
                <select name="workflow_id" id="workflow_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Todos os workflows</option>
                    @foreach($workflows as $workflow)
                    <option value="{{ $workflow->id }}" {{ (old('workflow_id', $webhook->workflow_id) == $workflow->id) ? 'selected' : '' }}>
                        {{ $workflow->name }}
                    </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    Se selecionado, este webhook só será acionado para eventos deste workflow.
                </p>
            </div>

            <div>
                <label for="max_retries" class="block text-sm font-medium text-gray-700 mb-1">Máximo de tentativas</label>
                <input type="number" name="max_retries" id="max_retries" value="{{ old('max_retries', $webhook->max_retries) }}" min="0" max="10"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <p class="mt-1 text-xs text-gray-500">
                    Número máximo de tentativas em caso de falha (0-10).
                </p>
            </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <label class="block text-sm font-medium text-gray-700 mb-3">Eventos <span class="text-red-500">*</span></label>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="flex items-center">
                    <input type="checkbox" name="events[]" id="event_process_created" value="process.created" 
                        {{ (is_array(old('events', $webhook->events)) && in_array('process.created', old('events', $webhook->events))) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="event_process_created" class="ml-2 block text-sm text-gray-700">
                        Processo Criado
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="events[]" id="event_process_stage_changed" value="process.stage_changed" 
                        {{ (is_array(old('events', $webhook->events)) && in_array('process.stage_changed', old('events', $webhook->events))) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="event_process_stage_changed" class="ml-2 block text-sm text-gray-700">
                        Mudança de Estágio
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="events[]" id="event_process_completed" value="process.completed" 
                        {{ (is_array(old('events', $webhook->events)) && in_array('process.completed', old('events', $webhook->events))) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="event_process_completed" class="ml-2 block text-sm text-gray-700">
                        Processo Concluído
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="events[]" id="event_process_comment_added" value="process.comment_added" 
                        {{ (is_array(old('events', $webhook->events)) && in_array('process.comment_added', old('events', $webhook->events))) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="event_process_comment_added" class="ml-2 block text-sm text-gray-700">
                        Comentário Adicionado
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="events[]" id="event_process_responsible_changed" value="process.responsible_changed" 
                        {{ (is_array(old('events', $webhook->events)) && in_array('process.responsible_changed', old('events', $webhook->events))) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="event_process_responsible_changed" class="ml-2 block text-sm text-gray-700">
                        Responsável Alterado
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="events[]" id="event_process_attachment_added" value="process.attachment_added" 
                        {{ (is_array(old('events', $webhook->events)) && in_array('process.attachment_added', old('events', $webhook->events))) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="event_process_attachment_added" class="ml-2 block text-sm text-gray-700">
                        Anexo Adicionado
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="flex justify-between items-center mb-3">
                <label class="block text-sm font-medium text-gray-700">Cabeçalhos HTTP Personalizados</label>
                <button type="button" id="add-header" class="text-xs bg-indigo-600 text-white px-2 py-1 rounded hover:bg-indigo-700">
                    <i class="fas fa-plus mr-1"></i> Adicionar Cabeçalho
                </button>
            </div>
            
            <div id="headers-container" class="space-y-2">
                @if(is_array($webhook->headers) && count($webhook->headers) > 0)
                    @foreach($webhook->headers as $key => $value)
                    <div class="flex items-center space-x-2">
                        <div class="flex-1">
                            <input type="text" name="header_keys[]" value="{{ $key }}" placeholder="Nome do cabeçalho" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                        </div>
                        <div class="flex-1">
                            <input type="text" name="header_values[]" value="{{ $value }}" placeholder="Valor" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                        </div>
                        <button type="button" class="remove-header text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @endforeach
                @endif
            </div>
            
            <p class="mt-2 text-xs text-gray-500">
                Os cabeçalhos X-Webhook-Signature e Content-Type são adicionados automaticamente.
            </p>
        </div>

        <div class="flex items-center">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ $webhook->is_active ? 'checked' : '' }}
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <label for="is_active" class="ml-2 block text-sm text-gray-700">Webhook ativo</label>
        </div>

        <div class="flex justify-end pt-5 border-t border-gray-200">
            <a href="{{ route('webhooks.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                Cancelar
            </a>
            <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Atualizar Webhook
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const generateSecretBtn = document.getElementById('generate-secret');
        const secretInput = document.getElementById('secret');
        const addHeaderBtn = document.getElementById('add-header');
        const headersContainer = document.getElementById('headers-container');
        
        // Adicionar evento para remover cabeçalhos existentes
        const removeButtons = document.querySelectorAll('.remove-header');
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.flex.items-center').remove();
            });
        });

        // Gerar um secret aleatório
        generateSecretBtn.addEventListener('click', function() {
            const randomSecret = Array(32).fill(0).map(() => Math.random().toString(36).charAt(2)).join('');
            secretInput.value = randomSecret;
        });

        // Adicionar um novo cabeçalho
        addHeaderBtn.addEventListener('click', function() {
            const headerRow = document.createElement('div');
            headerRow.className = 'flex items-center space-x-2';
            headerRow.innerHTML = `
                <div class="flex-1">
                    <input type="text" name="header_keys[]" placeholder="Nome do cabeçalho" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                </div>
                <div class="flex-1">
                    <input type="text" name="header_values[]" placeholder="Valor" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                </div>
                <button type="button" class="remove-header text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            `;
            headersContainer.appendChild(headerRow);
            
            // Adicionar evento para remover cabeçalho
            headerRow.querySelector('.remove-header').addEventListener('click', function() {
                headerRow.remove();
            });
        });

        // Verificação de eventos selecionados antes de enviar o formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const checkboxes = document.querySelectorAll('input[name="events[]"]:checked');
            if (checkboxes.length === 0) {
                e.preventDefault();
                alert('Por favor, selecione pelo menos um evento.');
            }
        });
    });
</script>
@endsection
