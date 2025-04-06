@extends('layouts.app')

@section('title', 'Criar Novo Processo')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Criar Novo Processo</h1>
    <a href="{{ route('processes.index') }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Voltar para lista
    </a>
</div>

@if ($errors->any())
<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
    <p class="font-bold">Por favor, corrija os seguintes erros:</p>
    <ul class="list-disc pl-5">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <form action="{{ route('processes.store') }}" method="POST">
        @csrf

        <div class="p-6">
            <div class="mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Informações Básicas</h2>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="workflow_id" class="block text-sm font-medium text-gray-700 mb-1">Workflow <span class="text-red-500">*</span></label>
                        <select name="workflow_id" id="workflow_id" required onchange="loadInitialStage()"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">-- Selecione o Workflow --</option>
                            @foreach($workflows as $workflow)
                            <option value="{{ $workflow->id }}" {{ old('workflow_id') == $workflow->id ? 'selected' : '' }}
                                data-first-stage="{{ $workflow->stages->first()->id ?? '' }}"
                                data-first-stage-name="{{ $workflow->stages->first()->name ?? 'Nenhum estágio' }}">
                                {{ $workflow->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="initial-stage-info" class="bg-gray-50 p-4 rounded-md {{ old('workflow_id') ? '' : 'hidden' }}">
                        <p class="text-sm text-gray-600">Estágio inicial: <strong id="stage-name">{{ old('initial_stage', '') }}</strong></p>
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Título <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
                        <select name="assigned_to" id="assigned_to"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">-- Selecione --</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Dados Adicionais (Opcional)</h2>

                <div id="dynamic-fields" class="space-y-4">
                    @if(old('data') && is_array(old('data')))
                    @foreach(old('data') as $key => $value)
                    <div class="grid grid-cols-12 gap-2 data-field">
                        <div class="col-span-5">
                            <input type="text" name="data_keys[]" value="{{ $key }}" placeholder="Chave"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div class="col-span-6">
                            <input type="text" name="data_values[]" value="{{ $value }}" placeholder="Valor"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div class="col-span-1 flex items-center">
                            <button type="button" class="text-red-500 hover:text-red-700 remove-field">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>

                <div class="mt-3">
                    <button type="button" id="add-field" class="text-indigo-600 hover:text-indigo-900 text-sm flex items-center">
                        <i class="fas fa-plus-circle mr-1"></i> Adicionar campo
                    </button>
                </div>
            </div>

            <div>
                <label for="comments" class="block text-sm font-medium text-gray-700 mb-1">Comentários Iniciais</label>
                <textarea name="comments" id="comments" rows="2"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('comments') }}</textarea>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
            <a href="{{ route('processes.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                Cancelar
            </a>
            <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Criar Processo
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    function loadInitialStage() {
        const workflowSelect = document.getElementById('workflow_id');
        const selectedOption = workflowSelect.options[workflowSelect.selectedIndex];
        const stageInfo = document.getElementById('initial-stage-info');
        const stageName = document.getElementById('stage-name');

        if (workflowSelect.value) {
            stageName.textContent = selectedOption.dataset.firstStageName;
            stageInfo.classList.remove('hidden');
        } else {
            stageInfo.classList.add('hidden');
        }
    }

    // Inicializar informação do estágio se já houver um workflow selecionado
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('workflow_id').value) {
            loadInitialStage();
        }

        // Adicionar campo dinâmico
        document.getElementById('add-field').addEventListener('click', function() {
            const container = document.getElementById('dynamic-fields');
            const fieldDiv = document.createElement('div');
            fieldDiv.className = 'grid grid-cols-12 gap-2 data-field';
            fieldDiv.innerHTML = `
                <div class="col-span-5">
                    <input type="text" name="data_keys[]" placeholder="Chave"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div class="col-span-6">
                    <input type="text" name="data_values[]" placeholder="Valor"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div class="col-span-1 flex items-center">
                    <button type="button" class="text-red-500 hover:text-red-700 remove-field">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            container.appendChild(fieldDiv);

            // Adicionar evento para remover campo
            fieldDiv.querySelector('.remove-field').addEventListener('click', function() {
                container.removeChild(fieldDiv);
            });
        });

        // Adicionar evento para remover campos existentes
        document.querySelectorAll('.remove-field').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.data-field').remove();
            });
        });
    });

    // Processar dados dinâmicos antes de enviar o formulário
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();

        // Criar objeto de dados
        const data = {};
        const keys = document.querySelectorAll('input[name="data_keys[]"]');
        const values = document.querySelectorAll('input[name="data_values[]"]');

        for (let i = 0; i < keys.length; i++) {
            const key = keys[i].value.trim();
            const value = values[i].value.trim();

            if (key) {
                data[key] = value;
            }
        }

        // Adicionar campo oculto com os dados JSON
        const dataInput = document.createElement('input');
        dataInput.type = 'hidden';
        dataInput.name = 'data';
        dataInput.value = JSON.stringify(data);
        this.appendChild(dataInput);

        // Enviar formulário
        this.submit();
    });
</script>
@endsection
