<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\CategoryManager;
use App\Livewire\ItemManager;
use App\Livewire\SupplierManager;
use App\Livewire\CustomerManager;
use App\Livewire\LocationManager;
use App\Livewire\UnitManager;
use App\Livewire\ItemKitManager;
use App\Livewire\RequisitionManager;
use App\Livewire\PurchaseManager;
use App\Livewire\ReceivingManager;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::post('logout', \App\Livewire\Actions\Logout::class)
    ->middleware(['auth'])
    ->name('logout');

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/categories', function () {
        return view('livewire.index_pages.categories-index');
    })->name('categories');
    Route::get('/items', function () {
        return view('livewire.index_pages.items-index');
    })->name('items');
    Route::get('/users', function () {
        return view('livewire.index_pages.users-index');
    })->name('users');
    Route::get('/suppliers', SupplierManager::class)->name('suppliers');
    Route::get('/customers', CustomerManager::class)->name('customers');
    Route::get('/locations', function () {
        return view('livewire.index_pages.locations-index');
    })->name('locations');
    Route::get('/units', function() {
        return view('livewire.index_pages.units-index');
    })->name('units');
    Route::get('/item-kits', ItemKitManager::class)->name('item-kits');
    Route::get('/requisitions', RequisitionManager::class)->name('requisitions');
    Route::get('/purchases', PurchaseManager::class)->name('purchases');
    Route::get('/receivings', ReceivingManager::class)->name('receivings');
});

require __DIR__.'/auth.php';
