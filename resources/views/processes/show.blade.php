@extends('layouts.app')

@section('title', 'Detalhes do Processo')

@section('styles')
<style>
    .process-card {
        transition: all 0.3s ease;
    }

    .process-header:hover {
        background-color: #f9fafb;
    }

    .stage-badge {
        transition: all 0.2s ease;
    }

    .transition-btn:hover .stage-badge {
        transform: scale(1.05);
    }

    .transition-error {
        animation: shake 0.5s;
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        10%,
        30%,
        50%,
        70%,
        90% {
            transform: translateX(-5px);
        }

        20%,
        40%,
        60%,
        80% {
            transform: translateX(5px);
        }
    }

    .alert-transition {
        transition: all 0.5s ease;
        max-height: 200px;
        overflow: hidden;
    }

    .alert-transition.hide {
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
        margin-top: 0;
        margin-bottom: 0;
        opacity: 0;
    }
</style>
@endsection

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Detalhes do Processo</h1>
    <a href="{{ route('processes.index') }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Voltar para processos
    </a>
</div>

@if(session('errorType') === 'validation')
<div id="validationError" class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 mb-6 rounded-r-md shadow alert-transition" role="alert">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-2xl mr-4"></i>
        </div>
        <div>
            <p class="font-bold">Atenção! Erro de validação</p>
            <p>{{ session('error') }}</p>
        </div>
        <button onclick="document.getElementById('validationError').classList.add('hide')" class="ml-auto">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@if(session('errorType') === 'system')
<div id="systemError" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-md shadow alert-transition" role="alert">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-times-circle text-2xl mr-4"></i>
        </div>
        <div>
            <p class="font-bold">Erro do Sistema</p>
            <p>{{ session('error') }}</p>
            <p class="text-sm mt-1">Se o erro persistir, entre em contato com o administrador.</p>
        </div>
        <button onclick="document.getElementById('systemError').classList.add('hide')" class="ml-auto">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

<div class="bg-white rounded-lg shadow-md overflow-hidden process-card mb-6">
    <div class="bg-gray-50 p-4 border-b process-header">
        <div class="md:flex md:justify-between md:items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $process->title }}</h2>
                <div class="text-sm text-gray-600 mt-1">
                    <span><i class="fas fa-hashtag mr-1"></i>{{ $process->id }}</span>
                    <span class="mx-2">|</span>
                    <span><i class="fas fa-project-diagram mr-1"></i>{{ $process->workflow->name }}</span>
                </div>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    {{ $process->status === 'active' ? 'bg-green-100 text-green-800' :
                      ($process->status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' :
                      ($process->status === 'completed' ? 'bg-blue-100 text-blue-800' :
                       'bg-red-100 text-red-800')) }}">
                    <i class="fas {{ $process->status === 'active' ? 'fa-play' :
                                    ($process->status === 'on_hold' ? 'fa-pause' :
                                    ($process->status === 'completed' ? 'fa-check' :
                                     'fa-times')) }} mr-1"></i>
                    {{ ucfirst($process->status) }}
                </div>
            </div>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-4">
            <div>
                <h3 class="text-sm uppercase text-gray-500 font-semibold mb-2">Informações Básicas</h3>
                <dl class="grid grid-cols-1 gap-y-3">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Criado por:</dt>
                        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $process->creator->name ?? 'Não definido' }}</dd>
                    </div>
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Responsável:</dt>
                        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $process->assignee->name ?? 'Não atribuído' }}</dd>
                    </div>
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Criado em:</dt>
                        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $process->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Atualizado:</dt>
                        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $process->updated_at->diffForHumans() }}</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-sm uppercase text-gray-500 font-semibold mb-2">Estágio Atual</h3>
                <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-100">
                    <div class="flex items-center mb-2">
                        <span class="stage-badge px-2 py-1 rounded-full text-xs font-semibold
                            {{ $process->currentStage->type === 'manual' ? 'bg-blue-100 text-blue-800' :
                               ($process->currentStage->type === 'automatic' ? 'bg-green-100 text-green-800' :
                                'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($process->currentStage->type) }}
                        </span>
                        <span class="ml-2 text-lg font-medium text-gray-800">{{ $process->currentStage->name }}</span>
                    </div>
                    @if($process->currentStage->description)
                    <p class="text-sm text-gray-600">{{ $process->currentStage->description }}</p>
                    @endif
                </div>

                @if($process->status === 'active')
                <div class="mt-4">
                    <h4 class="text-sm uppercase text-gray-500 font-semibold mb-2">Ações Disponíveis</h4>
                    @if($availableTransitions->count() > 0)
                    <div class="space-y-2">
                        @foreach($availableTransitions as $transition)
                        <button
                            type="button"
                            class="transition-btn w-full flex justify-between items-center px-4 py-2 border border-indigo-200 rounded-md hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            onclick="showTransitionForm('{{ $transition->id }}', '{{ $transition->toStage->name }}')">
                            <span class="font-medium text-indigo-700">Mover para: {{ $transition->toStage->name }}</span>
                            <span class="stage-badge px-2 py-1 rounded-full text-xs font-semibold
                                {{ $transition->trigger_type === 'manual' ? 'bg-blue-100 text-blue-800' :
                                ($transition->trigger_type === 'automatic' ? 'bg-green-100 text-green-800' :
                                    'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($transition->trigger_type) }}
                            </span>
                        </button>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4 bg-gray-50 rounded">
                        <p class="text-gray-500">Não há transições disponíveis para este estágio.</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-sm uppercase text-gray-500 font-semibold mb-2">Descrição</h3>
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                @if($process->description)
                <p class="text-gray-700">{{ $process->description }}</p>
                @else
                <p class="text-gray-500 italic">Nenhuma descrição fornecida.</p>
                @endif
            </div>
        </div>

        @if(isset($process->data) && !empty($process->data))
        <div class="mt-6">
            <h3 class="text-sm uppercase text-gray-500 font-semibold mb-2">Dados do Processo</h3>
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <dl class="grid grid-cols-1 gap-y-3">
                    @foreach($process->data as $key => $value)
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}:</dt>
                        <dd class="text-sm text-gray-900 sm:col-span-2">
                            @if(is_array($value))
                            <pre class="text-xs overflow-x-auto">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                            @else
                            {{ $value }}
                            @endif
                        </dd>
                    </div>
                    @endforeach
                </dl>
            </div>
        </div>
        @endif

        <div class="mt-6 flex justify-between">
            <div>
                <a href="{{ route('processes.history', $process->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-history mr-2"></i>
                    Ver Histórico
                </a>
            </div>
            <div>
                <a href="{{ route('processes.edit', $process->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal para formulário de transição -->
<div id="transitionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden">
        <div class="bg-indigo-600 px-6 py-4">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-white" id="transitionModalTitle">Mover para próximo estágio</h3>
                <button type="button" onclick="hideTransitionForm()" class="text-white hover:text-indigo-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <form action="{{ route('processes.move', $process->id) }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="to_stage_id" id="to_stage_id">

            <div class="space-y-4">
                <div>
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Atribuir a:</label>
                    <select name="assigned_to" id="assigned_to" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">-- Selecione um responsável --</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $process->assigned_to == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="comments" class="block text-sm font-medium text-gray-700 mb-1">Comentários:</label>
                    <textarea name="comments" id="comments" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" onclick="hideTransitionForm()" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                    Cancelar
                </button>
                <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Mover Processo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showTransitionForm(transitionId, stageName) {
        const fromStage = document.querySelector('.stage-badge + span').textContent;
        document.getElementById('transitionModalTitle').textContent = `Mover de "${fromStage}" para "${stageName}"`;

        const toStageId = '{{ $availableTransitions->isNotEmpty() ? $availableTransitions->first()->to_stage_id : "" }}';
        document.getElementById('to_stage_id').value = toStageId;

        document.getElementById('transitionModal').classList.remove('hidden');
    }

    function hideTransitionForm() {
        document.getElementById('transitionModal').classList.add('hidden');
    }

    // Fechar modais ao clicar fora deles
    document.getElementById('transitionModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideTransitionForm();
        }
    });

    // Auto-esconder alertas após 10 segundos
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-transition');
        alerts.forEach(alert => {
            alert.classList.add('hide');
        });
    }, 10000);
</script>
@endsection
