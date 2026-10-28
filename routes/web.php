<?php

use App\Http\Controllers\{AdminController, AuthController, BookingController, CartController, CatalogController, ReviewController};
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'home'])->name('home');
Route::get('/services', [CatalogController::class, 'services'])->name('services');
Route::get('/gallery', [CatalogController::class, 'gallery'])->name('gallery');
Route::get('/reviews', [CatalogController::class, 'reviews'])->name('reviews');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->middleware('throttle:6,1')->name('register.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::get('/checkout', [BookingController::class, 'checkout'])->name('checkout');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('booking.store');
    Route::get('/bookings/{booking}/confirmation', [BookingController::class, 'success'])->name('booking.success');
    Route::post('/reviews', [ReviewController::class, 'store'])->middleware('throttle:5,1')->name('reviews.store');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'adminLogin'])->middleware('guest:admin')->name('login');
    Route::post('/login', [AuthController::class, 'authenticateAdmin'])->middleware(['guest:admin', 'throttle:6,1'])->name('login.store');
    Route::post('/logout', [AuthController::class, 'adminLogout'])->middleware('admin')->name('logout');
    Route::middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/{resource}', [AdminController::class, 'index'])->where('resource', 'services|gallery|bookings|reviews|users')->name('index');
        Route::post('/{resource}', [AdminController::class, 'store'])->where('resource', 'services|gallery|bookings|reviews|users')->name('store');
        Route::put('/{resource}/{id}', [AdminController::class, 'update'])->where('resource', 'services|gallery|bookings|reviews|users')->whereNumber('id')->name('update');
        Route::delete('/{resource}/{id}', [AdminController::class, 'destroy'])->where('resource', 'services|gallery|bookings|reviews|users')->whereNumber('id')->name('destroy');
    });
});

// Keep bookmarks from the PHP application working through Laravel.
foreach (['index.php' => '/', 'services.php' => '/services', 'gallery.php' => '/gallery', 'reviews.php' => '/reviews', 'login.php' => '/login', 'register.php' => '/register', 'cart.php' => '/cart', 'checkout.php' => '/checkout', 'admin/index.php' => '/admin', 'admin/login.php' => '/admin/login'] as $old => $new) {
    Route::get('/'.$old, fn (\Illuminate\Http\Request $request) => redirect($new.($request->getQueryString() ? '?'.$request->getQueryString() : ''), 301));
}
foreach (['services', 'gallery', 'bookings', 'reviews', 'users'] as $resource) {
    Route::redirect('/admin/manage_'.$resource.'.php', '/admin/'.$resource, 301);
}
