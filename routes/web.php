<?php

use App\Livewire\Actions\Logout;
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

Route::view('/', 'welcome')->name('welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::post('logout', function (){

    (new Logout())->__invoke();

    return redirect()->route('welcome');
})->middleware(['auth'])->name('logout');

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/categories', function () {
        return view('livewire.index_pages.categories-index');
    })->name('categories');
    Route::get('/departments', function () {
        return view('livewire.index_pages.departments-index');
    })->name('departments');
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
    Route::get('/item-kits', function () {
        return view('livewire.index_pages.item-kits-index');
    })->name('item-kits');
    Route::get('/requisitions', function(){
        return view('livewire.index_pages.requisitions-index');
    })->name('requisitions');
    Route::get('/purchases', function(){
        return view('livewire.index_pages.purchases-index');
    })->name('purchases');
    Route::get('/receivings', function(){
        return view('livewire.index_pages.receivings-index');
    })->name('receivings');
    Route::get('/adjustments', function(){
        return view('livewire.index_pages.stock-adjustment-index');
    })->name('adjustments');
    Route::get('/sales', function(){
        return view('livewire.index_pages.sales-index');
    })->name('sales');
});

require __DIR__.'/auth.php';
