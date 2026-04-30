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
    public string $rejectionReason ="";

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

    public function boot(TransferService $service){
        $this->service = $service;
        $this->locations = Location::all()->toArray();
        $this->current_user_id = Auth::id();
    }

    public function updatedShowCreatePage(){
        if($this->showCreatePage == false){
            $this->reset(['transferItems','locationId','issues','showCreatePage','selectedIssue','user_id']);
        }
    }

    public function updatedTransferItems($value,$keys){
        $index = explode('.',$keys)[0];
        $key = explode('.',$keys)[1];
        $unit = [];
        foreach($this->transferItems[$index]['units'] as $u){
            if($this->transferItems[$index]['selected_unit_id'] == $u['id']){
                $unit = $u;
            }
        }
        $stock = $this->service->getItemStock($this->transferItems[$index]['item_id'],$this->locationId);
        if($key == 'quantity'){
            if($value != ''){
                $q = $value * $unit['smallest_units_number'];
                if($q > $stock){
                    $this->addError("transferItems.$index.quantity", "Cannot exceed stock");
                    return;
                }
                $this->resetErrorBag("transferItems.$index.quantity");
            }
        }
        if($key == 'selected_unit_id'){
            $q = $this->transferItems[$index]['quantity'] * $unit['smallest_units_number'];

            if($q > $stock){
                $this->addError("transferItems.$index.quantity", "Cannot exceed stock");
                return;
            }
            $this->resetErrorBag("transferItems.$index.quantity");
        }
    }

    public function updatedLocationId(){
        if($this->locationId != null && $this->locationId != ""){
            $this->issues = $this->service->loadIssues($this->locationId)->toArray();
            if(count($this->issues) == 0){
                session()->flash('warning','No issues to this location!');
                $this->dispatch('flash');
            }
            $this->resetErrorBag('locationId');
        }else{
            $this->reset(['issues','transferItems']);
        }
    }

    public function updatedShowRejectIssue(){
        if($this->showRejectIssue == true){
            $this->showCreatePage = false;
            $this->transferItems = [];
        }
    }

    public function rejectIssue(){
        if(trim($this->rejectionReason != "")){
            $this->resetErrorBag('rejectionReason');
            if($this->selectedIssue != null && $this->selectedIssue != ""){
                try {
                    $this->service->rejectIssue($this->selectedIssue,$this->rejectionReason);
                    $this->reset(['selectedIssue','showRejectIssue','rejectionReason','locationId','issues']);
                    session()->flash('success','Issue Marked As Rejected');
                    $this->dispatch('flash');
                } catch (\Throwable $th) {
                    session()->flash('error',$th->getMessage());
                    $this->dispatch('flash');
                }

            }else{
                //dd($this->selectedIssue);
            }
        }else{
            $this->addError('rejectionReason','Reason is required');
        }
    }

    public function removeItem(int $itemId): void
    {

        foreach($this->transferItems as $index => $item){
            if($item['item_id'] == $itemId){
                unset($this->transferItems[$index]);
                break;
            }
        }
        if(count($this->transferItems) < 1){
            $this->reset(['transferItems','locationId','issues','showCreatePage','selectedIssue']);
            return;
        }
    }
    public function newTransfer(){
        $this->showCreatePage = true;
    }
    public function updatedSelectedIssue(){
        if($this->locationId == null || $this->locationId == ""){
            session()->flash('error',"Select Your Location First");
            $this->dispatch('flash');
            $this->addError("locationId", "Please select your Location!");
            return;
        }
        if($this->selectedIssue != null && $this->selectedIssue != ""){
            $issue = $this->service->getIssue($this->selectedIssue);
            $this->user_id = $issue->user_id;
            $this->destinationId = $issue->from_location_id;
            foreach($issue->items as $item){
                $unit = Unit::find($item->unit_id);
                $temp = [];
                $temp['item_id'] = $item->item_id;
                $temp['name'] = $item->item->name;
                $temp['requested_unit'] = $unit->name;
                $temp['requested_quantity'] = $item->quantity;
                $temp['unit'] = $unit->name;
                $temp['quantity'] = $item->quantity;
                $temp['selected_unit_id'] = $item->unit_id;
                $temp['units'] = $item->item->units()->get()->toArray();
                $this->transferItems[] = $temp;
            }
        }else{
            $this->reset(['transferItems','user_id']);
        }
    }

    public function showTransferInfo(int $transferId){
        $transfer = $this->service->getById($transferId);
        $this->user_id = $transfer->user_id;
        $this->transferId = $transfer->id;
        $this->viewTransfer['next'] = match ($transfer->status) {
            'pending'   => 'Awaiting Receiving',
            'received'  => 'completed',
            default     => '',
        };
        $this->viewTransfer['color'] = match ($transfer->status) {
            'pending'   => 'warning',
            'received'  => 'success',
            default     => 'secondary',
        };
        $this->viewTransfer['status'] = $transfer->status;
        $this->viewTransfer['transfer_number'] = str_pad($transfer->id,5,'0',STR_PAD_LEFT);
        $this->viewTransfer['source'] = $transfer->fromLocation->name;
        $this->viewTransfer['destination'] = $transfer->toLocation->name;
        $this->viewTransfer['processed_by'] = $transfer->user->name;
        $this->viewTransfer['processed_at'] = optional($transfer->created_at)->format('d/m/Y H:i');
        if($transfer->receivedBy){
            $this->viewTransfer['received_by'] = $transfer->receivedBy->name;
            $this->viewTransfer['received_at'] = Carbon::parse($transfer->updated_at)->format('d/m/Y H:i');
        }

        $this->viewTransfer['items'] = [];
        foreach($transfer->items as $item){
            $temp = [];
            $unit = Unit::find($item->unit_id);
            $temp['name'] = $item->item->name;
            $temp['unit'] = $unit->name;
            $temp['quantity'] = $item->quantity;
            $this->viewTransfer['items'][] = $temp;
        }
        $this->showViewTransfer = true;
    }

    public function receiveTransfer(){
        try {
            $this->service->receiveTransfer($this->transferId);
            $this->showViewTransfer = false;
            $this->viewTransfer = [];
            $this->transferId = null;
            session()->flash('success',"Transfer Received!");
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            session()->flash('error',$th->getMessage());
            $this->dispatch('flash');
        }
    }

    public function save(){
        $hasErrors = false;
        foreach ($this->transferItems as $index => $item) {
            $unit = [];
            $stock = $this->service->getItemStock($item['item_id'],$this->locationId);
            foreach($this->transferItems[$index]['units'] as $u){
                if($item['selected_unit_id'] == $u['id']){
                    $unit = $u;
                    $q = 0;
                    if($item['quantity'] != ''){
                        $q = $item['quantity'] * ($unit['smallest_units_number']);
                    }else{
                        $hasErrors = true;
                        $this->addError("transferItems.$index.quantity", "Cannot be empty");
                    }

                    if($q > $stock){
                        $this->addError("transferItems.$index.quantity", "Cannot exceed stock");
                        $hasErrors = true;
                    }
                }
            }
        }

        if($hasErrors == true){
            return;
        }
        $data = $this->validate($this->service->rules());
        try {
            $this->service->save($data,$this->transferId);
            $this->refreshTable();
            $this->reset(['transferItems','locationId','destinationId','transferId']);
            $this->showCreatePage = false;
            session()->flash('success',"Transfer Saved!");
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            session()->flash('error',$th->getMessage());
            $this->dispatch('flash');
        }
    }

    public function refreshTable(){
        $this->dispatch('pg:eventRefresh-transfer-table-olw5tk-table');
    }

    public function render()
    {
        return view('livewire.transfer-manager');
    }
}
