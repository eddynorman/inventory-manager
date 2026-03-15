<?php

namespace App\Livewire;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Livewire\Component;

class PurchaseManager extends Component
{
    public ?int $created_by = null;
    public array $requisitions = [];
    public array $suppliers = [];
    public array $order = [];
    public array $purchase = [];
    public array $requisition = [];
    public ?int $selected_requisition_id = null;
    public ?int $selected_supplier_id = null;
    public ?int $idToDelete = null;
    public ?int $order_id = null;
    public float $amount = 0;
    public string $payment_reference = "";

    public bool $showPurchaseCreationPage = false;
    public bool $showOrderCreationPage = false;
    public bool $showIndexPage = true;
    public bool $showOrders = false;
    public bool $showPurchases = true;
    public bool $showConfirmDeleteOrder = false;
    public bool $showConfirmDeletePurchase = false;
    public bool $showOrderPaymentForm = false;

    // View modals
    public array $view_order = [];
    public array $view_purchase = [];
    public bool $showViewOrder = false;
    public bool $showViewPurchase = false;
    private PurchaseService $purchaseService;

    protected $listeners = [
        'editOrder'=> 'editOrder',
        'viewOrder' => 'viewOrder',
        'deleteOrder' => 'confirmDeleteOrder',
        'editPurchase'=> 'editPurchase',
        'viewPurchase' => 'viewPurchase',
        'deletePurchase' => 'confirmDeletePurchase',
        'addPayment' => 'showPaymentForm',
    ];

    public function boot(PurchaseService $purchaseService){
        $this->created_by = Auth::id();
        $this->purchaseService = $purchaseService;
    }

    public function updatedShowPurchaseCreationPage(){
        if($this->showPurchaseCreationPage == false){
            $this->showIndexPage = true;
            $this->resetForm();
        }
    }
    public function updatedShowOrderCreationPage(){
        if($this->showPurchaseCreationPage == false){
            $this->showIndexPage = true;
            $this->resetForm();
        }
    }

    public function updatedSelectedRequisitionId(){
        $this->reset('requisition');
        if($this->selected_requisition_id != null){
            $this->loadRequisition($this->selected_requisition_id);
        }
    }

    public function resetForm(): void
    {
        $this->reset(['requisitions','order','purchase','requisition','selected_requisition_id']);
        $this->reset(['selected_supplier_id','suppliers']);
        $this->resetValidation();
        $this->refreshOrderTable();
        $this->refreshPurchaseTable();
    }

    public function togglePurchaseOrderView(){
        if($this->showPurchases == true){
            $this->showPurchases = false;
            $this->showOrders = true;
        }else{
            $this->showOrders = false;
            $this->showPurchases = true;

        }
    }

    public function getAvailable(array $requisition_items) :array {
        $available = [];
        foreach($requisition_items as $item){
            if(!$item['used']){
                $available[] = $item;
            }
        }
        return $available;

    }

    public function loadRequisition(int $requisition_id){
        $this->requisition = $this->purchaseService->loadRequisitionData($requisition_id);
        $this->requisition['available'] = $this->getAvailable($this->requisition['items']);
    }

    public function createPurchase(): void
    {
        $this->resetForm();
        $this->requisitions = $this->purchaseService->loadUnpurchasedRequisitions();
        $this->purchase['grand_total'] = 0;
        $this->showPurchaseCreationPage = true;
        $this->showIndexPage = false;
    }

    public function createOrder(){
        $this->suppliers = $this->purchaseService->loadSuppliers();
        if(count($this->suppliers) == 0){
            session()->flash('error','Add supplier first!');
            $this->dispatch('flash');
            return;
        }else{
            $this->resetForm();
            $this->suppliers = $this->purchaseService->loadSuppliers();
            $this->requisitions = $this->purchaseService->loadUnpurchasedRequisitions();
            $this->order['grand_total'] = 0;
            $this->showOrderCreationPage = true;
            $this->showIndexPage = false;
        }

    }

    public function addPurchaseItem(int $index){
        $item = $this->requisition['available'][$index];
        $this->purchase['items'][] = $item;
        array_splice($this->requisition['available'],$index,1);
        $sum = 0;
        foreach( $this->purchase['items'] as $item){
            $sum += $item['total'];
        }
        $this->purchase['grand_total'] = $sum;
    }

    public function removeItem(int $index,string $type = 'purchase'){
        if($type == 'purchase'){
            $item = $this->purchase['items'][$index];
            $this->requisition['available'][] = $item;
            array_splice($this->purchase['items'],$index,1);
            $sum = 0;
            foreach( $this->purchase['items'] as $item){
                $sum += $item['total'];
            }
            $this->purchase['grand_total'] = $sum;
        }else if($type == 'order'){
            $item = $this->order['items'][$index];
            $this->requisition['available'][] = $item;
            array_splice($this->order['items'],$index,1);
            $sum = 0;
            foreach( $this->order['items'] as $item){
                $sum += $item['total'];
            }
            $this->order['grand_total'] = $sum;
        }
    }

    public function updatedPurchase($value,$key){
        //dd($key,$value);
        $index =  explode('.', $key)[1];
        $field =  explode('.', $key)[2];

        if($field == "unit_price" || $field == 'quantity'){
            if($value == '' || $value == null){
                $this->purchase['items'][$index]['total'] = 0;
                $sum = 0;
                foreach( $this->purchase['items'] as $item){
                    $sum += $item['total'];
                }
                $this->purchase['grand_total'] = $sum;
                return;
            }
            $this->purchase['items'][$index]['total'] = $this->purchase['items'][$index]['unit_price'] * $this->purchase['items'][$index]['quantity'];
            $sum = 0;
            foreach( $this->purchase['items'] as $item){
                $sum += $item['total'];
            }
            $this->purchase['grand_total'] = $sum;

        }

    }
    public function updatedOrder($value,$key){
        //dd($key,$value);
        $index =  explode('.', $key)[1];
        $field =  explode('.', $key)[2];

        if($field == "unit_price" || $field == 'quantity'){
            if($value == '' || $value == null){
                $this->order['items'][$index]['total'] = 0;
                $sum = 0;
                foreach( $this->order['items'] as $item){
                    $sum += $item['total'];
                }
                $this->order['grand_total'] = $sum;
                return;
            }
            $this->order['items'][$index]['total'] = $this->order['items'][$index]['unit_price'] * $this->order['items'][$index]['quantity'];
            $sum = 0;
            foreach( $this->order['items'] as $item){
                $sum += $item['total'];
            }
            $this->order['grand_total'] = $sum;

        }

    }
    public function updatedSelectedSupplierId(){
        if($this->requisition === [] && $this->selected_supplier_id != null){
            session()->flash("error", 'Select Requisition First!');
            $this->dispatch('flash');
            $this->selected_supplier_id = null;
        }else{
            if($this->selected_supplier_id != null){
                $offset = 0;
                foreach($this->requisition['available'] as $index => $available){
                    //automatically add items belonging to that supplier
                    if($available['supplier_id'] == $this->selected_supplier_id){
                        $this->addOrderItem($index - $offset);
                        $offset++;
                    }
                }
            }
        }
    }
    public function addOrderItem(int $index){

        if($this->selected_supplier_id == null){
            dd("errored");
            session()->flash("error","Supplier Must Be Selected!");
            $this->dispatch("flash");
            return;
        }
        $item = $this->requisition['available'][$index];
        $this->order['items'][] = $item;
        array_splice($this->requisition['available'],$index,1);
        $sum = 0;
        foreach( $this->order['items'] as $item){
            $sum += $item['total'];
        }
        $this->order['grand_total'] = $sum;
    }

    public function editPurchase(int $id): void
    {
        $purchase = $this->purchaseService->getById($id,"purchase");
        $purchase['grand_total'] = $purchase['total_amount'];
        unset($purchase['total_amount']);
        foreach ($purchase['items'] as $key => $item) {
            $purchase['items'][$key]['total'] = $purchase['items'][$key]['actual_total'];
            $purchase['items'][$key]['unit_price'] = $purchase['items'][$key]['actual_unit_price'];
            unset($purchase['items'][$key]['actual_total']);
            unset($purchase['items'][$key]['actual_unit_price']);
        }
        $this->loadRequisition($purchase['requisition_id']);
        $this->purchase = $purchase;
        $this->selected_requisition_id = $purchase['requisition_id'];
        $this->showPurchaseCreationPage = true;
        $this->showIndexPage = false;
    }

    public function editOrder(int $id): void
    {
        $order = $this->purchaseService->getById($id,"order");
        $order['grand_total'] = $order['total_amount'];
        unset($order['total_amount']);
        foreach ($order['items'] as $key => $item) {
            $order['items'][$key]['unit_price'] = $item['actual_unit_price'];
            unset($order['items'][$key]['actual_unit_price']);
        }
        $this->loadRequisition($order['requisition_id']);
        $this->order = $order;
        $this->selected_requisition_id = $order['requisition_id'];
        $this->selected_supplier_id = $order['supplier_id'];
        $this->showOrderCreationPage = true;
        $this->showIndexPage = false;
    }

    public function viewOrder(int $id): void
    {
        $order = $this->purchaseService->getById($id, "order");
        $order['grand_total'] = $order['total_amount'] ?? ($order['grand_total'] ?? 0);
        if(isset($order['total_amount'])){
            unset($order['total_amount']);
        }
        foreach ($order['items'] as $key => $item) {
            $order['items'][$key]['unit_price'] = $item['actual_unit_price'] ?? ($item['unit_price'] ?? 0);
            $order['items'][$key]['total'] = $item['total'] ?? ($item['actual_total'] ?? 0);
        }
        // add supplier name if available
        if(isset($order['supplier_id'])){
            $supplier = Supplier::find($order['supplier_id']);
            $order['supplier_name'] = $supplier ? $supplier->name : null;
        }

        $this->view_order = $order;
        $this->showViewOrder = true;
    }

    public function viewPurchase(int $id): void
    {
        $purchase = $this->purchaseService->getById($id, "purchase");
        $purchase['grand_total'] = $purchase['total_amount'] ?? ($purchase['grand_total'] ?? 0);
        if(isset($purchase['total_amount'])){
            unset($purchase['total_amount']);
        }
        foreach ($purchase['items'] as $key => $item) {
            $purchase['items'][$key]['unit_price'] = $item['actual_unit_price'] ?? ($item['unit_price'] ?? 0);
            $purchase['items'][$key]['total'] = $item['actual_total'] ?? ($item['total'] ?? 0);
        }

        $this->view_purchase = $purchase;
        $this->showViewPurchase = true;
    }

    public function savePurchase(): void
    {
        $data = $this->validate($this->purchaseService->purchaseRules());
        try {
            $this->purchaseService->saveCashPurchase($data,Auth::id());
            $this->resetForm();
            $this->showPurchaseCreationPage = false;
            $this->showIndexPage = true;
            session()->flash('success', 'Purchase saved.');
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            dd($th);
            session()->flash('error', 'A fatal Error Occured while saving');
            $this->dispatch('flash');
        }


    }

    public function saveOrder(): void
    {
        $data = $this->validate($this->purchaseService->orderRules());
        try {
            $this->purchaseService->saveSupplierOrder($data,Auth::id());
            $this->showOrderCreationPage = false;
            $this->showIndexPage = true;
            $this->resetForm();
            session()->flash('success', 'Purchase saved.');
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            dd($th);
            session()->flash('error', 'A fatal Error Occured while saving');
            $this->dispatch('flash');
        }


    }

    public function showPaymentForm(int $orderId){
        $this->order_id = $orderId;
        $this->showOrderPaymentForm = true;
    }

    public function addPayment(){
        try {
            $this->purchaseService->registerPayment($this->order_id,$this->amount, Auth::id(),$this->payment_reference);
            $this->showOrderPaymentForm = false;
            session()->flash('success','Payment Added Successfully');
            $this->dispatch('flash');
            $this->refreshOrderTable();
        } catch (\Throwable $th) {
            $this->showOrderPaymentForm = false;
            session()->flash('error','Fatal Error while adding payment.');
            $this->dispatch('flash');
        }
    }

    public function refreshOrderTable(): void
    {
        $this->dispatch('pg:eventRefresh-orders-table-t2dk1o-table');
    }
    public function refreshPurchaseTable(): void
    {
        $this->dispatch('pg:eventRefresh-purchases-table-bzk4f0-table');
    }

    public function confirmDeletePurchase(int $id): void
    {
        $this->showConfirmDeletePurchase = true;
        $this->idToDelete = $id;
    }

    public function confirmDeleteOrder(int $id): void
    {
        $this->showConfirmDeleteOrder = true;
        $this->idToDelete = $id;
    }

    public function deletePurchase(): void
    {
        try {
            $this->purchaseService->delete($this->idToDelete,"purchase");
            $this->showConfirmDeletePurchase = false;
            session()->flash('success','purchase Deleted Successfully');
            $this->dispatch('flash');
            $this->refreshPurchaseTable();
        } catch (\Throwable $th) {
            dd("put ");
            $this->showConfirmDeletePurchase = false;
            session()->flash('error','Fatal error ocurred while deleting');
            $this->dispatch('flash');
        }
    }

    public function deleteOrder(): void
    {
        try {
            $this->purchaseService->delete($this->idToDelete,"order");
            $this->showConfirmDeleteOrder = false;
            session()->flash('success','Order Deleted Successfully');
            $this->dispatch('flash');
            $this->refreshOrderTable();
        } catch (\Throwable $th) {
            dd($th);
            $this->showConfirmDeleteOrder = false;
            session()->flash('error','Fatal error ocurred while deleting');
            $this->dispatch('flash');
        }
    }

    public function render()
    {
        return view('livewire.purchase-manager');
    }
}


