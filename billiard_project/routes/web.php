<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController\DashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ReviewController;
// use App\Http\Controllers\FoodController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/* HOME */
Route::get('/', [SiteController::class, 'index'])->name('home');

/* BOOKING */
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

/* MENU */
Route::get('/menu/{branchId?}', [SiteController::class, 'menu'])->name('menu');
Route::get('/orders/order', [SiteController::class, 'order'])->name('orders.order');

/* CARTS */
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

Route::post('/cart/checkout', [CartController::class, 'processCheckout'])
     ->middleware(['auth'])
     ->name('cart.checkout');

/* POINTS */
Route::get('/points', [SiteController::class, 'pointsPage'])->name('points.index');
Route::get('/points/history', [SiteController::class, 'pointsHistoryPage'])->name('points.history');
Route::post('/points/redeem', [SiteController::class, 'redeemPoints'])->name('points.redeem');
Route::post('/points/checkin', [SiteController::class, 'dailyCheckin'])->name('points.checkin');

/* REVIEWS */
Route::get('/review/create/{order_id}/{branch_id}', [ReviewController::class, 'create'])
    ->name('review.create')
    ->middleware('auth');
Route::post('/review/store', [ReviewController::class, 'store'])
    ->name('review.store')
    ->middleware('auth');

/* DASHBOARDS */
Route::middleware('auth')->group(function () {
    Route::get('/user/dashboard', [DashboardController::class, 'index'])
         ->middleware(['verified']) 
         ->name('user.dashboard');
    Route::post('/admin/bookings/complete/{order_id}', [AdminController::class, 'markAsCompleted'])
     ->name('admin.bookings.complete');
    Route::post('/dashboard/cancel-booking', [DashboardController::class, 'cancelBooking'])
         ->name('dashboard.booking.cancel');
});


/* admin */
Route::middleware(['auth', 'admin'])->group(function () {
     Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
     Route::post('/admin/order/complete', [AdminController::class, 'markAsCompleted'])->name('admin.order.complete');
     Route::get('/admin/users', [AdminController::class, 'manageUsers'])->name('admin.users');
     Route::post('/admin/users/delete/{user_id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete'); 
     Route::get('/admin/branches', [AdminController::class, 'manageBranches'])->name('admin.branches');
     Route::get('/admin/branches/edit/{branch_id}', [AdminController::class, 'editBranch'])->name('admin.branches.edit'); 
     Route::post('/admin/branches/update/{branch_id}', [AdminController::class, 'updateBranch'])->name('admin.branches.update');
     Route::get('/admin/menus', [AdminController::class, 'manageMenus'])->name('admin.menus');
     Route::get('/admin/menus/edit/{branch_id}/{menu_id}', [AdminController::class, 'editMenu'])->name('admin.menus.edit'); 
     Route::post('/admin/menus/update/{branch_id}/{menu_id}', [AdminController::class, 'updateMenu'])->name('admin.menus.update'); 
     Route::get('/admin/tables', [AdminController::class, 'manageTables'])->name('admin.tables');
     Route::get('/admin/bookings', [AdminController::class, 'manageBookings'])->name('admin.bookings');
});


// --- Authentication Routes (Handled by Breeze) ---
require __DIR__.'/auth.php';