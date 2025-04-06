@extends('layouts.app')

@section('title', 'Adicionar Transição')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Adicionar Transição</h1>
        <a href="{{ route('workflows.show', $workflow->id) }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Voltar para workflow
        </a>
    </div>

    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
        <h2 class="text-lg font-medium text-gray-800 mb-2">Workflow: {{ $workflow->name }}</h2>
        <p class="text-gray-600">{{ $workflow->description }}</p>
        <div class="mt-2 grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-500">Estágios:</span>
                <span class="font-medium">{{ $workflow->stages->count() }}</span>
            </div>
            <div>
                <span class="text-sm text-gray-500">Transições existentes:</span>
                <span class="font-medium">{{ $workflow->transitions->count() }}</span>
            </div>
        </div>
    </div>

    <form action="{{ route('workflows.transitions.store', $workflow->id) }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="from_stage_id" class="block text-sm font-medium text-gray-700 mb-1">Estágio de Origem <span class="text-red-500">*</span></label>
                <select name="from_stage_id" id="from_stage_id" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Selecione o estágio de origem</option>
                    @foreach ($workflow->stages as $stage)
                    <option value="{{ $stage->id }}" {{ old('from_stage_id') == $stage->id ? 'selected' : '' }}>
                        {{ $stage->name }} ({{ ucfirst($stage->type) }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="to_stage_id" class="block text-sm font-medium text-gray-700 mb-1">Estágio de Destino <span class="text-red-500">*</span></label>
                <select name="to_stage_id" id="to_stage_id" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Selecione o estágio de destino</option>
                    @foreach ($workflow->stages as $stage)
                    <option value="{{ $stage->id }}" {{ old('to_stage_id') == $stage->id ? 'selected' : '' }}>
                        {{ $stage->name }} ({{ ucfirst($stage->type) }})
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label for="trigger_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Gatilho <span class="text-red-500">*</span></label>
            <select name="trigger_type" id="trigger_type" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="manual" {{ old('trigger_type') == 'manual' ? 'selected' : '' }}>Manual</option>
                <option value="automatic" {{ old('trigger_type') == 'automatic' ? 'selected' : '' }}>Automático</option>
                <option value="scheduled" {{ old('trigger_type') == 'scheduled' ? 'selected' : '' }}>Agendado</option>
            </select>
            <p class="mt-1 text-sm text-gray-500">
                <span class="font-medium">Manual:</span> Requer ação do usuário para transitar
                <br>
                <span class="font-medium">Automático:</span> Transita automaticamente quando as condições são satisfeitas
                <br>
                <span class="font-medium">Agendado:</span> Transita após um período de tempo específico
            </p>
        </div>

        <!-- Configurações condicionais baseadas no tipo de gatilho -->
        <div id="condition-section" class="pt-5 border-t border-gray-200">
            <h3 class="text-lg font-medium text-gray-800 mb-3">Condições para Transição</h3>

            <div id="manual-conditions" class="condition-group">
                <p class="text-sm text-gray-500 mb-4">Para transições manuais, você pode definir permissões necessárias para realizar esta transição.</p>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Permissão Necessária</label>
                    <input type="text" name="condition[permission]" value="{{ old('condition.permission') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="Ex: approve_requests (deixe em branco para qualquer usuário)">
                </div>
            </div>

            <div id="automatic-conditions" class="condition-group hidden">
                <p class="text-sm text-gray-500 mb-4">Para transições automáticas, defina as condições que devem ser satisfeitas para que a transição ocorra.</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Campo</label>
                        <input type="text" name="condition[field]" value="{{ old('condition.field') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            placeholder="Ex: status, valor, categoria">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Operador</label>
                        <select name="condition[operator]"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="equals" {{ old('condition.operator') == 'equals' ? 'selected' : '' }}>Igual a</option>
                            <option value="not_equals" {{ old('condition.operator') == 'not_equals' ? 'selected' : '' }}>Diferente de</option>
                            <option value="greater_than" {{ old('condition.operator') == 'greater_than' ? 'selected' : '' }}>Maior que</option>
                            <option value="less_than" {{ old('condition.operator') == 'less_than' ? 'selected' : '' }}>Menor que</option>
                            <option value="contains" {{ old('condition.operator') == 'contains' ? 'selected' : '' }}>Contém</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor</label>
                        <input type="text" name="condition[value]" value="{{ old('condition.value') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            placeholder="Ex: aprovado, 1000, urgente">
                    </div>
                </div>
            </div>

            <div id="scheduled-conditions" class="condition-group hidden">
                <p class="text-sm text-gray-500 mb-4">Para transições agendadas, defina o tempo que o processo deve permanecer no estágio antes da transição.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duração</label>
                        <input type="number" name="condition[duration]" value="{{ old('condition.duration', 1) }}" min="1"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unidade</label>
                        <select name="condition[unit]"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="minutes" {{ old('condition.unit') == 'minutes' ? 'selected' : '' }}>Minutos</option>
                            <option value="hours" {{ old('condition.unit') == 'hours' ? 'selected' : '' }}>Horas</option>
                            <option value="days" {{ old('condition.unit') == 'days' ? 'selected' : '' }}>Dias</option>
                            <option value="weeks" {{ old('condition.unit') == 'weeks' ? 'selected' : '' }}>Semanas</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="condition[business_days]" id="business_days" value="1" {{ old('condition.business_days') ? 'checked' : '' }}
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <label for="business_days" class="ml-2 block text-sm text-gray-700">Considerar apenas dias úteis</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-5 border-t border-gray-200">
            <a href="{{ route('workflows.show', $workflow->id) }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                Cancelar
            </a>
            <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Adicionar Transição
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const triggerTypeSelect = document.getElementById('trigger_type');
        const conditionGroups = document.querySelectorAll('.condition-group');
        const fromStageSelect = document.getElementById('from_stage_id');
        const toStageSelect = document.getElementById('to_stage_id');

        function showRelevantConditions() {
            const selectedType = triggerTypeSelect.value;

            // Ocultar todas as seções de condição
            conditionGroups.forEach(group => group.classList.add('hidden'));

            // Mostrar apenas a seção relevante
            const relevantGroup = document.getElementById(`${selectedType}-conditions`);
            if (relevantGroup) {
                relevantGroup.classList.remove('hidden');
            }
        }

        function validateStageSelection() {
            const fromId = fromStageSelect.value;
            const toId = toStageSelect.value;

            if (fromId && toId && fromId === toId) {
                alert('Os estágios de origem e destino não podem ser os mesmos.');
                toStageSelect.value = '';
            }
        }

        // Inicializar
        showRelevantConditions();

        // Atualizar quando o tipo de gatilho mudar
        triggerTypeSelect.addEventListener('change', showRelevantConditions);

        // Validar seleção de estágios
        fromStageSelect.addEventListener('change', validateStageSelection);
        toStageSelect.addEventListener('change', validateStageSelection);
    });
</script>
@endsection