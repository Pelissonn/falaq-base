<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\ModeracaoController;
use App\Http\Controllers\PerguntaController;
use Illuminate\Support\Facades\Route;

// Autenticação
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Eventos
Route::get('/', [EventoController::class, 'index'])->name('eventos.index');

Route::middleware('auth')->group(function () {
    // Declaradas antes de /eventos/{id} para "create" não ser tratado como id.
    Route::get('/eventos/create', [EventoController::class, 'create'])->name('eventos.create');
    Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
});

Route::get('/eventos/{id}', [EventoController::class, 'show'])->name('eventos.show');

// Perguntas (Sprint 3: apenas usuários logados enviam; Sprints 5 e 6: excluir e votar)
Route::middleware('auth')->group(function () {
    Route::post('/eventos/{id}/perguntas', [EventoController::class, 'storePergunta'])->name('eventos.perguntas.store');
    Route::delete('/perguntas/{pergunta}/excluir', [PerguntaController::class, 'destroy'])->name('perguntas.destroy');
    Route::post('/perguntas/{pergunta}/votar', [PerguntaController::class, 'votar'])->name('perguntas.votar');

    // Sprint 7: moderação
    Route::post('/perguntas/{pergunta}/moderar', [ModeracaoController::class, 'store'])->name('perguntas.moderar');
    Route::get('/eventos/{evento}/moderacoes', [ModeracaoController::class, 'index'])->name('eventos.moderacoes');
    Route::patch('/moderacoes/{moderacao}/status', [ModeracaoController::class, 'updateStatus'])->name('moderacoes.status');
});
