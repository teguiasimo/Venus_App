<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/users', 'users.index');
Volt::route('/register', 'register');
Volt::route('/login', 'login')->name('login');

// Define the logout
Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
});

// Protected routes here
Route::middleware('auth')->group(function () {
    Volt::route('/', 'index');
    Volt::route('/student','student' )->name('student');
    Volt::route('/annee_scolaire', 'annee_scolaire')->name('annee');
    Volt::route('/correction', 'correction')->name('correction');
    Volt::route('/about', 'about')->name('about');







    Volt::route('/users/create', 'users.create')->name('correction');
    Volt::route('/users/{user}/edit', 'users.edit');
    // Volt::route('/', 'users.index');
    // ... more
});
