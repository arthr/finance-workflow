<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\WorkflowController;
use App\Http\Controllers\API\ProcessController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WebhookLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rotas de autenticação (públicas)
Route::post('/login', [AuthController::class, 'login']);

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {
    // Informações do usuário autenticado
    Route::get('/user', [AuthController::class, 'user']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Workflow routes
    Route::apiResource('workflows', WorkflowController::class);
    Route::post('workflows/{id}/stages', [WorkflowController::class, 'addStage']);
    Route::post('workflows/{id}/transitions', [WorkflowController::class, 'addTransition']);

    // Process routes
    Route::apiResource('processes', ProcessController::class);
    Route::post('processes/{id}/move', [ProcessController::class, 'moveToNextStage']);
    Route::get('processes/{id}/history', [ProcessController::class, 'history']);

    /*
    * Webhook Routes
    */
    Route::prefix('webhooks')->group(function () {
        Route::get('/', [WebhookController::class, 'index']);
        Route::post('/', [WebhookController::class, 'store']);
        Route::get('/{id}', [WebhookController::class, 'show']);
        Route::put('/{id}', [WebhookController::class, 'update']);
        Route::delete('/{id}', [WebhookController::class, 'destroy']);
        Route::get('/{id}/logs', [WebhookController::class, 'logs']);
        
        // Nova rota para detalhes de log específico
        Route::get('/logs/{id}', [WebhookLogController::class, 'show']);
    });
});
