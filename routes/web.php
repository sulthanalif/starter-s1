<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Volt::route('/login', 'login')->name('login');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/logout', function () {
        Auth::logout();
        return redirect()->route('login');
    })->name('logout');

    //profile
    Volt::route('/profile', 'cms.profile')->name('profile');

    //dashboard
    Volt::route('/dashboard', 'cms.dashboard')->can('dashboard-page')->middleware('can:dashboard-page')->name('dashboard');

    //users
    Volt::route('/users', 'cms.users.index')->middleware('can:user-page')->name('users');
});
