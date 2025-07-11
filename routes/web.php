<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ThreatScenarioController;
use App\Http\Controllers\PlaceholderController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
})->name('welcome');


Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/placeholder.svg', [PlaceholderController::class, 'placeholder']);

Route::middleware('auth')->group(function () {

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/sessions', [ChatController::class, 'getSessions'])->name('chat.get-sessions');
    Route::post('/chat/session', [ChatController::class, 'createSession'])->name('chat.create-session');
    Route::post('/chat/message', [ChatController::class, 'sendMessage'])->name('chat.send-message');
    Route::get('/chat/session/{sessionId}', [ChatController::class, 'getSession'])->name('chat.get-session');
    Route::put('/chat/session/{sessionId}', [ChatController::class, 'updateSession'])->name('chat.update-session');
    Route::delete('/chat/session/{sessionId}', [ChatController::class, 'deleteSession'])->name('chat.delete-session');


    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/scenarios', [AdminController::class, 'scenarios'])->name('scenarios');
        
        
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        
    
        Route::post('/scenarios', [AdminController::class, 'storeScenario'])->name('scenarios.store');
        Route::get('/scenarios/{scenario}/edit', [AdminController::class, 'editScenario'])->name('scenarios.edit');
        Route::put('/scenarios/{scenario}', [AdminController::class, 'updateScenario'])->name('scenarios.update');
        Route::delete('/scenarios/{scenario}', [AdminController::class, 'destroyScenario'])->name('scenarios.destroy');
        

        Route::apiResource('api/scenarios', ThreatScenarioController::class);
    });
});