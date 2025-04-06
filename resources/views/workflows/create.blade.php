@extends('layouts.app')

@section('title', 'Criar Workflow')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Criar Novo Workflow</h1>
        <a href="{{ route('workflows.index') }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Voltar para lista
        </a>
    </div>

    <form action="{{ route('workflows.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome do Workflow <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">Workflow ativo</label>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Estágios Iniciais (opcional)</h2>

                <div id="stages-container" class="space-y-4">
                    <div class="stage-item p-3 bg-white rounded border border-gray-200 relative">
                        <button type="button" class="remove-stage absolute top-2 right-2 text-red-500 hover:text-red-700" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Estágio <span class="text-red-500">*</span></label>
                                <input type="text" name="stages[0][name]" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                <select name="stages[0][type]"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="manual">Manual</option>
                                    <option value="automatic">Automático</option>
                                    <option value="conditional">Condicional</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                            <textarea name="stages[0][description]" rows="2"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                        </div>
                    </div>
                </div>

                <button type="button" id="add-stage" class="mt-3 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-plus mr-2"></i> Adicionar Estágio
                </button>
            </div>
        </div>

        <div class="flex justify-end pt-5 border-t border-gray-200">
            <a href="{{ route('workflows.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                Cancelar
            </a>
            <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Criar Workflow
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stagesContainer = document.getElementById('stages-container');
        const addStageButton = document.getElementById('add-stage');
        let stageCount = 1;

        addStageButton.addEventListener('click', function() {
            const stageTemplate = `
                <div class="stage-item p-3 bg-white rounded border border-gray-200 relative">
                    <button type="button" class="remove-stage absolute top-2 right-2 text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Estágio <span class="text-red-500">*</span></label>
                            <input type="text" name="stages[${stageCount}][name]" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select name="stages[${stageCount}][type]"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="manual">Manual</option>
                                <option value="automatic">Automático</option>
                                <option value="conditional">Condicional</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="stages[${stageCount}][description]" rows="2"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                    </div>
                </div>
            `;

            stagesContainer.insertAdjacentHTML('beforeend', stageTemplate);
            stageCount++;

            // Exibir o botão de remover para o primeiro estágio quando houver mais de um
            if (stageCount > 1) {
                document.querySelector('.stage-item .remove-stage').style.display = 'block';
            }
        });

        // Delegação de eventos para o botão remover
        stagesContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-stage')) {
                e.target.closest('.stage-item').remove();

                // Ocultar o botão de remover do primeiro estágio se ficar apenas um
                const stageItems = document.querySelectorAll('.stage-item');
                if (stageItems.length === 1) {
                    stageItems[0].querySelector('.remove-stage').style.display = 'none';
                }

                // Reindexar os campos
                reindexStages();
            }
        });

        function reindexStages() {
            const stageItems = document.querySelectorAll('.stage-item');
            stageItems.forEach((item, index) => {
                item.querySelectorAll('input, select, textarea').forEach(field => {
                    const name = field.getAttribute('name');
                    if (name) {
                        field.setAttribute('name', name.replace(/stages\[\d+\]/, `stages[${index}]`));
                    }
                });
            });
            stageCount = stageItems.length;
        }
    });
</script>
@endsection