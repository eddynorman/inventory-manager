<?php

namespace App\Livewire;

use App\Models\Requisition;
use App\Services\DepartmentService;
use App\Services\ItemService;
use App\Services\RequisitionService;
use App\Services\UserService;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
class RequisitionManager extends Component
{
    public ?int $reqId = null;
    public ?int $department_id = null;
    public string $department_name = '';
    public $cost = 0;
    public string $status = 'pending';
    public ?string $description = '';
    public ?string $date_requested = '';
    public ?string $reviewed_on = '';
    public ?string $date_approved = '';
    public ?string $funded_on = '';
    public ?string $rejected_at = '';
    public ?int $requested_by_id = null;
    public string $requested_by_name = '';
    public ?int $reviewed_by = null;
    public string $reviewed_by_name = '';
    public ?int $approved_by_id = null;
    public string $approved_by_name = '';
    public ?int $funded_by = null;
    public string $funded_by_name = '';
    public string $funded_to_name = '';
    public ?int $funded_to_id = null;
    public ?int $rejected_by = null;
    public string $rejected_by_name = '';
    public $fund_amount = 0;
    public string $rejectionReason = "";
    public string $fundErrorMessage = "";

    public array $items = [];
    public array $searchItems = [];
    public array $departments = [];
    public array $users = [];
    public string $search = '';

    public array $steps = [
        'pending' => 1,
        'reviewed' => 2,
        'approved' => 3,
        'funded' => 4,
        'rejected' => 4
    ];

    public int $currentStep = 1;

    public bool $showListTable = true;
    public bool $showCreateEditPage = false;
    public bool $showViewPage = false;
    public bool $showDeleteModal = false;
    public bool $showBulkDeleteModal = false;
    public bool $showRejectionReasonModal = false;
    public bool $showFundAmountEntryModal = false;
    public bool $showFundAmountError = false;
    public bool $canReview = false;
    public bool $canApprove = false;
    public bool $canFund = false;
    public bool $canReject = false;

    protected DepartmentService $departmentService;
    protected RequisitionService $requisitionService;
    protected ItemService $itemService;
    protected UserService $userService;

    protected $listeners = [
        'edit'=> 'edit',
        'view' => 'view',
        'delete' => 'confirmDelete'
    ];

    public function boot(DepartmentService $departmentService, RequisitionService $requisitionService, ItemService $itemService, UserService $userService){
        $this->departmentService = $departmentService;
        $this->requisitionService = $requisitionService;
        $this->itemService = $itemService;
        $this->userService = $userService;
        $this->departments = $this->departmentService->getAll();
        $this->users = $this->userService->getAll();
    }

    public function resetForm(): void
    {
        $this->reset(['reqId','cost','status','description','date_requested','date_approved','requested_by_id','reviewed_by','approved_by_id','funded_by','reviewed_on','funded_on','rejected_at','search','items','department_id']);
        $this->reset(['reviewed_by_name','approved_by_name','funded_by_name','items']);
        $this->reset(['canReview','canApprove','canReject','canFund']);
        $this->status = 'pending';
        $this->resetValidation();
    }

    public function refreshTable(): void
    {
        $this->dispatch('refresh-requisitions-table');
    }

    public function updatedShowCreateEditPage(){
        if($this->showCreateEditPage == false){
            $this->showListTable = true;
            $this->resetForm();
        }
    }

     public function updatedShowViewPage(){
        if($this->showViewPage == false){
            $this->showListTable = true;
            $this->resetForm();
        }
    }

    public function updatedSearch(){
        if($this->department_id == null){
            session()->flash('error','Select Department First');
            $this->dispatch('flash');
        }else{
            $this->searchItems = $this->departmentService->searchItems($this->department_id,$this->search);
        }
        if($this->search == ""){
            $this->searchItems = [];
        }

    }

    public function updatedDepartmentId(){
        if($this->department_id !== null){
            $lowStockItems = $this->departmentService->getLowStockItems($this->department_id);
            foreach($lowStockItems as $item){
                $this->addItem($item['id']);
            }
        }else{
            $this->resetForm();
        }
    }

    public function updatedItems($value, $key)
    {
        $index = explode('.', $key)[0];
        $field = explode('.',$key)[1];

        if($field == 'selected_unit_id'){
            $unit = collect($this->items[$index]['units'])->firstWhere('id', $this->items[$index]['selected_unit_id']);
            $buyingPrice = $unit['buying_price'] ?? null;
            $this->items[$index]['unit_price'] = $buyingPrice;
        }

        if($field == 'unit_price'){
            if($value == null || $value == ''){
                $value = 0.00;
            }
            $this->items[$index]['units'];
            foreach($this->items[$index]['units'] as $index2 => $unit){
                if($unit['id'] == $this->items[$index]['selected_unit_id']){
                    $this->items[$index]['units'][$index2]['buying_price'] = $value;
                }
            }
        }

        if($field == 'quantity'){
            if($value == null || $value == ''){
                $this->items[$index]['quantity'] = 1;
            }
        }

        if($field !== 'total'){
            $this->calculateSubTotal($index);
        }


    }


    /**
     * Adds new item to the list of items in the itemkit.
     * The item is stored in the items array
     */
    public function addItem(int $itemId): void
    {
        $item = $this->itemService->getById($itemId);
        $units = $this->itemService->getUnits($itemId);
        //if item is already in the list, update the quantity
        $found = false;
        foreach($this->items as $index => $i){
            if($i['item_id'] == $itemId){
                $this->items[$index]['quantity'] += 1;
                $found = true;
                $this->calculateSubTotal($index);
                break;
            }
        }
        if(!$found){
            $this->items[] = [
            'item_id' => $itemId,
            'name' => $item->name,
            'units' => $units,
            'quantity' => 2*$item->reorder_level,
            'current_stock' => $item->current_stock,
            'selected_unit_id' => $units[0]['id'],
            'unit_price' => $units[0]['buying_price'],
            'total' => $units[0]['buying_price']*2*$item->reorder_level,
            ];
        }
        $this->search = '';
        $this->searchItems = [];
        $this->calculateCost();
    }
    public function removeItem(int $itemId): void
    {

        foreach($this->items as $index => $item){
            if($item['item_id'] == $itemId){
                unset($this->items[$index]);
                break;
            }
        }
        $this->calculateCost();
    }

    public function calculateSubTotal(int $index){
        $unit = collect($this->items[$index]['units'])->firstWhere('id', $this->items[$index]['selected_unit_id']);

        $buyingPrice = $unit['buying_price'] ?? null;

        $this->items[$index]['total'] = $buyingPrice * $this->items[$index]['quantity'];
        $this->calculateCost();
    }

    public function calculateCost(){
        $sum = 0 ;
        foreach ($this->items as $item) {
            $sum += $item['total'];
        }
        $this->cost = $sum;
    }

    public function create(): void
    {
        $this->resetForm();
        $this->date_requested = now();
        $this->requested_by_id = Auth::id();
        $this->showCreateEditPage = true;
        $this->showListTable = false;
    }

    public function view(int $id){
        $this->canReview = $this->requisitionService->canReview($id);
        $this->canReject = $this->requisitionService->canReject($id);
        $this->reqId = $id;
        $r = $this->requisitionService->getById($id);
        $this->department_id = $r->department_id;
        $this->department_name = $r->department()->get('name')[0]->name;
        $this->cost = (string)$r->cost;
        $this->status = $r->status;
        $this->description = (string)($r->description ?? '');
        $this->date_requested = Carbon::parse($r->date_requested)->format('d/m/Y H:i');
        $this->date_approved = Carbon::parse($r->date_approved)->format('d/m/Y H:i');
        $this->requested_by_id = $r->requested_by_id;
        $this->requested_by_name = $r->requestedBy()->get('name')[0]->name;
        $this->currentStep = $this->steps[$r->status];
        $this->funded_to_id = $r->funded_to ?? null;
        $reqItems = $r->items;
        if($this->status == 'reviewed'){
            $this->canApprove = $this->requisitionService->canApprove($id);
            $this->reviewed_by_name = $r->reviewedBy()->get('name')[0]->name;
            $this->reviewed_on = Carbon::parse($r->reviewed_on)->format('d/m/Y H:i');
        }

        if($this->status == 'approved'){
            $this->canFund = $this->requisitionService->canFund($id);
            $this->reviewed_by_name = $r->reviewedBy()->get('name')[0]->name;
            $this->reviewed_on = Carbon::parse($r->reviewed_on)->format('d/m/Y H:i');
            $this->approved_by_name = $r->approvedBy()->get('name')[0]->name;
        }
        if($this->status == 'funded'){
            $this->reviewed_by_name = $r->reviewedBy()->get('name')[0]->name;
            $this->reviewed_on = Carbon::parse($r->reviewed_on)->format('d/m/Y H:i');
            $this->approved_by_name = $r->approvedBy()->get('name')[0]->name;
            $this->funded_on = $r->funded_on;
            $this->fund_amount = number_format($r->fund_amount,2);
            $this->funded_by_name = $r->fundedBy()->get('name')[0]->name;
            $this->funded_to_name = $r->fundedTo()->get('name')[0]->name;
        }

        if($this->status == 'rejected'){
            $this->rejectionReason = $r->rejection_reason;
            $this->rejected_by_name = $r->rejectedBy()->get('name')[0]->name;
            if($r->reviewed_by != null){
                $this->reviewed_by_name = $r->reviewedBy()->get('name')[0]->name;
                $this->reviewed_on = Carbon::parse($r->reviewed_on)->format('d/m/Y H:i');
            }
            if($r->approved_by_id != null){
                $this->approved_by_name = $r->approvedBy()->get('name')[0]->name;
            }
        }

        foreach($reqItems as $reqItem){
            $item = $this->itemService->getById($reqItem->item_id);
            $units = $item->units->toArray();
            $unitName = '';
            foreach($units as $index2 => $unit){
                if($unit['id'] == $reqItem->unit_id){
                    $units[$index2]['buying_price'] = $reqItem->unit_price;
                    $unitName = $unit['name'];
                    break;
                }
            }
            $this->items[] = [
            'item_id' => $reqItem->item_id,
            'name' => $item->name,
            'units' => $units,
            'quantity' => $reqItem->quantity,
            'current_stock' => $reqItem->current_stock,
            'selected_unit_id' => $reqItem->unit_id,
            'selected_unit_name' => $unitName,
            'unit_price' => $reqItem->unit_price,
            'total' => $reqItem->total,
            ];
        }
        $this->showViewPage = true;
        $this->showListTable = false;
    }

    public function edit(int $id): void
    {
        $r = $this->requisitionService->getById($id);
        if($r->status != 'funded' && $r->status != 'rejected')
        {
            $this->reqId = $r->id;
            $this->department_id = $r->department_id;
            $this->cost = (string)$r->cost;
            $this->status = $r->status;
            $this->description = (string)($r->description ?? '');
            $this->date_requested = $r->date_requested;
            $this->date_approved = $r->date_approved;
            $this->requested_by_id = $r->requested_by_id;

            $reqItems = $r->items;
            foreach($reqItems as $reqItem){
                $item = $this->itemService->getById($reqItem->item_id);
                $units = $item->units->toArray();
                foreach($units as $index2 => $unit){
                    if($unit['id'] == $reqItem->unit_id){
                        $units[$index2]['buying_price'] = $reqItem->unit_price;
                        break;
                    }
                }
                $this->items[] = [
                'item_id' => $reqItem->item_id,
                'name' => $item->name,
                'units' => $units,
                'quantity' => $reqItem->quantity,
                'current_stock' => $reqItem->current_stock,
                'selected_unit_id' => $reqItem->unit_id,
                'unit_price' => $reqItem->unit_price,
                'total' => $reqItem->total,
                ];
            }
            $this->showCreateEditPage = true;
            $this->showListTable = false;
        }else {
            if($r->status == 'funded'){
                session()->flash("error","You can not edit a funded requisition!");
                $this->dispatch('flash');
            }else{
                session()->flash("error","You can not edit a rejected requisition!");
                $this->dispatch('flash');
            }
        }

    }

    public function save(): void
    {
        $data = $this->validate($this->requisitionService->rules($this->reqId));
        try {
            $this->requisitionService->save($this->reqId, $data);
            $this->showCreateEditPage = false;
            $this->updatedShowCreateEditPage();
            $this->resetForm();
            session()->flash('success', 'Requisition saved.');
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            session()->flash('error', 'Fatal Error occurred while saving');
            $this->dispatch('flash');
        }


    }

    public function confirmDelete(int $id): void
    {
        $this->reqId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->reqId) {
            $this->requisitionService->delete($this->reqId);
        }
        $this->showDeleteModal = false;
        $this->resetForm();
        $this->refreshTable();
        session()->flash('success', 'Requisition deleted.');
        $this->dispatch('flash');
    }

    public function markAsReviewed(){
        try {
            $this->requisitionService->review($this->reqId,Auth::id());
            $this->showViewPage = false;
            $this->showListTable = true;
            $this->resetForm();
            session()->flash('success','Requisition Marked as Reviewed!');
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            session()->flash('error',$th->getMessage());
            $this->dispatch('flash');
        }
    }

    public function approve(){
        try {
            $this->requisitionService->approve($this->reqId,Auth::id());
            $this->showViewPage = false;
            $this->showListTable = true;
            $this->resetForm();
            session()->flash('success','Requisition Marked as Approved!');
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            session()->flash('error',$th->getMessage());
            $this->dispatch('flash');
        }
    }

    public function enterRejectionReason(){
        $this->showRejectionReasonModal = true;
    }

    public function reject(){
        if(trim($this->rejectionReason) == ""){
            session()->flash('error','Rejection Reason Required!');
            $this->dispatch('flash');
        }else{
            try {
                $this->requisitionService->reject($this->reqId,Auth::id(),$this->rejectionReason);
                $this->showRejectionReasonModal = false;
                $this->showViewPage = false;
                $this->showListTable = true;
                $this->resetForm();
                session()->flash('success','Requisition Marked as Rejected!');
                $this->dispatch('flash');
            } catch (\Throwable $th) {
                session()->flash('error',$th->getMessage());
                $this->dispatch('flash');
            }

        }
    }

    public function showFundAmountModal(){
        $this->showFundAmountEntryModal = true;
    }

    public function fund(){
        $this->fundErrorMessage = '';
        $this->showFundAmountError = false;
        if($this->fund_amount <= 0){
            $this->fundErrorMessage = "Amount should be greater than zero!";
            $this->showFundAmountError = true;
        }else if($this->fund_amount < $this->cost){
            $this->fundErrorMessage = "Amount should be equal to or greater than requisition cost!";
            $this->showFundAmountError = true;
        }else if($this->fund_amount >= $this->cost){
            try {
                $this->showFundAmountError = true;
                $this->showFundAmountEntryModal = false;
                $this->requisitionService -> fund($this->reqId,Auth::id(),$this->fund_amount,$this->funded_to_id);
                $this->showViewPage = false;
                $this->showListTable = true;
                $this->resetForm();
                session()->flash('success','Requisition Marked as Funded!');
                $this->dispatch('flash');
            } catch (\Throwable $th) {
                session()->flash('error',$th->getMessage());
                $this->dispatch('flash');
            }
        }else{
            $this->fundErrorMessage = "Please Enter a valid amount!";
            $this->showFundAmountError = true;
        }
    }

    protected function messages(): array
    {
        return [
            'items.required' => "At least one item is required!",
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.numeric'  => 'Quantity must be a number.',
            'items.*.quantity.min'      => 'Quantity must be at least 0.1.',

            'items.*.item_id.required' => 'Item is required.',
            'items.*.selected_unit_id.required' => 'Unit is required.',
        ];
    }

    public function render()
    {
        return view('livewire.requisition-manager');
    }
}


