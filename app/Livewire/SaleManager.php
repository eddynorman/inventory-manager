<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\PaymentMethod;
use App\Models\Unit;
use App\Models\User;
use App\Services\OrganisationService;
use App\Services\SaleService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SaleManager extends Component
{
    public array $categories = [];
    public array $searchItems = [];
    public array $sale = [];
    public array $saleView = [];
    public array $users = [];
    public array $servers = [];
    public array $locationIds = [];
    public array $locations = [];
    public array $selectedLocations = [];
    public $selected_id = "";
    public $selected_user_id = "";
    public array $paymentMethods = [];
    public array $payments = [];
    public string $search = '';
    public ?int $selectedMethodId = null;
    public ?int $saleId = null;
    public array $paymentMethod = [];
    public float $paymentAmount = 0;

    public $highlightedIndex = 0;

    public string $methodName = '';
    public string $referenceNumber = '';

    public bool $showAddPaymentMethod = false;
    public bool $showGrid = false;
    public bool $showCategories = true;
    public bool $showCategoryItems = false;
    public bool $showPendingSales = false;
    public bool $showIndex = true;
    public bool $showConfirmSale = false;
    public bool $showSalesTables = false;
    public bool $showPending = false;
    public bool $showTodays = false;
    public bool $showViewSale = false;
    public bool $showAddPaymentForm = false;

    private SaleService $service;
    private OrganisationService $organisationService;

    protected $listeners = [
        'editSale'=> 'editSale',
        'viewSale' => 'viewSale',
        'addPayment' => 'showPaymentForm',
        'printSale' => 'printSale'
    ];

    public function mount(){
        $this->sale['served_by'] = [];
        $this->sale['items'] = [];
        $this->sale['paid'] = 0;
        $this->sale['balance'] = 0;
        $this->users = User::all()->toArray();
        $this->selectedMethodId = $this->paymentMethods[0]['id'] ?? null;
        $this->locations = Location::all()->toArray();
        $this->organisationService = new OrganisationService();
        $this->loadDefaultLocations();
    }

    public function boot(SaleService $service, OrganisationService $organisationService){
        $this->paymentMethods = PaymentMethod::all()->toArray();
        $this->service = $service;
        $this->organisationService = $organisationService;
    }

    public function updatedShowSalesTables(){
        if($this->showSalesTables == false){
            $this->showIndex = true;
        }
    }

    public function updatedShowPending(){
        if($this->showPending == true){
            $this->showTodays = false;
        }
    }
    public function showPendingFn(){
        $this->showIndex = false;
        $this->showSalesTables = true;
        $this->showPending = true;
        $this->showTodays = false;
    }
    public function updatedShowTodays(){
        if($this->showTodays == true){
            $this->showPending = false;
        }
    }

    public function showTodaysFn(){
        $this->showIndex = false;
        $this->showSalesTables = true;
        $this->showTodays = true;
        $this->showPending = false;
    }


    public function updatedSearch(){
        if(trim($this->search) != '' && count($this->locationIds) > 0){
            $this->searchItems = $this->service->search($this->search,$this->locationIds)->toArray();
        }else{
            if(count($this->locationIds) == 0){
                session()->flash('error','Select a Location first!');
                $this->dispatch('flash');
            }
            $this->searchItems = [];
        }
    }

    public function selectUser($id){
        if($id != ""){
            if (!in_array($id, $this->sale['served_by'])) {
                $this->sale['served_by'][] = $id;
            }
            $this->servers = collect($this->users)->whereIn('id',$this->sale['served_by'])->values()->toArray();
        }
    }

    public function removeUser($id){
        $this->sale['served_by'] = array_values(
            array_filter($this->sale['served_by'], fn($i) => $i != $id)
        );
        $this->servers = collect($this->users)->whereIn('id',$this->sale['served_by'])->values()->toArray();
    }

    public function getSelectedLocationsProperty()
    {
        return collect($this->locations)
            ->whereIn('id', $this->locationIds)
            ->values();
    }

    public function getAvailableLocationsProperty()
    {
        return collect($this->locations)
            ->whereNotIn('id', $this->locationIds)
            ->values();
    }

    public function selectLocation($id){
        if($id != ""){
            if (!in_array($id, $this->locationIds)) {
                $this->locationIds[] = $id;
            }
            $this->selectedLocations = collect($this->locations)->whereIn('id',$this->locationIds)->values()->toArray();
            $this->selected_id = "";
        }
    }

    public function removeLocation($id)
    {
        $this->locationIds = array_values(
            array_filter($this->locationIds, fn($i) => $i != $id)
        );
        $this->selectedLocations = collect($this->locations)->whereIn('id',$this->locationIds)->values()->toArray();
    }

    public function loadDefaultLocations(){
        $defaultLocations = $this->organisationService->getDefaultSaleLocations();
        if(count($defaultLocations) > 0){
            foreach($defaultLocations as $loc){
                $this->locationIds[] = $loc->location_id;
            }
            $this->selectedLocations = collect($this->locations)->whereIn('id',$this->locationIds)->values()->toArray();
        }
    }

    public function moveDown()
    {
        if ($this->highlightedIndex < count($this->searchItems) - 1) {
            $this->highlightedIndex++;
        }
    }

    public function moveUp()
    {
        if ($this->highlightedIndex > 0) {
            $this->highlightedIndex--;
        }
    }

    public function selectHighlighted()
    {
        if (isset($this->searchItems[$this->highlightedIndex])) {
            $this->selectItem($this->highlightedIndex);
            $this->highlightedIndex = 0;
        }
    }

    public function selectItem(int $index)
    {
        $item = $this->searchItems[$index];

        $foundIndex = null;

        if ($item['type'] == 'item') {

            foreach ($this->sale['items'] as $i => $sale_item) {
                if ($sale_item['item_id'] == $item['item_id']) {

                    $unit_price = 0;

                    foreach ($sale_item['units'] as $unit) {
                        if ($unit['id'] == $sale_item['selected_unit_id']) {
                            $unit_price = $unit['selling_price'];
                            break;
                        }
                    }

                    $this->sale['items'][$i]['quantity'] += 1;
                    $this->sale['items'][$i]['total'] =
                        $this->sale['items'][$i]['quantity'] * $unit_price;

                    $foundIndex = $i;
                    break;
                }
            }

            if ($foundIndex === null) {
                $item['quantity'] = 1;
                $item['units'] = $this->service->getUnits($item['item_id']);
                $item['selected_unit_id'] = $item['units'][0]['id'];
                $item['selling_price'] = $item['units'][0]['selling_price'];
                $item['total'] = $item['selling_price'];

                $this->sale['items'][] = $item;

                $foundIndex = count($this->sale['items']) - 1;
            }

        } else {

            foreach ($this->sale['items'] as $i => $sale_item) {
                if ($sale_item['kit_id'] == $item['kit_id']) {

                    $this->sale['items'][$i]['quantity'] += 1;
                    $this->sale['items'][$i]['total'] =
                        $this->sale['items'][$i]['quantity'] * $sale_item['selling_price'];

                    $foundIndex = $i;
                    break;
                }
            }

            if ($foundIndex === null) {
                $item['quantity'] = 1;
                $item['units'] = [
                    ['id' => 0, 'name' => 'kit','selling_price' => $item['selling_price'],'smallest_units_number' => 1]
                ];
                $item['selected_unit_id'] = 0;
                $item['total'] = $item['selling_price'];

                $this->sale['items'][] = $item;

                $foundIndex = count($this->sale['items']) - 1;
            }
        }

        // Reset search
        $this->search = '';
        $this->searchItems = [];
        $this->highlightedIndex = 0;

        $this->calculateTotal();

        // 🔥 focus this row
        $this->dispatch('focus-qty', index: $foundIndex);

        // 🔥 return cursor to search
        //$this->dispatch('focus-search');
    }

    public function removeItem(int $index){
        array_splice($this->sale['items'],$index,1);
        $this->calculateTotal();
    }

    public function increaseQty($index)
    {
        //dd($this->sale);
        $this->sale['items'][$index]['quantity']++;

        $this->updateItemTotal($index);

        $this->dispatch('focus-qty', index: $index);
    }

    public function decreaseQty($index)
    {
        if ($this->sale['items'][$index]['quantity'] > 1) {
            $this->sale['items'][$index]['quantity']--;

            $this->updateItemTotal($index);

            $this->dispatch('focus-qty', index: $index);
        }
    }

    private function updateItemTotal($index)
    {
        $item = $this->sale['items'][$index];

        $unit_price = 0;

        foreach ($item['units'] as $unit) {
            if ($unit['id'] == $item['selected_unit_id']) {
                $unit_price = $unit['selling_price'];
                $this->sale['items'][$index]['selling_price'] = $unit_price;
                break;
            }
        }


        $this->sale['items'][$index]['total'] =
            $this->sale['items'][$index]['quantity'] * $unit_price;

        $this->calculateTotal();
    }

    public function updatedSale($value,$key){
        //dd('running');
        $name = explode('.', $key)[0];
        if($name == 'items'){
            $index = explode('.', $key)[1];
            if($value != '' && $value != null){
                $this->updateItemTotal($index);
            }else{
                $field = explode('.', $key)[2];
                if($field == 'quantity'){
                    $this->sale['items'][$index]['total'] = 0;
                }
            }

        }

    }

    public function calculateTotal(){
        $this->sale['total'] = 0;
        foreach ($this->sale['items'] as $item) {
            $this->sale['total'] += $item['total'];
        }
        $this->sale['balance'] = $this->sale['total'] - $this->sale['paid'];
        $this->paymentAmount = $this->sale['balance'];
    }

    public function addPayment(){

        if(count($this->sale['items']) < 1){
            session()->flash('error','Add Sale Items First!');
            $this->dispatch('flash');
            return;
        }

        if($this->selectedMethodId != null && $this->paymentAmount > 0){
            if($this->sale['balance'] >= $this->paymentAmount){
                $method = [];
                foreach($this->paymentMethods as $pm){
                    if($pm['id'] == $this->selectedMethodId){
                        $method = $pm;
                    }
                }
                $this->sale['paid'] += $this->paymentAmount;
                $this->payments[] = ['amount'=> $this->paymentAmount,'method'=>$method];
                $this->paymentAmount = 0;
                $this->selectedMethodId = $this->paymentMethods[0]['id'] ?? null;
                $this->calculateTotal();
            }else{
                session()->flash('error','Payment is greater than Balance!');
                $this->dispatch('flash');
            }
        }else{
            session()->flash('error','Invalid Payment Amount!');
            $this->dispatch('flash');
        }
    }

    /**
     * Payment Method Management
     */

    public function savePaymentMethod(){
        if(trim($this->methodName) != '' && trim($this->referenceNumber) != ''){
            $this->service->savePaymentMethod($this->methodName,$this->referenceNumber,null);
            $this->showAddPaymentMethod = false;
            $this->methodName = '';
            $this->referenceNumber = '';
        }
    }

    public function clearSaleForm(){
        $this->sale['served_by'] = [];
        $this->sale['items'] = [];
        $this->sale['paid'] = 0;
        $this->sale['balance'] = 0;
        $this->servers = [];
        $this->selected_user_id = "";
        $this->calculateTotal();
        $this->reset('payments','paymentAmount');
    }

    public function saveSale(){
        try {
            $data = $this->validate($this->service->rules());
            $saleid = $this->service->save($data,$this->sale['id']??null,"upfront");
            session()->flash('Success',"Sale saved successfully!");
            $this->dispatch('flash');
            $this->clearSaleForm();
            $this->dispatch('focus-search');
            $this->printSale($saleid);
            $this->dispatch('refreshSales');
        } catch (\Throwable $th) {
            //dd($th);
            session()->flash('error',$th->getMessage());
            $this->dispatch('flash');
        }
        $this->showConfirmSale = false;
    }

    public function loadSale(int $saleId):array{
        $sale = $this->service->getSale($saleId);
        $saleData = [];

        //load locations
        $saleData['locations'] = $sale->locations->toArray();
        $loc_ids = [];
        foreach($saleData['locations'] as $sl){
            $loc_ids[] =$sl['id'];
        }

        //load payments
        $saleData['payments'] = [];
        foreach($sale->payments as $payment){
            $saleData['payments'][] = ['id' => $payment->id,'amount' => $payment->amount, 'method' => $payment->method->toArray()];
        }

        //load servers
        $saleData['served_by'] = $sale->servedBy->toArray();
        $saleData['recorded_by'] = $sale->createdBy->name;

        //load payment summary and info
        $saleData['total'] = $sale->total_amount;
        $saleData['paid'] = $sale->total_paid;
        $saleData['balance'] = $sale->balance;
        $saleData['created_at'] = $sale->created_at->toDateTimeString();
        $saleData['id'] = $sale->id;
        $saleData['status'] = $sale->status;

        //load items
        $saleData['items'] = [];
        foreach($sale->items as $saleItem){
            $stock = $this->service->getItemStock($saleItem->item->id,$loc_ids);
            $unit = Unit::find($saleItem->unit_id)->toArray();
            $i = [
                'type' => "item",
                'item_id' => $saleItem->item->id,
                'name' => $saleItem->item->name,
                'unit_name' =>$unit['name'],
                'stock' => $stock,
                'selling_price' => $saleItem->unit_price,
                'quantity' => $saleItem->quantity,
                'units' => $saleItem->item->units()->get()->toArray(),
                'selected_unit_id' => $saleItem->unit_id,
                'total' => $saleItem->total,
            ];
            $saleData['items'][] = $i;
        }
        foreach($sale->kits as $saleKit){
            $stock = $this->service->getKitAvailableQty($saleKit->kit->id,$loc_ids);
            $i = [
                'type' => "kit",
                'kit_id' => $saleKit->kit->id,
                'name' => $saleKit->kit->name,
                'unit_name' => "Kit",
                'selling_price' => $saleKit->selling_price,
                'quantity' => $saleKit->quantity,
                'units' => [
                    ['id' => 0, 'name' => 'kit','selling_price' => $saleKit->selling_price,'smallest_units_number' => 1]
                ],
                'selected_unit_id' => 0,
                'total' => $saleKit->total,
            ];
            $saleData['items'][] = $i;
        }
        return $saleData;
    }

    public function editSale(int $saleId){
        $data = $this->loadSale($saleId);
        $this->sale['id'] = $data['id'];

        $saleLocations = $data['locations'];
        if(count($saleLocations) > 0){
            $this->locationIds = [];
            foreach($saleLocations as $loc){
                $this->locationIds[] = $loc['id'];
            }
            $this->selectedLocations = collect($this->locations)->whereIn('id',$this->locationIds)->values()->toArray();
        }

        $this->sale['served_by'] = [];
        foreach($data['served_by'] as $sb){
            if (!in_array($sb['id'], $this->sale['served_by'])) {
                $this->sale['served_by'][] = $sb['id'];
            }
            $this->servers = collect($this->users)->whereIn('id',$this->sale['served_by'])->values()->toArray();
        }

        $this->sale['items'] = $data['items'];
        $this->sale['total'] = $data['total'];
        $this->sale['paid'] = $data['paid'];
        $this->sale['balance'] = $data['balance'];
        $this->payments = $data['payments'];
        $this->paymentAmount = $data['balance'];
        $this->showSalesTables = false;
        $this->showViewSale = false;
        $this->showIndex = true;
    }
    public function viewSale(int $saleId){
        $this->saleView = $this->loadSale($saleId);
        $this->showViewSale = true;
    }

    public function updatedShowViewSale(){
        if($this->showViewSale == false){
            $this->saleView = [];
        }
    }

    public function showPaymentForm(int $saleId){
        $sale = $this->service->getSale($saleId);
        $this->saleId = $saleId;
        $this->paymentAmount = $sale->balance;
        $this->selectedMethodId = $this->paymentMethods[0]['id'] ?? null;
        $this->showAddPaymentForm = true;
    }

    public function refreshTables(){
        $this->dispatch('pg:eventRefresh-todays-sales-table-xlvydk-table');
        $this->dispatch('pg:eventRefresh-pending-sales-table-xkotlo-table');
    }

    public function savePayment(){
        $sale = $this->service->getSale($this->saleId);
        if($this->saleId != null && $this->paymentAmount > 0 && $this->selectedMethodId != null){
            if($sale->balance >= $this->paymentAmount){
                $this->service->addPayment($this->saleId,$this->paymentAmount,$this->selectedMethodId,Auth::id());
                session()->flash('success','Payment Added Successfully!');
                $this->dispatch('flash');
                $this->refreshTables();
                $this->paymentAmount = 0;
                $this->selectedMethodId = $this->paymentMethods[0]['id'] ?? null;
                $this->saleId = null;
                $this->showAddPaymentForm = false;
            }else{
                session()->flash('error','Payment is greater than Balance!');
                $this->dispatch('flash');
            }
        }else{
            session()->flash('error','Invalid Payment Amount!');
            $this->dispatch('flash');
        }


    }

    public function printSale(int $id){
        $this->dispatch('print-sale', saleId: $id);
    }

    public function render()
    {
        return view('livewire.sale-manager');
    }
}
