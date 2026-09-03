<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login', function () {
    return redirect()->route('home');
})->name('login');

Route::get('/admin', function () {
    return view('admin');
})->name('admin');

Route::get('/owner', function () {
    return view('owner');
})->name('owner');

Route::get('/cabinet', function () {
    return redirect()->route('owner');
})->name('cabinet');

Route::get('/support', function () {
    return view('support');
})->name('support');

Route::get('/auth/{provider}/redirect', [AuthController::class, 'redirectToProvider'])->name('auth.redirect');
Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])->name('auth.callback');


