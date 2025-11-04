<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController\DashboardController;
use App\Http\Controllers\ReservationController;
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
Route::get('/menu/{branchId?}', [SiteController::class, 'menu'])->name('menu');
Route::get('/reviews', [SiteController::class, 'reviews'])->name('reviews');

Route::get('/booking/branches', [SiteController::class, 'branches'])->name('booking.branches');
Route::get('/booking/reservation', [ReservationController::class, 'reservation'])->name('booking.reservation');
Route::get('/booking/table', [ReservationController::class, 'showBookingForm'])->name('booking.table');
Route::post('/booking/check', [ReservationController::class, 'checkTableAvailability'])->name('reservation.check');
Route::post('/reservation/confirm', [ReservationController::class, 'confirmBooking'])->name('reservation.confirm');
Route::get('/orders/order', [SiteController::class, 'order'])->name('orders.order');
Route::get('/points', [SiteController::class, 'pointsPage'])->name('points.index');
Route::get('/points/history', [SiteController::class, 'pointsHistoryPage'])->name('points.history');
Route::post('/points/redeem', [SiteController::class, 'redeemPoints'])->name('points.redeem');
Route::post('/points/checkin', [SiteController::class, 'dailyCheckin'])->name('points.checkin');
Route::get('/carts/cart', [SiteController::class, 'cart'])->name('carts.cart');

// --- Breeze Default Routes (Keep These) ---
Route::get('/user/dashboard', function () {
    return view('user.dashboard');
})->middleware(['auth', 'verified'])->name('user.dashboard'); // User dashboard

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // อนาคตจะ "จัดการเมนู" "จัดการสาขา" ก็ยัดไว้ในนี้
    // Route::get('/admin/menus', [AdminController::class, 'manageMenus']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/my-account', [DashboardController::class, 'index'])->name('user.dashboard'); 
    
});

Route::delete('/profile', [ProfileController::class, 'destroy'])
    ->middleware(['auth', 'password.confirm']) // ใช้ middleware เพื่อยืนยันรหัสผ่านอีกครั้ง
    ->name('profile.destroy');

// --- Authentication Routes (Handled by Breeze) ---
require __DIR__.'/auth.php'; // This line is crucial