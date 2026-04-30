<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\Unit;
use App\Services\IssueService;
use App\Services\ItemService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class IssueManager extends Component
{
    public string $search = '';
    public array $searchItems = [];
    public array $locations = [];
    public ?int $user_id = null;
    public ?int $current_user_id = null;
    public ?int $issueId = null;
    public ?int $locationId = null;
    public ?int $destinationId = null;
    public array $issueItems = [];
    public array $viewIssue = [];

    public string $description = '';
    public string $rejectionReason ="";

    public bool $showIndex = true;
    public bool $showCreatePage = false;
    public bool $showViewIssue = false;
    public bool $showRejectIssue = false;

    private IssueService $service;
    protected ItemService $itemService;

    protected $listeners = [
        'viewIssue' => 'showIssueInfo',
    ];

    public function boot(IssueService $service, ItemService $itemService){
        $this->service = $service;
        $this->itemService = $itemService;
        $this->locations = Location::all()->toArray();
        $this->current_user_id = Auth::id();
    }

    public function updatedSearch(){
        if($this->destinationId == null){
            session()->flash('warning','Select Destination First');
            $this->dispatch('flash');
            $this->search = '';
        }else if($this->locationId == null){
            session()->flash('warning','Select Source First');
            $this->dispatch('flash');
            $this->search = '';
        }else{
            if($this->destinationId == $this->locationId){
                session()->flash('error','Same source and destination location!');
                $this->dispatch('flash');
                $this->search = '';
                return;
            }
            if($this->search != ''){
                $this->searchItems = $this->service->search($this->destinationId,$this->search);
            }

        }

    }

    public function updatedShowCreatePage(){
        if($this->showCreatePage == false){
            $this->reset(['issueItems','locationId','destinationId']);
        }
    }

    public function updatedShowViewIssue(){
        if($this->showViewIssue == false){
            $this->reset(['viewIssue','issueId','user_id']);
        }
    }

    public function updatedIssueItems($value,$keys){
        $index = explode('.',$keys)[0];
        $key = explode('.',$keys)[1];
        $unit = [];
        foreach($this->issueItems[$index]['units'] as $u){
            if($this->issueItems[$index]['selected_unit_id'] == $u['id']){
                $unit = $u;
            }
        }

        if($key == 'quantity'){
            if($value != ''){
                $q = $value * $unit['smallest_units_number'];
                if($q > $this->issueItems[$index]['stock']){
                    $this->addError("issueItems.$index.quantity", "Cannot exceed stock");
                    return;
                }
                $this->resetErrorBag("issueItems.$index.quantity");
            }
        }
        if($key == 'selected_unit_id'){
            $q = $this->issueItems[$index]['quantity'] * $unit['smallest_units_number'];
            if($q > $this->issueItems[$index]['stock']){
                $this->addError("issueItems.$index.quantity", "Cannot exceed stock");
                return;
            }
            $this->resetErrorBag("issueItems.$index.quantity");
        }
    }

    public function addItem(int $itemId): void
    {
        $item = collect($this->searchItems)->where('item_id',$itemId)->values()->first();
        //if item is already in the list, update the quantity
        $found = false;
        foreach($this->issueItems as $index => $i){
            if($i['item_id'] == $itemId){
                $this->issueItems[$index]['quantity'] += 1;
                $found = true;
                break;
            }
        }
        if(!$found){
            $this->issueItems = array_values(
                array_merge([$item], $this->issueItems)
            );
        }
        $this->search = '';
        $this->searchItems = [];
        $this->resetErrorBag("issueItems");
    }
    public function removeItem(int $itemId): void
    {

        foreach($this->issueItems as $index => $item){
            if($item['item_id'] == $itemId){
                unset($this->issueItems[$index]);
                break;
            }
        }
    }
    public function newIssue(){

        $this->showCreatePage = true;
    }

    public function showIssueInfo(int $issueId){
        $issue = $this->service->getById($issueId);
        $this->issueId = $issueId;
        $this->user_id = $issue->user_id;
        $this->viewIssue['next'] = match ($issue->status) {
            'pending'   => 'Awaiting Processing',
            'processed'  => 'completed',
            'rejected'  => 'Process Terminated',
            default     => '',
        };
        $this->viewIssue['color'] = match ($issue->status) {
            'pending'   => 'warning',
            'processed'  => 'success',
            'rejected'  => 'danger',
            default     => 'secondary',
        };
        $this->viewIssue['status'] = $issue->status;
        $this->viewIssue['issue_number'] = str_pad($issue->id,5,'0',STR_PAD_LEFT);
        $this->viewIssue['source'] = $issue->fromLocation->name;
        $this->viewIssue['destination'] = $issue->toLocation->name;
        $this->viewIssue['requested_by'] = $issue->user->name;
        $this->viewIssue['requested_at'] = optional($issue->created_at)->format('d/m/Y H:i');
        if($issue->processedBy != null){
            $this->viewIssue['processed_by'] = $issue->processedBy->name;
            $this->viewIssue['processed_at'] = Carbon::parse($issue->processed_at)->format('d/m/Y H:i');
        }
        //dd($issue->rejected_at);
        if($issue->rejectedBy != null){
            $this->viewIssue['rejected_by'] = $issue->rejectedBy->name;
            $this->viewIssue['rejected_at'] = Carbon::parse($issue->rejected_at)->format('d/m/Y H:i');
            $this->viewIssue['rejection_reason'] = $issue->rejection_reason;
        }
        $this->viewIssue['items'] = [];
        foreach($issue->items as $item){
            $temp = [];
            $unit = Unit::find($item->unit_id);
            $temp['name'] = $item->item->name;
            $temp['unit'] = $unit->name;
            $temp['quantity'] = $item->quantity;
            $this->viewIssue['items'][] = $temp;
        }
        $this->showViewIssue = true;
    }
    public function updatedShowRejectIssue(){
        if($this->showRejectIssue == true){
            $this->showViewIssue = false;
            $this->viewIssue = [];
        }
    }
    public function rejectIssue(){
        if(trim($this->rejectionReason != "")){
            $this->resetErrorBag('rejectionReason');
            if($this->issueId != null && $this->issueId != ""){
                try {
                    $this->service->rejectIssue($this->issueId,$this->rejectionReason);
                    $this->reset(['issueId','showRejectIssue','rejectionReason','locationId','user_id']);
                    session()->flash('success','Issue Marked As Rejected');
                    $this->dispatch('flash');
                } catch (\Throwable $th) {
                    session()->flash('error',$th->getMessage());
                    $this->dispatch('flash');
                }

            }else{
                //dd($this->issueId);
            }
        }else{
            $this->addError('rejectionReason','Reason is required');
        }
    }

    public function save(){
        $hasErrors = false;
        foreach ($this->issueItems as $index => $item) {
            $unit = [];
            foreach($this->issueItems[$index]['units'] as $u){
                if($item['selected_unit_id'] == $u['id']){
                    $unit = $u;
                    if($item['quantity'] != ''){
                        $q = $item['quantity'] * ($unit['smallest_units_number']);
                    }else{
                        $hasErrors = true;
                        $this->addError("issueItems.$index.quantity", "Cannot be empty");
                    }

                    if($q > $this->issueItems[$index]['stock']){
                        $this->addError("issueItems.$index.quantity", "Cannot exceed stock");
                        $hasErrors = true;
                    }
                }
            }
        }
        if($this->destinationId == $this->locationId){
            session()->flash('error','Same source and destination location!');
            $this->dispatch('flash');
            $this->search = '';
            $hasErrors = true;
        }
        if($hasErrors){
            return;
        }
        $data = $this->validate($this->service->rules());
        try {
            $this->service->save($data,$this->issueId);
            $this->refreshTable();
            $this->reset(['issueItems','locationId','destinationId','issueId']);
            $this->showCreatePage = false;

        } catch (\Throwable $th) {
            session()->flash($th->getMessage());
            $this->dispatch('flash');
        }
    }

    public function refreshTable(){
        $this->dispatch('pg:eventRefresh-issue-table-ffsf1p-table');
    }

    public function render()
    {
        return view('livewire.issue-manager');
    }
}
