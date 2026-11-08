<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $preferred = request()->getPreferredLanguage(SetLocale::LOCALES) ?? 'ar';

    return redirect()->route('home', ['locale' => $preferred]);
});

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

Route::prefix('{locale}')
    ->whereIn('locale', SetLocale::LOCALES)
    ->middleware(SetLocale::class)
    ->group(function () {
        Route::get('/', [PageController::class, 'home'])->name('home');
        Route::get('about', [PageController::class, 'about'])->name('about');
        Route::get('services', [PageController::class, 'services'])->name('services');
        Route::get('gallery', [PageController::class, 'gallery'])->name('gallery');
        Route::get('privacy', [PageController::class, 'privacy'])->name('privacy');
        Route::get('terms', [PageController::class, 'terms'])->name('terms');

        Route::get('rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::get('rooms/{roomType}', [RoomController::class, 'show'])->name('rooms.show');

        Route::get('booking', [BookingController::class, 'create'])->name('booking.create');
        Route::post('booking/quote', [BookingController::class, 'quote'])->middleware('throttle:60,1')->name('booking.quote');
        Route::post('booking', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('booking.store');
        Route::get('booking/{reference}', [BookingController::class, 'show'])->name('booking.show');

        Route::get('contact', [ContactController::class, 'show'])->name('contact');
        Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
    });
