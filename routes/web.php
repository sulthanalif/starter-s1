<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Spatie\Activitylog\Models\Activity;

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Volt::route('/login', 'login')->name('login');
});

Route::get('/coba', function (){
    return response()->json(Activity::with('causer')->get()->last());
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

    //logs activity
    Volt::route('/logs', 'cms.logs')->middleware('can:log-page')->name('logs');
});
