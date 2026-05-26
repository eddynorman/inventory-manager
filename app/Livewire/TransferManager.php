<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\Unit;
use App\Services\TransferService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TransferManager extends Component
{
    public array $locations = [];
    public array $issues = [];

    public ?int $selectedIssue = null;
    public ?int $transferId = null;
    public ?int $locationId = null;
    public ?int $destinationId = null;
    public ?int $user_id = null;
    public ?int $current_user_id = null;

    public array $transferItems = [];
    public array $viewTransfer = [];

    public string $rejectionReason = "";
    public string $description = '';

    public bool $showIndex = true;
    public bool $showCreatePage = false;
    public bool $showViewTransfer = false;
    public bool $showRejectIssue = false;

    private TransferService $service;

    protected $listeners = [
        'viewTransfer' => 'showTransferInfo',
        'receiveTransfer' => 'receiveTransfer'
    ];

    public function boot(TransferService $service)
    {
        $this->service = $service;

        $this->locations = Location::all()->toArray();

        $this->current_user_id = Auth::id();
    }

    /*
    |--------------------------------------------------------------------------
    | MODALS
    |--------------------------------------------------------------------------
    */

    public function updatedShowCreatePage()
    {
        if (!$this->showCreatePage) {

            $this->reset([
                'transferItems',
                'locationId',
                'issues',
                'selectedIssue',
                'user_id',
                'destinationId',
            ]);
        }
    }

    public function updatedShowRejectIssue()
    {
        if ($this->showRejectIssue) {

            $this->showCreatePage = false;

            $this->transferItems = [];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LOCATION
    |--------------------------------------------------------------------------
    */

    public function updatedLocationId()
    {
        $this->reset([
            'selectedIssue',
            'transferItems',
            'destinationId',
            'user_id',
        ]);

        if (!$this->locationId) {

            $this->issues = [];

            return;
        }

        $this->issues = $this->service
            ->loadIssues($this->locationId)
            ->toArray();

        if (count($this->issues) === 0) {

            session()->flash('warning', 'No issues to this location!');

            $this->dispatch('flash');
        }

        $this->resetErrorBag('locationId');
    }

    /*
    |--------------------------------------------------------------------------
    | ISSUE SELECTION
    |--------------------------------------------------------------------------
    */

    public function updatedSelectedIssue()
    {
        if (!$this->locationId) {

            session()->flash(
                'error',
                "Select Your Location First"
            );

            $this->dispatch('flash');

            $this->addError(
                "locationId",
                "Please select your Location!"
            );

            return;
        }

        $this->transferItems = [];

        if (!$this->selectedIssue) {

            $this->destinationId = null;

            $this->user_id = null;

            return;
        }

        $issue = $this->service->getIssue(
            $this->selectedIssue
        );

        $this->user_id = $issue->user_id;

        $this->destinationId = $issue->from_location_id;

        foreach ($issue->items as $item) {

            $unit = Unit::find($item->unit_id);

            $this->transferItems[] = [

                'item_id' => $item->item_id,

                'name' => $item->item->name,

                'requested_unit' => $unit->name,

                'requested_quantity' => $item->quantity,

                'unit' => $unit->name,

                'quantity' => $item->quantity,

                'selected_unit_id' => $item->unit_id,

                'units' => $item->item
                    ->units()
                    ->get()
                    ->toArray(),
            ];
        }

        $this->dispatchTransferErrors();
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    public function updatedTransferItems($value, $keys)
    {
        $parts = explode('.', $keys);

        if (count($parts) < 2) {
            return;
        }

        $index = $parts[0];
        $key = $parts[1];

        if (!isset($this->transferItems[$index])) {
            return;
        }

        $item = $this->transferItems[$index];

        $unit = collect($item['units'])
            ->firstWhere(
                'id',
                $item['selected_unit_id']
            );

        if (!$unit) {
            return;
        }

        $stock = $this->service->getItemStock(
            $item['item_id'],
            $this->locationId
        );

        if (
            $key === 'quantity' ||
            $key === 'selected_unit_id'
        ) {

            $quantity = (float) ($item['quantity'] ?? 0);

            $convertedQty =
                $quantity *
                $unit['smallest_units_number'];

            if ($quantity <= 0) {

                $this->addError(
                    "transferItems.$index.quantity",
                    "Quantity is required"
                );

            } elseif ($convertedQty > $stock) {

                $this->addError(
                    "transferItems.$index.quantity",
                    "Cannot exceed stock"
                );

            } else {

                $this->resetErrorBag(
                    "transferItems.$index.quantity"
                );
            }
        }

        $this->dispatchTransferErrors();
    }

    protected function dispatchTransferErrors(): void
    {
        $this->dispatch(
            'transfer-errors-updated',
            errors: $this->getErrorBag()->toArray()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REMOVE ITEM
    |--------------------------------------------------------------------------
    */

    public function removeItem(int $itemId): void
    {
        foreach ($this->transferItems as $index => $item) {

            if ($item['item_id'] == $itemId) {

                unset($this->transferItems[$index]);

                break;
            }
        }

        $this->transferItems = array_values(
            $this->transferItems
        );

        if (count($this->transferItems) < 1) {

            $this->reset([
                'transferItems',
                'locationId',
                'issues',
                'showCreatePage',
                'selectedIssue',
            ]);
        }

        $this->dispatchTransferErrors();
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function newTransfer()
    {
        $this->showCreatePage = true;
    }

    /*
    |--------------------------------------------------------------------------
    | REJECT
    |--------------------------------------------------------------------------
    */

    public function rejectIssue()
    {
        if (trim($this->rejectionReason) === "") {

            $this->addError(
                'rejectionReason',
                'Reason is required'
            );

            return;
        }

        $this->resetErrorBag('rejectionReason');

        try {

            $this->service->rejectIssue(
                $this->selectedIssue,
                $this->rejectionReason
            );

            $this->reset([
                'selectedIssue',
                'showRejectIssue',
                'rejectionReason',
                'locationId',
                'issues',
            ]);

            session()->flash(
                'success',
                'Issue Marked As Rejected'
            );

            $this->dispatch('flash');

        } catch (\Throwable $th) {

            session()->flash(
                'error',
                $th->getMessage()
            );

            $this->dispatch('flash');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */

    public function showTransferInfo(int $transferId)
    {
        $transfer = $this->service->getById($transferId);

        $this->user_id = $transfer->user_id;

        $this->transferId = $transfer->id;

        $this->viewTransfer['next'] = match (
            $transfer->status
        ) {
            'pending' => 'Awaiting Receiving',
            'received' => 'completed',
            default => '',
        };

        $this->viewTransfer['color'] = match (
            $transfer->status
        ) {
            'pending' => 'warning',
            'received' => 'success',
            default => 'secondary',
        };

        $this->viewTransfer['status'] =
            $transfer->status;

        $this->viewTransfer['transfer_number'] =
            str_pad(
                $transfer->id,
                5,
                '0',
                STR_PAD_LEFT
            );

        $this->viewTransfer['source'] =
            $transfer->fromLocation->name;

        $this->viewTransfer['destination'] =
            $transfer->toLocation->name;

        $this->viewTransfer['processed_by'] =
            $transfer->user->name;

        $this->viewTransfer['processed_at'] =
            optional($transfer->created_at)
                ->format('d/m/Y H:i');

        if ($transfer->receivedBy) {

            $this->viewTransfer['received_by'] =
                $transfer->receivedBy->name;

            $this->viewTransfer['received_at'] =
                Carbon::parse($transfer->updated_at)
                    ->format('d/m/Y H:i');
        }

        $this->viewTransfer['items'] = [];

        foreach ($transfer->items as $item) {

            $unit = Unit::find($item->unit_id);

            $this->viewTransfer['items'][] = [

                'name' => $item->item->name,

                'unit' => $unit->name,

                'quantity' => $item->quantity,
            ];
        }

        $this->showViewTransfer = true;
    }

    /*
    |--------------------------------------------------------------------------
    | RECEIVE
    |--------------------------------------------------------------------------
    */

    public function receiveTransfer()
    {
        try {

            $this->service->receiveTransfer(
                $this->transferId
            );

            $this->showViewTransfer = false;

            $this->viewTransfer = [];

            $this->transferId = null;

            $this->refreshTable();

            session()->flash(
                'success',
                "Transfer Received!"
            );

            $this->dispatch('flash');

        } catch (\Throwable $th) {

            session()->flash(
                'error',
                $th->getMessage()
            );

            $this->dispatch('flash');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE
    |--------------------------------------------------------------------------
    */

    public function save()
    {
        $hasErrors = false;

        foreach (
            $this->transferItems as $index => $item
        ) {

            $stock = $this->service->getItemStock(
                $item['item_id'],
                $this->locationId
            );

            $unit = collect($item['units'])
                ->firstWhere(
                    'id',
                    $item['selected_unit_id']
                );

            if (!$unit) {
                continue;
            }

            if (
                !isset($item['quantity']) ||
                $item['quantity'] === ''
            ) {

                $this->addError(
                    "transferItems.$index.quantity",
                    "Cannot be empty"
                );

                $hasErrors = true;

                continue;
            }

            $qty =
                $item['quantity'] *
                $unit['smallest_units_number'];

            if ($qty > $stock) {

                $this->addError(
                    "transferItems.$index.quantity",
                    "Cannot exceed stock"
                );

                $hasErrors = true;
            }
        }

        $this->dispatchTransferErrors();

        if ($hasErrors) {
            return;
        }

        $data = $this->validate(
            $this->service->rules()
        );

        try {

            $this->service->save(
                $data,
                $this->transferId
            );

            $this->refreshTable();

            $this->reset([
                'transferItems',
                'locationId',
                'destinationId',
                'transferId',
            ]);

            $this->showCreatePage = false;

            session()->flash(
                'success',
                "Transfer Saved!"
            );

            $this->dispatch('flash');

        } catch (\Throwable $th) {

            session()->flash(
                'error',
                $th->getMessage()
            );

            $this->dispatch('flash');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    public function refreshTable()
    {
        $this->dispatch(
            'pg:eventRefresh-transfer-table-olw5tk-table'
        );
    }

    public function render()
    {
        return view(
            'livewire.transfer-manager'
        );
    }
}
