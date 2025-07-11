<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\ThreatScenarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth'])->prefix('admin')->group(function () {   
    Route::apiResource('users', UserController::class);

    Route::apiResource('scenarios', ThreatScenarioController::class);
    
  
    Route::get('statistics/dashboard', [StatisticsController::class, 'dashboard']);
});