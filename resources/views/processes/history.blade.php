@extends('layouts.app')

@section('title', 'Histórico do Processo')

@section('styles')
<style>
    .timeline-item:last-child .timeline-line {
        display: none;
    }
</style>
@endsection

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Histórico do Processo</h1>
    <a href="{{ route('processes.show', $process->id) }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Voltar para detalhes
    </a>
</div>

<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center border-b pb-4 mb-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ $process->title }}</h2>
            <div class="text-sm text-gray-600 mt-1">
                <span><i class="fas fa-hashtag mr-1"></i>{{ $process->id }}</span>
                <span class="mx-2">|</span>
                <span><i class="fas fa-project-diagram mr-1"></i>{{ $process->workflow->name }}</span>
            </div>
        </div>
        <div class="mt-2 md:mt-0">
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

    <div class="mb-4">
        <h3 class="text-sm uppercase text-gray-500 font-semibold mb-2">Estágio Atual</h3>
        <div class="bg-indigo-50 rounded-lg p-3 border border-indigo-100">
            <div class="flex items-center">
                <span class="px-2 py-1 rounded-full text-xs font-semibold
                    {{ $process->currentStage->type === 'manual' ? 'bg-blue-100 text-blue-800' :
                       ($process->currentStage->type === 'automatic' ? 'bg-green-100 text-green-800' :
                        'bg-yellow-100 text-yellow-800') }}">
                    {{ ucfirst($process->currentStage->type) }}
                </span>
                <span class="ml-2 font-medium text-gray-800">{{ $process->currentStage->name }}</span>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-6">Linha do Tempo</h2>

    @if($history->count() > 0)
    <div class="space-y-6">
        @foreach($history as $item)
        <div class="timeline-item relative pl-8">
            <div class="absolute top-0 left-0 w-6 h-6 rounded-full {{ $item->action == 'process_created' ? 'bg-green-500' : 'bg-blue-500' }} flex items-center justify-center">
                <i class="fas {{ $item->action == 'process_created' ? 'fa-plus' : 'fa-arrow-right' }} text-white text-xs"></i>
            </div>
            <div class="timeline-line absolute top-6 bottom-0 left-3 w-px bg-gray-300"></div>

            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-medium text-gray-800">
                            @if($item->action == 'process_created')
                            Processo criado
                            @elseif($item->action == 'stage_changed')
                            Mudança de estágio
                            @else
                            {{ ucfirst(str_replace('_', ' ', $item->action)) }}
                            @endif
                        </h3>
                        <p class="text-sm text-gray-600">
                            {{ $item->created_at->format('d/m/Y H:i') }}
                            ({{ $item->created_at->diffForHumans() }})
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">
                            Por: {{ $item->performer->name ?? 'Sistema' }}
                        </span>
                    </div>
                </div>

                @if($item->action == 'stage_changed')
                <div class="flex items-center mb-3">
                    <div class="flex-1 text-right pr-3">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ $item->fromStage->type === 'manual' ? 'bg-blue-100 text-blue-800' :
                               ($item->fromStage->type === 'automatic' ? 'bg-green-100 text-green-800' :
                                'bg-yellow-100 text-yellow-800') }}">
                            {{ $item->fromStage->name }}
                        </span>
                    </div>
                    <div class="flex-none">
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </div>
                    <div class="flex-1 pl-3">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ $item->toStage->type === 'manual' ? 'bg-blue-100 text-blue-800' :
                               ($item->toStage->type === 'automatic' ? 'bg-green-100 text-green-800' :
                                'bg-yellow-100 text-yellow-800') }}">
                            {{ $item->toStage->name }}
                        </span>
                    </div>
                </div>
                @endif

                @if($item->comments)
                <div class="mt-2 bg-white p-3 rounded border border-gray-200">
                    <p class="text-sm text-gray-700">{{ $item->comments }}</p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $history->links() }}
    </div>
    @else
    <div class="text-center py-8 bg-gray-50 rounded-lg">
        <i class="fas fa-history text-gray-300 text-4xl mb-3"></i>
        <p class="text-gray-500">Nenhum evento no histórico deste processo.</p>
    </div>
    @endif
</div>
@endsection
