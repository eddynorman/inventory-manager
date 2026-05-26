<?php

namespace App\Livewire;

use App\Models\Item;
use App\Services\StockAdjustmentService;
use Livewire\Component;

class StockAdjustmentManager extends Component
{
    public ?int $location_id = null;

    public array $locations = [];

    public $viewAdjustmentData = null;

    public string $search = '';

    public array $item_list = [];

    public array $adjustment_items = [];

    public array $validated_data = [];

    public string $description = '';

    public bool $showIndexPage = true;

    public bool $showAdjustmentCreationPage = false;

    public bool $showConfirmSave = false;

    public bool $showViewAdjustment = false;

    private StockAdjustmentService $service;

    protected $listeners = [
        'viewAdjustment' => 'view',
        'setAdjustmentItems',
    ];

    public function boot(StockAdjustmentService $service)
    {
        $this->service = $service;

        $this->locations = $this->service
            ->loadLocations()
            ->toArray();
    }

    public function resetForm()
    {
        $this->reset([
            'location_id',
            'search',
            'item_list',
            'adjustment_items',
            'description',
        ]);
    }

    public function updatedSearch()
    {
        $this->search = trim($this->search);

        $this->item_list = [];

        if (
            !$this->location_id ||
            empty($this->search)
        ) {
            return;
        }

        $items = $this->searchItems()
            ->toArray();

        foreach ($items as $temp_i) {

            $locationItem = $temp_i['location_items'][0] ?? null;

            if (!$locationItem) {
                continue;
            }

            $this->item_list[] = [

                'item_id' => $temp_i['id'],

                'name' => $temp_i['name'],

                'quantity' => (float) $locationItem['quantity'],

                'adjustment_qty' => 0,

                'id' => $locationItem['id'],

                'reason' => '',
            ];
        }
    }

    public function searchItems()
    {
        return Item::query()

            ->select('id', 'name')

            ->where('name', 'like', "%{$this->search}%")

            ->whereHas('locationItems', function ($q) {

                $q->where(
                    'location_id',
                    $this->location_id
                );
            })

            ->with([
                'locationItems' => function ($q) {

                    $q->select(
                        'id',
                        'item_id',
                        'location_id',
                        'quantity'
                    )->where(
                        'location_id',
                        $this->location_id
                    );
                }
            ])

            ->limit(10)

            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Alpine Sync
    |--------------------------------------------------------------------------
    */

    public function setAdjustmentItems(array $items)
    {
        $this->adjustment_items = collect($items)

            ->map(function ($item) {

                return [

                    'item_id' => $item['item_id'],

                    'name' => $item['name'],

                    'quantity' => (float) $item['quantity'],

                    'adjustment_qty' =>
                        is_numeric($item['adjustment_qty'])
                            ? (float) $item['adjustment_qty']
                            : 0,

                    'id' => $item['id'],

                    'reason' => $item['reason'] ?? '',
                ];
            })

            ->values()

            ->toArray();
    }

    public function validateAdjustments()
    {
        foreach ($this->adjustment_items as $key => $item) {

            $newStock =
                $item['quantity']
                +
                $item['adjustment_qty'];

            if ($newStock < 0) {

                $this->addError(
                    "adjustment_items.$key.adjustment_qty",
                    "Adjustment exceeds available stock."
                );
            }

            if (
                empty(trim($item['reason'] ?? ''))
            ) {

                $this->addError(
                    "adjustment_items.$key.reason",
                    "Reason is required."
                );
            }
        }
    }

    public function confirmSave()
    {
        $this->resetErrorBag();

        $this->validateAdjustments();

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $this->validated_data = $this->validate(
            $this->service->rules()
        );

        $this->showConfirmSave = true;
    }

    public function refreshTable()
    {
        $this->dispatch(
            'pg:eventRefresh-stock-adjustment-table-qovu0z-table'
        );
    }

    public function save()
    {
        try {

            $this->service->save(
                $this->validated_data
            );

            $this->reset();

            $this->refreshTable();

            $this->showAdjustmentCreationPage = false;

            $this->showIndexPage = true;

            session()->flash(
                'success',
                'Adjustment saved successfully!'
            );

            $this->dispatch('flash');

        } catch (\Throwable $th) {

            report($th);

            session()->flash(
                'error',
                'Fatal error occurred!'
            );

            $this->dispatch('flash');
        }
    }

    public function create()
    {
        $this->showAdjustmentCreationPage = true;

        $this->showIndexPage = false;
    }

    public function view(int $id)
    {
        $this->viewAdjustmentData =
            $this->service->getById($id);

        $this->showIndexPage = false;

        $this->showViewAdjustment = true;
    }

    public function updatedShowViewAdjustment()
    {
        if (!$this->showViewAdjustment) {

            $this->showIndexPage = true;
        }
    }

    public function updatedShowAdjustmentCreationPage()
    {
        if (!$this->showAdjustmentCreationPage) {

            $this->showIndexPage = true;

            $this->reset();
        }
    }

    public function render()
    {
        return view(
            'livewire.stock-adjustment-manager'
        );
    }
}
