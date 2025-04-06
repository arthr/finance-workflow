<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\WorkflowController;
use App\Http\Controllers\API\ProcessController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Workflow routes
Route::apiResource('workflows', WorkflowController::class);
Route::post('workflows/{id}/stages', [WorkflowController::class, 'addStage']);
Route::post('workflows/{id}/transitions', [WorkflowController::class, 'addTransition']);

// Process routes
Route::apiResource('processes', ProcessController::class);
Route::post('processes/{id}/move', [ProcessController::class, 'moveToNextStage']);
Route::get('processes/{id}/history', [ProcessController::class, 'history']);
