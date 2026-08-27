<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AmenityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DachaController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Owner\OwnerBookingController;
use App\Http\Controllers\Api\Owner\OwnerDachaController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\TelegramWebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ==========================================
// Telegram Bot Webhook (Ochiq)
// ==========================================
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);

// ==========================================
// Ochiq (Public) marshrutlar
// ==========================================
Route::get('/dachas', [DachaController::class, 'index']);
Route::get('/locations', [DachaController::class, 'locations']);
Route::get('/dachas/{id}/calendar', [BookingController::class, 'calendar']);
Route::post('/dachas/{id}/calculate-price', [BookingController::class, 'calculatePrice']);
Route::get('/dachas/{id}/reviews', [ReviewController::class, 'index']);
Route::get('/amenities', [AmenityController::class, 'index']);

// Demo test login helper
Route::post('/demo-login', function (Request $request) {
    $role = $request->input('role', 'owner');
    $user = \App\Models\User::where('role', $role)->first() 
        ?? \App\Models\User::firstOrCreate(
            ['email' => "demo_{$role}@oromgo.uz"],
            ['name' => $role === 'owner' ? 'Alisher Rahimov (Dacha Egasi)' : 'Jasur Bekmurodov', 'role' => $role, 'phone' => '+998901234567']
        );
    $token = $user->createToken('demo_token')->plainTextToken;
    return response()->json(['token' => $token, 'user' => $user]);
});


// ==========================================
// Foydalanuvchi (Auth required) marshrutlar
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dachas/{id}', [DachaController::class, 'show']);
    Route::post('/dachas/{id}/book', [BookingController::class, 'store']);
    Route::post('/dachas/{id}/reviews', [ReviewController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    Route::post('/my-bookings/{id}/cancel', [BookingController::class, 'cancel']);

    // Sevimlilar (Favorites)
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{dachaId}', [FavoriteController::class, 'toggle']);

    // Bildirishnomalar markazi (Notifications)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::get('/telegram/bot-link', [NotificationController::class, 'getTelegramBotLink']);
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

