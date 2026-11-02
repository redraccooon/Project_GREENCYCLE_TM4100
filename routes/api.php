<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TreeCareController;
use Illuminate\Support\Facades\Route;

// Cualquiera puede registrarse o iniciar sesión; límite: 5,1, que restringe los intentos de uso indebido a 5 por minuto por dirección IP
Route::post('register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1')
    ->name('register');
Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login');

// Estos requieren un token de Sanctum válido en el encabezado de autorización
Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('me', [AuthController::class, 'me'])->name('me');

    // Sprint 1 (cuando TreeController existe en esta rama):
    // Route::apiResource('trees', TreeController::class)->only(['index', 'show', 'store']);

    // Se separa de las rutas de recursos, ya que un «care» no es una «update» normal.
    Route::post('trees/{tree}/care', [TreeCareController::class, 'store'])
        ->name('trees.care');
});