@extends('layouts.app')

@section('title', 'Editar Processo')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Editar Processo</h1>
    <a href="{{ route('processes.show', $process->id) }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Voltar para detalhes
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
    <form action="{{ route('processes.update', $process->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="p-6">
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-medium text-gray-900">Informações Básicas</h2>
                    <span class="px-3 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800">
                        Workflow: {{ $process->workflow->name }}
                    </span>
                </div>
                <p class="mb-4 text-sm text-gray-600">Estágio atual: <strong>{{ $process->currentStage->name }}</strong></p>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Título <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title', $process->title) }}" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $process->description) }}</textarea>
                    </div>

                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
                        <select name="assigned_to" id="assigned_to"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">-- Selecione --</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to', $process->assigned_to) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @if(isset($process->data) && is_array($process->data))
            <div class="mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Dados do Processo</h2>

                <div class="bg-gray-50 p-4 rounded-md">
                    @foreach($process->data as $key => $value)
                    <div class="mb-4">
                        <label for="data_{{ $key }}" class="block text-sm font-medium text-gray-700 mb-1">{{ ucfirst(str_replace('_', ' ', $key)) }}</label>

                        @if(is_array($value))
                        <div class="bg-white p-2 rounded border border-gray-300">
                            <pre class="text-xs">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        <input type="hidden" name="data[{{ $key }}]" value="{{ json_encode($value) }}">
                        @else
                        <input type="text" name="data[{{ $key }}]" id="data_{{ $key }}" value="{{ old("data.$key", $value) }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
            <a href="{{ route('processes.show', $process->id) }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                Cancelar
            </a>
            <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Salvar Alterações
            </button>
        </div>
    </form>
</div>
@endsection
