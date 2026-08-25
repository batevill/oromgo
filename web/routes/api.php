<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AmenityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DachaController;
use App\Http\Controllers\Api\Owner\OwnerBookingController;
use App\Http\Controllers\Api\Owner\OwnerDachaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ==========================================
// Ochiq (Public) marshrutlar
// ==========================================
Route::get('/dachas', [DachaController::class, 'index']);
Route::get('/dachas/{id}/calendar', [BookingController::class, 'calendar']);
Route::post('/dachas/{id}/calculate-price', [BookingController::class, 'calculatePrice']);
Route::get('/amenities', [AmenityController::class, 'index']);

// ==========================================
// Foydalanuvchi (Auth required) marshrutlar
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dachas/{id}', [DachaController::class, 'show']);
    Route::post('/dachas/{id}/book', [BookingController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    Route::post('/my-bookings/{id}/cancel', [BookingController::class, 'cancel']);
});

// ==========================================
// Dacha Egasi (Owner) uchun CRUD va Bron boshqaruvi
// ==========================================
Route::middleware(['auth:sanctum', 'role.owner'])->prefix('owner')->group(function () {
    // Dacha CRUD
    Route::apiResource('dachas', OwnerDachaController::class);
    Route::post('/dachas/{id}/media', [OwnerDachaController::class, 'uploadMedia']);
    Route::delete('/media/{mediaId}', [OwnerDachaController::class, 'deleteMedia']);

    // Bronlar va Sanalarni yopish
    Route::get('/bookings', [OwnerBookingController::class, 'index']);
    Route::post('/bookings/{id}/confirm', [OwnerBookingController::class, 'confirm']);
    Route::post('/bookings/{id}/reject', [OwnerBookingController::class, 'reject']);
    Route::post('/dachas/{id}/block-dates', [OwnerBookingController::class, 'blockDates']);
});
