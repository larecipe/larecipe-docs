<?php

use App\Http\Controllers\PackageController;
use Illuminate\Support\Facades\Route;


Route::view('/', 'home.index')->name('home.index');
Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
Route::get('/packages/submit', [PackageController::class, 'create'])->name('packages.create');
Route::post('/packages/submit', [PackageController::class, 'store'])->name('packages.store');
Route::get('/packages/{vendor}/{project}', [PackageController::class, 'show'])->name('packages.show');