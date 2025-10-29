<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController; // Breeze uses this
use App\Http\Controllers\SiteController;
use App\Http\Controllers\AdminController;
// Make sure to import controllers you use
// use App\Http\Controllers\BookingController;
// use App\Http\Controllers\OrderController;
// use App\Http\Controllers\FoodController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Your Custom Site Routes ---
Route::get('/', [SiteController::class, 'index'])->name('home');
Route::get('/menu', [SiteController::class, 'menu'])->name('menu');
Route::get('/reviews', [SiteController::class, 'reviews'])->name('reviews');
Route::get('/booking/branches', [SiteController::class, 'branches'])->name('booking.branches');
Route::get('/booking/reservation', [SiteController::class, 'reservation'])->name('booking.reservation'); // Your chosen name
Route::get('/booking/table', [SiteController::class, 'table'])->name('booking.table');
Route::get('/orders/order', [SiteController::class, 'order'])->name('orders.order');
Route::get('/points/points', [SiteController::class, 'points'])->name('points.points');
Route::get('/points/point_transact', [SiteController::class, 'point_transact'])->name('points.point_transact');
Route::get('/carts/cart', [SiteController::class, 'cart'])->name('carts.cart');
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

// --- Breeze Default Routes (Keep These) ---
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard'); // User dashboard

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- Authentication Routes (Handled by Breeze) ---
require __DIR__.'/auth.php'; // This line is crucial