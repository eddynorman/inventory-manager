<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\CategoryManager;
use App\Livewire\ItemManager;
use App\Livewire\SupplierManager;
use App\Livewire\CustomerManager;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/categories', CategoryManager::class)->name('categories');
    Route::get('/items', ItemManager::class)->name('items');
    Route::get('/suppliers', SupplierManager::class)->name('suppliers');
    Route::get('/customers', CustomerManager::class)->name('customers');
});

require __DIR__.'/auth.php';
