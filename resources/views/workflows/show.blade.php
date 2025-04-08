@extends('layouts.app')

@section('title', $workflow->name)

@section('styles')
<style>
    .workflow-diagram {
        position: relative;
        overflow-x: auto;
        padding: 20px 0;
        min-height: 300px;
    }

    .workflow-stage {
        border: 2px solid #4f46e5;
        border-radius: 8px;
        padding: 10px;
        margin: 0 auto 30px;
        background: white;
        position: relative;
        width: 220px;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .workflow-stage:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .workflow-transition {
        position: absolute;
        z-index: 0;
    }

    .workflow-transition-arrow {
        position: absolute;
        width: 12px;
        height: 12px;
        border-top: 2px solid #4b5563;
        border-right: 2px solid #4b5563;
        transform: rotate(135deg);
    }
</style>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">{{ $workflow->name }}</h1>
            <div class="flex space-x-2">
                <a href="{{ route('workflows.index') }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Voltar para lista
                </a>
                <a href="{{ route('workflows.edit', $workflow->id) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i> Editar
                </a>
            </div>
        </div>

        <div class="mt-4 flex flex-col md:flex-row">
            <div class="flex-1">
                <p class="text-gray-500">{{ $workflow->description ?? 'Sem descrição' }}</p>

                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Status:</span>
                        @if ($workflow->is_active)
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Ativo
                        </span>
                        @else
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Inativo
                        </span>
                        @endif
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Criado por:</span>
                        <span class="text-gray-900">{{ $workflow->creator->name ?? 'Sistema' }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Criado em:</span>
                        <span class="text-gray-900">{{ $workflow->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Processos ativos:</span>
                        <span class="text-gray-900">{{ $workflow->processes->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações -->
    <div class="bg-gray-50 p-4 border-b border-gray-200">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('workflows.stages.create', $workflow->id) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition">
                <i class="fas fa-plus-circle mr-2"></i> Adicionar Estágio
            </a>

            @if($workflow->stages->count() >= 2)
            <a href="{{ route('workflows.transitions.create', $workflow->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition">
                <i class="fas fa-exchange-alt mr-2"></i> Adicionar Transição
            </a>
            @else
            <button disabled class="inline-flex items-center px-4 py-2 bg-blue-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest opacity-50 cursor-not-allowed">
                <i class="fas fa-exchange-alt mr-2"></i> Adicionar Transição
            </button>
            <span class="text-xs text-gray-500">(Necessário ter ao menos 2 estágios)</span>
            @endif

            <a href="{{ route('webhooks.create', ['workflow_id' => $workflow->id]) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-700 focus:outline-none focus:border-purple-700 focus:ring ring-purple-300 disabled:opacity-25 transition">
                <i class="fas fa-plug mr-2"></i> Configurar Webhook
            </a>
        </div>
    </div>

    <!-- Diagrama do Workflow -->
    <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Diagrama do Workflow</h2>

        @if($workflow->stages->count() > 0)
        <div class="workflow-diagram mb-6" id="workflow-diagram">
            @foreach($workflow->stages as $index => $stage)
            <div id="stage-{{ $stage->id }}" class="workflow-stage" data-id="{{ $stage->id }}">
                <div class="text-center mb-1">
                    <span class="inline-block px-2 py-1 text-xs rounded-full {{ $stage->type === 'manual' ? 'bg-blue-100 text-blue-800' : ($stage->type === 'automatic' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($stage->type) }}
                    </span>
                </div>
                <h3 class="font-medium text-gray-900 text-center">{{ $stage->name }}</h3>
                @if($stage->description)
                <p class="text-xs text-gray-500 mt-1 text-center truncate" title="{{ $stage->description }}">
                    {{ $stage->description }}
                </p>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Transições -->
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Transições</h3>
        @if($workflow->transitions->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            De
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Para
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo de Gatilho
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Condição
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($workflow->transitions as $transition)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-medium text-gray-900">{{ $transition->fromStage->name }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-medium text-gray-900">{{ $transition->toStage->name }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $transition->trigger_type === 'manual' ? 'bg-blue-100 text-blue-800' :
                                  ($transition->trigger_type === 'automatic' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($transition->trigger_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($transition->condition)
                            <code class="text-xs bg-gray-100 p-1 rounded">{{ json_encode($transition->condition) }}</code>
                            @else
                            <span class="text-gray-500">Sem condição</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="#" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="bg-gray-50 rounded-lg p-6 text-center">
            <i class="fas fa-exchange-alt text-gray-300 text-3xl mb-3"></i>
            <p class="text-gray-500">Não há transições definidas para este workflow.</p>
            @if($workflow->stages->count() >= 2)
            <a href="{{ route('workflows.transitions.create', $workflow->id) }}" class="mt-2 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition">
                <i class="fas fa-plus-circle mr-2"></i> Adicionar Transição
            </a>
            @endif
        </div>
        @endif
        @else
        <div class="bg-gray-50 rounded-lg p-6 text-center">
            <i class="fas fa-project-diagram text-gray-300 text-3xl mb-3"></i>
            <p class="text-gray-500">Este workflow não possui estágios definidos.</p>
            <a href="{{ route('workflows.stages.create', $workflow->id) }}" class="mt-2 inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition">
                <i class="fas fa-plus-circle mr-2"></i> Adicionar Primeiro Estágio
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stages = document.querySelectorAll('.workflow-stage');
        const container = document.getElementById('workflow-diagram');
        const lines = [];

        // Organiza estágios usando flexbox
        function arrangeStages() {
            container.style.display = 'flex';
            container.style.flexWrap = 'wrap';
            container.style.justifyContent = 'center';
            container.style.alignItems = 'flex-start';
            container.style.gap = '20px';

            stages.forEach(stage => {
                stage.style.position = 'relative';
            });
        }

        // Função para desenhar transições usando LeaderLine
        function drawTransition(fromId, toId, type) {
            const fromStage = document.getElementById(`stage-${fromId}`);
            const toStage = document.getElementById(`stage-${toId}`);

            if (!fromStage || !toStage) return;

            // Cria a linha com LeaderLine
            const line = new LeaderLine(
                fromStage,
                toStage, {
                    color: type === 'manual' ? '#3b82f6' : (type === 'automatic' ? '#10b981' : '#f59e0b'),
                    size: 2,
                    startPlug: 'disc',
                    endPlug: 'disc',
                    endPlugSize: 1.5,
                    path: 'straight', // Pode ser 'arc', 'fluid', etc.
                }
            );

            lines.push(line);
        }

        // Função para redesenhar todas as transições
        function redrawTransitions() {
            // Remove todas as linhas existentes
            lines.forEach(line => line.remove());
            lines.length = 0;

            // Desenha novamente as transições
            <?php if ($workflow->transitions->count() > 0) { ?>
                <?php foreach ($workflow->transitions as $transition) { ?>
                    drawTransition(<?php echo $transition->from_stage_id ?>, <?php echo $transition->to_stage_id ?>, "<?php echo $transition->trigger_type ?>");
                <?php } ?>
            <?php } ?>
        }

        // Inicializar layout dos estágios
        arrangeStages();

        // Desenhar as transições
        redrawTransitions();

        // Adicionar evento de redimensionamento
        window.addEventListener('resize', function() {
            redrawTransitions();
        });

        // Esperar um momento para garantir que o layout dos estágios esteja pronto
        setTimeout(function() {
            // Função para redesenhar todas as transições
            window.redrawTransitions = function() {
                <?php if ($workflow->transitions->count() > 0) { ?>
                    <?php foreach ($workflow->transitions as $transition) { ?>
                        drawTransition(<?php echo $transition->from_stage_id ?>, <?php echo $transition->to_stage_id ?>, "<?php echo $transition->trigger_type ?>");
                    <?php } ?>
                <?php } ?>
            };
            // Desenhar as transições
            window.redrawTransitions();
        }, 100);
    });
</script>
@endsection
