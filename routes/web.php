<?php

use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('organizations', OrganizationController::class)->except(['show']);
    Route::post('/organizations/{organization}/members', [OrganizationController::class, 'addMember'])->name('organizations.members.store');
    Route::delete('/organizations/{organization}/members/{user}', [OrganizationController::class, 'removeMember'])->name('organizations.members.destroy');
});

require __DIR__.'/auth.php';
