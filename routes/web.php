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
    Volt::route('/student', 'student')->name('student');
    Volt::route('/annees-scolaires', 'pages.school-years.index')->name('school-years.index');
    Volt::route('/etablissements', 'pages.establishments.index')->name('establishments.index');
    Volt::route('/corrections', 'pages.corrections.index')->name('corrections.index');
    Volt::route('/corrections/{batch}', 'pages.corrections.show')->name('corrections.show');
    Volt::route('/classes', 'pages.classrooms.index')->name('classrooms.index');
    Volt::route('/classes/{classroom}', 'pages.classrooms.show')->name('classrooms.show');
    Volt::route('/about', 'about')->name('about');

    Volt::route('/users/create', 'users.create')->name('users.create');
    Volt::route('/users/{user}/edit', 'users.edit')->name('users.edit');
    // Volt::route('/', 'users.index');
    // ... more
});
