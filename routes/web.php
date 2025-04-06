<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\WorkflowController;
use App\Http\Controllers\Web\ProcessController;

// Rota de boas-vindas (pública)
Route::get('/', function () {
    return view('welcome');
});

// Rotas autenticadas
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Workflows
    Route::resource('workflows', WorkflowController::class);
    Route::get('/workflows/{workflow}/stages/create', [WorkflowController::class, 'createStage'])->name('workflows.stages.create');
    Route::post('/workflows/{workflow}/stages', [WorkflowController::class, 'storeStage'])->name('workflows.stages.store');
    Route::get('/workflows/{workflow}/transitions/create', [WorkflowController::class, 'createTransition'])->name('workflows.transitions.create');
    Route::post('/workflows/{workflow}/transitions', [WorkflowController::class, 'storeTransition'])->name('workflows.transitions.store');

    // Processos (including destroy)
    Route::resource('processes', ProcessController::class);
    Route::post('/processes/{process}/move', [ProcessController::class, 'move'])->name('processes.move');
    Route::get('/processes/{process}/history', [ProcessController::class, 'history'])->name('processes.history');
});

// Rotas de autenticação
require __DIR__ . '/auth.php';
