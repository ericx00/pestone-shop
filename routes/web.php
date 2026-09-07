<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\Account\AccountController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/shop', [ProductController::class, 'index'])->name('shop');
Route::get('/category/{category:slug}', [ProductController::class, 'index'])->name('category');
Route::get('/brand/{brand:slug}', [ProductController::class, 'index'])->name('brand');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product');

/* ---- cart ---- */
Route::controller(CartController::class)->prefix('cart')->name('cart.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/add', 'add')->name('add');
    Route::post('/update', 'update')->name('update');
    Route::post('/remove', 'remove')->name('remove');
    Route::post('/clear', 'clear')->name('clear');
});

/* ---- checkout ---- */
Route::controller(CheckoutController::class)->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'place')->name('place');
    Route::get('/{order:number}/pay', 'payForm')->name('pay.form');
    Route::post('/{order:number}/pay', 'pay')->name('pay');
    Route::get('/{order:number}/status', 'status')->name('status');
    Route::match(['get', 'post'], '/{order:number}/return', 'return')->name('return');
});

/* ---- quote requests ---- */
Route::get('/request-a-quote', [QuoteController::class, 'create'])->name('quote.create');
Route::post('/request-a-quote', [QuoteController::class, 'store'])->name('quote.store');

/* ---- auth ---- */
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/* ---- customer account ---- */
Route::middleware('auth')->prefix('account')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('account');
    Route::get('/orders/{order:number}', [AccountController::class, 'order'])->name('account.order');
    Route::get('/business', [AccountController::class, 'business'])->name('account.business');
    Route::post('/business', [AccountController::class, 'applyBusiness'])->name('account.business.apply');
    Route::post('/addresses', [AccountController::class, 'storeAddress'])->name('account.addresses.store');
});

/* ---- static / marketing pages ---- */
Route::get('/p/{slug}', [PageController::class, 'show'])->name('page');
