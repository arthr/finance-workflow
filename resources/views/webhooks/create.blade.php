@extends('layouts.app')

@section('title', 'Criar Webhook')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Criar Novo Webhook</h1>
        <a href="{{ route('webhooks.index') }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Voltar para lista
        </a>
    </div>

    @if($workflowId)
    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
        <h2 class="text-lg font-medium text-gray-800 mb-2">Workflow: {{ $workflows->find($workflowId)->name }}</h2>
        <p class="text-gray-600">{{ $workflows->find($workflowId)->description }}</p>
        <p class="mt-2 text-sm text-gray-500">Este webhook será associado ao workflow atual.</p>
    </div>
    @endif

    <form action="{{ route('webhooks.store') }}" method="POST" class="space-y-6">
        @csrf

        @if($workflowId)
        <input type="hidden" name="workflow_id" value="{{ $workflowId }}">
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome do Webhook <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="Ex: Notificação de Mudança de Estágio">
            </div>

            <div>
                <label for="url" class="block text-sm font-medium text-gray-700 mb-1">URL do Webhook <span class="text-red-500">*</span></label>
                <input type="url" name="url" id="url" value="{{ old('url') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="https://example.com/webhook">
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea name="description" id="description" rows="2"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                placeholder="Descreva o propósito deste webhook...">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="secret" class="block text-sm font-medium text-gray-700 mb-1">Secret (para verificação de assinatura)</label>
                <div class="flex">
                    <input type="text" name="secret" id="secret" value="{{ old('secret', Str::random(32)) }}"
                        class="w-full rounded-l-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <button type="button" id="generateSecret" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-r-md hover:bg-gray-300">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <p class="mt-1 text-xs text-gray-500">Usado para verificar a autenticidade dos webhooks enviados</p>
            </div>

            @if(!$workflowId)
            <div>
                <label for="workflow_id" class="block text-sm font-medium text-gray-700 mb-1">Workflow (opcional)</label>
                <select name="workflow_id" id="workflow_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Todos os workflows</option>
                    @foreach($workflows as $workflow)
                    <option value="{{ $workflow->id }}" {{ old('workflow_id') == $workflow->id ? 'selected' : '' }}>
                        {{ $workflow->name }}
                    </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Se não selecionar, o webhook será disparado para todos os workflows</p>
            </div>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Eventos <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center p-3 rounded-md border border-gray-200 bg-white">
                    <input type="checkbox" name="events[]" id="event_created" value="process.created" {{ in_array('process.created', old('events', [])) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="event_created" class="ml-2 block text-sm text-gray-700">Processo Criado</label>
                </div>

                <div class="flex items-center p-3 rounded-md border border-gray-200 bg-white">
                    <input type="checkbox" name="events[]" id="event_stage_changed" value="process.stage_changed" {{ in_array('process.stage_changed', old('events', [])) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="event_stage_changed" class="ml-2 block text-sm text-gray-700">Mudança de Estágio</label>
                </div>

                <div class="flex items-center p-3 rounded-md border border-gray-200 bg-white">
                    <input type="checkbox" name="events[]" id="event_completed" value="process.completed" {{ in_array('process.completed', old('events', [])) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="event_completed" class="ml-2 block text-sm text-gray-700">Processo Concluído</label>
                </div>

                <div class="flex items-center p-3 rounded-md border border-gray-200 bg-white">
                    <input type="checkbox" name="events[]" id="event_comment_added" value="process.comment_added" {{ in_array('process.comment_added', old('events', [])) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="event_comment_added" class="ml-2 block text-sm text-gray-700">Comentário Adicionado</label>
                </div>

                <div class="flex items-center p-3 rounded-md border border-gray-200 bg-white">
                    <input type="checkbox" name="events[]" id="event_responsible_changed" value="process.responsible_changed" {{ in_array('process.responsible_changed', old('events', [])) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="event_responsible_changed" class="ml-2 block text-sm text-gray-700">Responsável Alterado</label>
                </div>

                <div class="flex items-center p-3 rounded-md border border-gray-200 bg-white">
                    <input type="checkbox" name="events[]" id="event_attachment_added" value="process.attachment_added" {{ in_array('process.attachment_added', old('events', [])) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="event_attachment_added" class="ml-2 block text-sm text-gray-700">Anexo Adicionado</label>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h3 class="text-lg font-medium text-gray-800 mb-3">Configurações avançadas</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="max_retries" class="block text-sm font-medium text-gray-700 mb-1">Número máximo de tentativas</label>
                    <input type="number" name="max_retries" id="max_retries" value="{{ old('max_retries', 3) }}" min="0" max="10"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <p class="mt-1 text-xs text-gray-500">Em caso de falha, quantas vezes tentar novamente (0-10)</p>
                </div>

                <div>
                    <label for="headers" class="block text-sm font-medium text-gray-700 mb-1">Cabeçalhos personalizados (JSON)</label>
                    <textarea name="headers" id="headers" rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder='{"X-Custom-Header": "Value"}'>{{ old('headers') }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">Webhook ativo</label>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-5 border-t border-gray-200">
            <a href="{{ route('webhooks.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                Cancelar
            </a>
            <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Criar Webhook
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const generateSecretBtn = document.getElementById('generateSecret');
        const secretInput = document.getElementById('secret');

        generateSecretBtn.addEventListener('click', function() {
            // Função para gerar string aleatória de 32 caracteres
            const generateRandomString = (length = 32) => {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                let result = '';
                for (let i = 0; i < length; i++) {
                    result += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return result;
            };

            secretInput.value = generateRandomString();
        });
    });
</script>
@endsection
