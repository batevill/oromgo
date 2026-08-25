<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AmenityController;
use App\Http\Controllers\Api\DachaController;
use App\Http\Controllers\Api\Owner\OwnerDachaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ==========================================
// Ochiq (Public) marshrutlar
// ==========================================
Route::get('/dachas', [DachaController::class, 'index']);
Route::get('/amenities', [AmenityController::class, 'index']);

// ==========================================
// Foydalanuvchi (Auth required) marshrutlar
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dachas/{id}', [DachaController::class, 'show']);
});

// ==========================================
// Dacha Egasi (Owner) uchun CRUD marshrutlar
// ==========================================
Route::middleware(['auth:sanctum', 'role.owner'])->prefix('owner')->group(function () {
    Route::apiResource('dachas', OwnerDachaController::class);
    Route::post('/dachas/{id}/media', [OwnerDachaController::class, 'uploadMedia']);
    Route::delete('/media/{mediaId}', [OwnerDachaController::class, 'deleteMedia']);
});
