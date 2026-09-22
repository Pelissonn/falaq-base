<?php

use App\Http\Controllers\EventoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventoController::class, 'index'])->name('eventos.index');
Route::get('/eventos/{id}', [EventoController::class, 'show'])->name('eventos.show');

// Ticket #005: Rota protegida com middleware 'auth' para proibir envios por visitantes anônimos
Route::post('/eventos/{id}/perguntas', [EventoController::class, 'storePergunta'])
    ->middleware('auth')
    ->name('eventos.perguntas.store');

    