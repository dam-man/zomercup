<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


Volt::route('/', 'frontend.sprints')->name('home');
//Volt::route('/', 'frontend.sprints')->name('home');

//Route::view('dashboard', 'dashboard')
//    ->middleware(['auth', 'verified'])
//    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
	Volt::route('athletes', 'athletes')->name('athletes.index');
	Volt::route('timing', 'timing')->name('timing.index');
	Volt::route('sprints', 'sprints')->name('sprints.index');
	Volt::route('sprint/indeling', 'sprintoverview')->name('sprint.overview');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
