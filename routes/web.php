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
use App\Models\Organization;
use App\Models\Sale;
use App\Services\SaleService;
use App\Services\StockBatchService;

Route::redirect('/', '/login');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('/restricted', 'livewire.index_pages.restricted-index')
    ->name('restricted');

Route::post('logout', function (){

    (new Logout())->__invoke();

    return redirect()->route('login');
})->middleware(['auth'])->name('logout');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::view('dashboard', 'dashboard')
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | USER MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::get('/users', function () {
        return view('livewire.index_pages.users-index');
    })
        ->middleware('permission:users.view')
        ->name('users');

    Route::get('/groups', function () {
        return view('livewire.index_pages.groups-index');
    })
        ->middleware('permission:groups.view')
        ->name('groups');

    /*
    |--------------------------------------------------------------------------
    | INVENTORY
    |--------------------------------------------------------------------------
    */

    Route::get('/categories', function () {
        return view('livewire.index_pages.categories-index');
    })
        ->middleware('permission:categories.view')
        ->name('categories');

    Route::get('/departments', function () {
        return view('livewire.index_pages.departments-index');
    })
        ->middleware('permission:departments.view')
        ->name('departments');

    Route::get('/items', function () {
        return view('livewire.index_pages.items-index');
    })
        ->middleware('permission:items.view')
        ->name('items');

    Route::get('/units', function () {
        return view('livewire.index_pages.units-index');
    })
        ->middleware('permission:items.view')
        ->name('units');

    Route::get('/item-kits', function () {
        return view('livewire.index_pages.item-kits-index');
    })
        ->middleware('permission:item_kits.view')
        ->name('item-kits');

    Route::get('/locations', function () {
        return view('livewire.index_pages.locations-index');
    })
        ->middleware('permission:locations.view')
        ->name('locations');

    /*
    |--------------------------------------------------------------------------
    | SUPPLIERS & CUSTOMERS
    |--------------------------------------------------------------------------
    */

    Route::get('/suppliers', SupplierManager::class)
        ->middleware('permission:suppliers.view')
        ->name('suppliers');

    Route::get('/customers', CustomerManager::class)
        ->middleware('permission:customers.view')
        ->name('customers');

    /*
    |--------------------------------------------------------------------------
    | REQUISITIONS
    |--------------------------------------------------------------------------
    */

    Route::get('/requisitions', function () {
        return view('livewire.index_pages.requisitions-index');
    })
        ->middleware('permission:requisitions.view')
        ->name('requisitions');

    /*
    |--------------------------------------------------------------------------
    | PURCHASES
    |--------------------------------------------------------------------------
    */

    Route::get('/purchases', function () {
        return view('livewire.index_pages.purchases-index');
    })
        ->middleware('permission:purchases.view')
        ->name('purchases');

    /*
    |--------------------------------------------------------------------------
    | RECEIVINGS
    |--------------------------------------------------------------------------
    */

    Route::get('/receivings', function () {
        return view('livewire.index_pages.receivings-index');
    })
        ->middleware('permission:receivings.view')
        ->name('receivings');

    /*
    |--------------------------------------------------------------------------
    | STOCK
    |--------------------------------------------------------------------------
    */

    Route::get('/adjustments', function () {
        return view('livewire.index_pages.stock-adjustment-index');
    })
        ->middleware('permission:stock.adjust')
        ->name('adjustments');

    Route::get('/closing-stock', function () {
        return view('livewire.index_pages.closing-stock-index');
    })
        ->middleware('permission:stock.close_day')
        ->name('closing-stock');

    /*
    |--------------------------------------------------------------------------
    | SALES
    |--------------------------------------------------------------------------
    */

    Route::get('/sales', function () {
        return view('livewire.index_pages.sales-index');
    })
        ->middleware('permission:sales.view')
        ->name('sales');

    Route::get('/print/sale/{sale}', function ($saleId) {

        $organisation = Organization::first()->toArray();

        $saleService = new SaleService(new StockBatchService);

        $sale = $saleService->getPrintSaleData($saleId);

        return view(
            'print.sale-receipt2',
            compact('sale', 'organisation')
        );

    })
        ->middleware('permission:sales.view');

    /*
    |--------------------------------------------------------------------------
    | ISSUES & TRANSFERS
    |--------------------------------------------------------------------------
    */

    Route::get('/issues', function () {
        return view('livewire.index_pages.issues-index');
    })
        ->middleware('permission:issues.view')
        ->name('issues');

    Route::get('/transfers', function () {
        return view('livewire.index_pages.transfer-index');
    })
        ->middleware('permission:transfers.view')
        ->name('transfers');

    /*
    |--------------------------------------------------------------------------
    | EXPENSES
    |--------------------------------------------------------------------------
    */

    Route::get('/expenses', function () {
        return view('livewire.index_pages.expense-index');
    })
        ->middleware('permission:expenses.view')
        ->name('expenses');

    /*
    |--------------------------------------------------------------------------
    | ASSETS
    |--------------------------------------------------------------------------
    */

    Route::get('/assets', function () {
        return view('livewire.index_pages.asset-inventory-index');
    })
        ->middleware('permission:items.view')
        ->name('assets');

    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */

    Route::get('/settings', function () {
        return view('livewire.index_pages.settings-index');
    })
        ->middleware('permission:settings.view')
        ->name('settings');

    /*
    |--------------------------------------------------------------------------
    | BANKING
    |--------------------------------------------------------------------------
    */

    Route::get('/banking', function () {
        return view('livewire.index_pages.banking-index');
    })
        ->middleware('permission:reports.view_financial')
        ->name('banking');
});

require __DIR__.'/auth.php';
