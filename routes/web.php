<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::redirect('/dashboard', '/admin/websites')->name('dashboard');
});

Route::redirect('/login', '/admin/login')->name('login');

require __DIR__.'/settings.php';
