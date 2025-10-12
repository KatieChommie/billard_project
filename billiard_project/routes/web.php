<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;

//main page (home page)
Route::get('/', [SiteController::class, 'index'])->name('home');
//login and register
Route::get('/login', [SiteController::class, 'login'])->name('login');
Route::get('/register', [SiteController::class, 'register'])->name('register');
//booking-category
Route::get('/booking/branches', [SiteController::class, 'branches'])->name('booking.branches');
Route::get('/booking/reservation', [SiteController::class, 'reservation'])->name('booking.reservation');
Route::get('/booking/table', [SiteController::class, 'table'])->name('booking.table');
//menu
Route::get('/menu', [SiteController::class, 'menu'])->name('menu');
//orders
Route::get('/orders/order', [SiteController::class, 'order'])->name('orders.order');
//points-category
Route::get('/points/points', [SiteController::class, 'points'])->name('points.points');
Route::get('/points/point_transact', [SiteController::class, 'point_transact'])->name('points.point_transact');
//cart
Route::get('/carts/cart', [SiteController::class, 'cart'])->name('carts.cart');
//reviews
Route::get('/reviews', [SiteController::class, 'reviews'])->name('reviews');


use App\Http\Controllers\AdminController;
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
