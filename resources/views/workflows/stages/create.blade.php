@extends('layouts.app')

@section('title', 'Adicionar Estágio')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Adicionar Estágio ao Workflow</h1>
        <a href="{{ route('workflows.show', $workflow->id) }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Voltar para workflow
        </a>
    </div>

    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
        <h2 class="text-lg font-medium text-gray-800 mb-2">Workflow: {{ $workflow->name }}</h2>
        <p class="text-gray-600">{{ $workflow->description }}</p>
        <div class="mt-2">
            <span class="text-sm text-gray-500">Estágios atuais:</span>
            <span class="font-medium">{{ $workflow->stages->count() }}</span>
        </div>
    </div>

    <form action="{{ route('workflows.stages.store', $workflow->id) }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome do Estágio <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="Ex: Análise Inicial">
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Estágio <span class="text-red-500">*</span></label>
                <select name="type" id="type" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="manual" {{ old('type') == 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="automatic" {{ old('type') == 'automatic' ? 'selected' : '' }}>Automático</option>
                    <option value="conditional" {{ old('type') == 'conditional' ? 'selected' : '' }}>Condicional</option>
                </select>
                <p class="mt-1 text-sm text-gray-500">
                    <span class="font-medium">Manual:</span> Requer ação do usuário para avançar
                    <br>
                    <span class="font-medium">Automático:</span> Avança automaticamente
                    <br>
                    <span class="font-medium">Condicional:</span> Avança baseado em condições
                </p>
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea name="description" id="description" rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                placeholder="Descreva o propósito deste estágio...">{{ old('description') }}</textarea>
        </div>

        <!-- Configurações condicionais baseadas no tipo -->
        <div id="config-manual" class="config-section hidden">
            <h3 class="text-lg font-medium text-gray-800 mb-3 pt-3 border-t border-gray-200">Configurações para Estágio Manual</h3>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="config[requires_comment]" id="requires_comment" value="1" {{ old('config.requires_comment') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="requires_comment" class="ml-2 block text-sm text-gray-700">Requer comentário para avançar</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="config[requires_attachment]" id="requires_attachment" value="1" {{ old('config.requires_attachment') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="requires_attachment" class="ml-2 block text-sm text-gray-700">Requer anexo para avançar</label>
                </div>
            </div>
        </div>

        <div id="config-automatic" class="config-section hidden">
            <h3 class="text-lg font-medium text-gray-800 mb-3 pt-3 border-t border-gray-200">Configurações para Estágio Automático</h3>
            <div class="space-y-4">
                <div>
                    <label for="config_delay" class="block text-sm font-medium text-gray-700 mb-1">Atraso (em minutos)</label>
                    <input type="number" name="config[delay]" id="config_delay" value="{{ old('config.delay', 0) }}" min="0"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <p class="mt-1 text-xs text-gray-500">Tempo de espera antes de avançar automaticamente (0 = imediato)</p>
                </div>
                <div>
                    <label for="config_action" class="block text-sm font-medium text-gray-700 mb-1">Ação</label>
                    <select name="config[action]" id="config_action"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="advance" {{ old('config.action') == 'advance' ? 'selected' : '' }}>Avançar para próximo estágio</option>
                        <option value="notify" {{ old('config.action') == 'notify' ? 'selected' : '' }}>Apenas notificar</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="config-conditional" class="config-section hidden">
            <h3 class="text-lg font-medium text-gray-800 mb-3 pt-3 border-t border-gray-200">Configurações para Estágio Condicional</h3>
            <div class="space-y-4">
                <div>
                    <label for="config_field" class="block text-sm font-medium text-gray-700 mb-1">Campo a ser avaliado</label>
                    <input type="text" name="config[field]" id="config_field" value="{{ old('config.field') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="Ex: status, valor, categoria">
                </div>
                <div>
                    <label for="config_operator" class="block text-sm font-medium text-gray-700 mb-1">Operador</label>
                    <select name="config[operator]" id="config_operator"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="equals" {{ old('config.operator') == 'equals' ? 'selected' : '' }}>Igual a</option>
                        <option value="not_equals" {{ old('config.operator') == 'not_equals' ? 'selected' : '' }}>Diferente de</option>
                        <option value="greater_than" {{ old('config.operator') == 'greater_than' ? 'selected' : '' }}>Maior que</option>
                        <option value="less_than" {{ old('config.operator') == 'less_than' ? 'selected' : '' }}>Menor que</option>
                        <option value="contains" {{ old('config.operator') == 'contains' ? 'selected' : '' }}>Contém</option>
                    </select>
                </div>
                <div>
                    <label for="config_value" class="block text-sm font-medium text-gray-700 mb-1">Valor para comparação</label>
                    <input type="text" name="config[value]" id="config_value" value="{{ old('config.value') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="Ex: aprovado, 1000, urgente">
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-5 border-t border-gray-200">
            <a href="{{ route('workflows.show', $workflow->id) }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                Cancelar
            </a>
            <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Adicionar Estágio
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const configSections = document.querySelectorAll('.config-section');

        function showRelevantConfig() {
            const selectedType = typeSelect.value;

            // Ocultar todas as seções
            configSections.forEach(section => section.classList.add('hidden'));

            // Mostrar apenas a seção relevante
            const relevantSection = document.getElementById(`config-${selectedType}`);
            if (relevantSection) {
                relevantSection.classList.remove('hidden');
            }
        }

        // Inicializar
        showRelevantConfig();

        // Atualizar quando o tipo mudar
        typeSelect.addEventListener('change', showRelevantConfig);
    });
</script>
@endsection