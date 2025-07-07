<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


Volt::route('/', 'frontend.home')->name('home');
Volt::route('/sprints', 'frontend.sprints')->name('frontend.sprints');

Route::middleware(['auth'])->group(function () {
	Volt::route('admin/timers', 'timer')->name('timers.index');
	Volt::route('admin/athletes', 'athletes')->name('athletes.index');
	Volt::route('admin/timing', 'timing')->name('timing.index');
	Volt::route('admin/sprints', 'sprints')->name('sprints.index');
	Volt::route('admin/sprint/indeling', 'sprintoverview')->name('sprint.overview');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
