<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SkpdController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\SubActivityController;
use App\Http\Controllers\FiscalYearController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('skpds', SkpdController::class);
    Route::resource('programs', ProgramController::class)->except('destroy');
    Route::resource('activities', ActivityController::class)->except('destroy');
    Route::resource('sub-activities', SubActivityController::class)->except('destroy');
    Route::resource('fiscal-years', FiscalYearController::class);
    Route::post(
        'fiscal-years/{fiscalYear}/activate',
        [FiscalYearController::class,'activate']
        )->name('fiscal-years.activate');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
