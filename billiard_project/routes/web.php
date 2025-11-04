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
Route::get('/booking/table/{branchId}', [ReservationController::class, 'showBookingForm'])->name('booking.table');
Route::post('/booking/check', [ReservationController::class, 'checkTableAvailability'])->name('reservation.check');
Route::post('/reservation/confirm', [ReservationController::class, 'reserveBooking'])->name('reservation.confirm');

Route::get('/checkout/{order_id?}', [ReservationController::class, 'showCheckoutPage'])
     ->middleware(['auth'])
     ->name('checkout.page');
Route::post('/checkout/apply-reward', [ReservationController::class, 'applyReward'])
     ->middleware(['auth'])
     ->name('checkout.apply_reward');
Route::post('/checkout/process', [ReservationController::class, 'processPayment'])
     ->middleware(['auth'])
     ->name('checkout.process');

Route::get('/orders/order', [SiteController::class, 'order'])->name('orders.order');
Route::get('/points', [SiteController::class, 'pointsPage'])->name('points.index');
Route::get('/points/history', [SiteController::class, 'pointsHistoryPage'])->name('points.history');
Route::post('/points/redeem', [SiteController::class, 'redeemPoints'])->name('points.redeem');
Route::post('/points/checkin', [SiteController::class, 'dailyCheckin'])->name('points.checkin');
Route::get('/carts/cart', [SiteController::class, 'cart'])->name('carts.cart');


/* DASHBOARDS */
Route::middleware('auth')->group(function () {
    Route::get('/user/dashboard', [DashboardController::class, 'index'])
         ->middleware(['verified']) 
         ->name('user.dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/dashboard/cancel-booking', [DashboardController::class, 'cancelBooking'])
         ->name('dashboard.booking.cancel');
});

Route::delete('/profile', [ProfileController::class, 'destroy'])
    ->middleware(['auth', 'password.confirm']) // ใช้ middleware เพื่อยืนยันรหัสผ่านอีกครั้ง
    ->name('profile.destroy');

/* admin */
Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});
Route::post('/admin/order/complete', [AdminController::class, 'markAsCompleted'])
     ->middleware(['auth', 'admin']) 
     ->name('admin.order.complete');


// --- Authentication Routes (Handled by Breeze) ---
require __DIR__.'/auth.php';