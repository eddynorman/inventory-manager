<?php

namespace App\Livewire\Reports;

use Carbon\Carbon;
use Livewire\Component;
use App\Services\Reports\SalesReportService;
use App\Services\Reports\StockReportService;

class ReportsIndex extends Component
{
    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    public $fromDate;

    public $toDate;

    public $selectedDepartment = null;

    public $selectedLocation = null;

    /*
    |--------------------------------------------------------------------------
    | REPORT SELECTION
    |--------------------------------------------------------------------------
    */

    public $showReportSelector = true;

    public $activeReport = null;
    public $activeReportName = null;
    public $activeReportDescription = null;

    public $group_permissions = [
        'sales_reports' => 'reports.view_sales',
        'inventory_reports' => 'reports.view_inventory',
        'assets_reports' => 'reports.view_assets',
        'expenses_reports' => 'reports.view_expenses',
        'purchases_reports' => 'reports.view_purchases',
        'banking_reports' => 'reports.view_banking',
        'financial_reports' => 'reports.view_financial',
    ];

    public $reportGroups = [

        'sales_reports' => [

            'title' => 'Sales Reports',

            'icon' => 'bi bi-cash-stack',

            'reports' => [

                'general_sales' => [
                    'title' => 'General Sales',
                    'description' => 'Overall sales, payments and profitability',
                    'icon' => 'bi bi-bar-chart',
                ],

                'sold_items' => [
                    'title' => 'Sold Items',
                    'description' => 'Item sales and profitability',
                    'icon' => 'bi bi-box-seam',
                ],

                'sold_kits' => [
                    'title' => 'Sold Kits',
                    'description' => 'Recipe and kit performance',
                    'icon' => 'bi bi-grid',
                ],

                'used_items' => [
                    'title' => 'Used Items',
                    'description' => 'Inventory consumption analytics',
                    'icon' => 'bi bi-arrow-left-right',
                ],

                'individual_sales' => [
                    'title' => 'Individual Sales',
                    'description' => 'Sales contribution by staff',
                    'icon' => 'bi bi-people',
                ],

            ],

        ],

        'inventory_reports' => [

            'title' => 'Inventory Reports',

            'icon' => 'bi bi-boxes',

            'reports' => [

                'stock_movement' => [
                    'title' => 'Stock Movement',
                    'description' => 'Inventory movement and balances',
                    'icon' => 'bi bi-arrow-left-right',
                ],

                'stock_valuation' => [
                    'title' => 'Stock Valuation',
                    'description' => 'Current inventory value analysis',
                    'icon' => 'bi bi-cash-coin',
                ],

                'low_stock' => [
                    'title' => 'Low Stock',
                    'description' => 'Items below minimum levels',
                    'icon' => 'bi bi-exclamation-triangle',
                ],

                'negative_stock' => [
                    'title' => 'Negative Stock',
                    'description' => 'Inventory anomalies and variances',
                    'icon' => 'bi bi-dash-circle',
                ],

            ],

        ],

    ];

    /*
    |--------------------------------------------------------------------------
    | REPORT DATA
    |--------------------------------------------------------------------------
    */
    public $departments = [];

    public $locations = [];

    public $summary;

    public $soldItems = [];

    public $soldKits = [];

    public $usedItems = [];

    public $stockMovement = [];

    public $stockValuation = [];

    public $lowStockItems = [];

    public $negativeStockItems = [];

    /*
    |--------------------------------------------------------------------------
    | GROUPED DATA
    |--------------------------------------------------------------------------
    */

    public $groupedSoldItems = [];

    public $groupedSoldKits = [];

    public $groupedUsedItems = [];

    public $generalSummary;

    public $departmentSummaries = [];

    public $paymentSummary = [];

    public $salesList = [];

    public $individualSales = [];

    /*
    |--------------------------------------------------------------------------
    | MOUNT
    |--------------------------------------------------------------------------
    */

    public function mount()
    {
        $this->fromDate = now()
            ->startOfDay()
            ->format('Y-m-d\TH:i');

        $this->toDate = now()
            ->endOfDay()
            ->format('Y-m-d\TH:i');

        $this->departments =
            \App\Models\Department::all();

        $this->locations =
            \App\Models\Location::all();

    }

    /*
    |--------------------------------------------------------------------------
    | CHANGE REPORT
    |--------------------------------------------------------------------------
    */

    public function changeReport($report,$name,$desc)
    {
        $this->activeReport = $report;
        $this->activeReportName = $name;
        $this->activeReportDescription = $desc;

        $this->showReportSelector = false;

        $this->resetReportData();

        $this->loadReport();
    }

    public function backToReports()
    {
        $this->showReportSelector = true;

        $this->activeReport = null;

        $this->activeReportName = null;

        $this->activeReportDescription = null;

        $this->resetReportData();
    }

    /*
    |--------------------------------------------------------------------------
    | RESET REPORT DATA
    |--------------------------------------------------------------------------
    */

    private function resetReportData()
    {
        $this->summary = null;

        $this->soldItems = [];

        $this->soldKits = [];

        $this->usedItems = [];

        $this->groupedSoldItems = [];

        $this->groupedSoldKits = [];

        $this->groupedUsedItems = [];

        $this->individualSales = [];
    }

    /*
    |--------------------------------------------------------------------------
    | LOAD REPORT
    |--------------------------------------------------------------------------
    */

    public function loadReport()
    {
        $this->validate([
            'fromDate' => [
                'required',
                'date',
                'before_or_equal:now',
            ],

            'toDate' => [
                'required',
                'date',
                'after_or_equal:fromDate',
            ],

        ], [

            'fromDate.before_or_equal' =>
                'The start date cannot be in the future.',

            'toDate.after_or_equal' =>
                'The end date must be greater than or equal to the start date.',

        ]);

        $filters = [
            'from_date' => Carbon::parse($this->fromDate),
            'to_date' => Carbon::parse($this->toDate),

            'department_id' => $this->selectedDepartment,
            'location_id' => $this->selectedLocation,
        ];

        $service = app(SalesReportService::class);
        $stockService = app(StockReportService::class);

        switch ($this->activeReport) {

            case 'sold_items':

                $this->soldItems = $service
                    ->soldItemsReport($filters);

                $this->groupedSoldItems = collect($this->soldItems)
                    ->groupBy('department_name');

                $this->summary = $service
                    ->soldItemsSummary($filters);

                break;

            case 'sold_kits':

                $this->soldKits = $service
                    ->soldKitsReport($filters);

                $this->groupedSoldKits = collect($this->soldKits)
                    ->groupBy('department_name');

                $this->summary = $service
                    ->soldKitsSummary($filters);

                break;

            case 'used_items':

                $this->usedItems = $service
                    ->usedItemsReport($filters);

                $this->groupedUsedItems = collect($this->usedItems)
                    ->groupBy('department_name');

                $this->summary = $service
                    ->usedItemsSummary($filters);

                break;

            case 'general_sales':

                $this->generalSummary = $service
                    ->generalSalesSummary($filters);

                $this->departmentSummaries = $service
                    ->departmentSalesSummary($filters);

                $this->paymentSummary = $service
                    ->paymentSummary($filters);

                $this->salesList = $service
                    ->salesList($filters);

                break;

            case 'individual_sales':

                $this->individualSales = $service
                    ->individualSalesReport($filters);

                break;
            case 'stock_movement':

                $this->stockMovement =
                    $stockService->stockMovementReport($filters);

                break;

            case 'stock_valuation':

                $this->stockValuation =
                    $stockService->stockValuationReport($filters);

                break;

            case 'low_stock':

                $this->lowStockItems =
                    $stockService->lowStockReport();

                break;

            case 'negative_stock':

                $this->negativeStockItems =
                    $stockService->negativeStockReport();

                break;
        }
    }

    public function render()
    {
        return view('livewire.reports.reports-index');
    }
}
