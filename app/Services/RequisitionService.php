<?php

namespace App\Services;

use App\Models\Requisition;
use App\Models\RequisitionItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RequisitionService
{
    protected ItemService $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */

    public function rules(?int $requisitionId = null): array
    {
        return [
            'description'        => ['nullable', 'string'],
            'cost'               => ['required','numeric'],
            'department_id'      => ['required','exists:departments,id'],
            'requested_by_id'    => ['required', 'exists:users,id'],
            'status'             => ['nullable', Rule::in([
                'pending', 'reviewed', 'approved', 'funded', 'rejected'
            ])],

            'items'                     => ['required', 'array', 'min:1'],
            'items.*.id'                => ['nullable', 'exists:requisition_items,id'],
            'items.*.item_id'           => ['required', 'exists:items,id'],
            'items.*.quantity'          => ['required', 'numeric', 'min:0.1'],
            'items.*.current_stock'     => ['required', 'numeric'],
            'items.*.unit_price'          => ['required', 'numeric', 'min:1'],
            'items.*.selected_unit_id'  => ['required', 'exists:units,id'],
            'items.*.total'             => ['required', 'numeric'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Create / Update Requisition
    |--------------------------------------------------------------------------
    */

    public function save(?int $requisitionId, array $data): void
    {

        foreach ($data['items'] as $index => $item) {
            $data['items'][$index]['unit_id'] = $item['selected_unit_id'];
            unset($data['items'][$index]['selected_unit_id']);
        }
        DB::transaction(function () use ($requisitionId, $data) {

            $requisitionData = collect($data)
                ->except('items')
                ->toArray();


            if (!$requisitionId) {
                $requisitionData['status'] = 'pending';
                $requisitionData['date_requested'] = now();
            }

            $requisition = Requisition::updateOrCreate(
                ['id' => $requisitionId],
                $requisitionData
            );

            $keepIds = collect($data['items'])
                ->pluck('id')
                ->filter()
                ->values();

            RequisitionItem::where('requisition_id', $requisition->id)
                ->whereNotIn('id', $keepIds)
                ->delete();

            foreach ($data['items'] as $item) {

                RequisitionItem::updateOrCreate(
                    ['id' => $item['id'] ?? null],
                    [
                        'requisition_id' => $requisition->id,
                        'item_id'        => $item['item_id'],
                        'current_stock'  => $item['current_stock'],
                        'quantity'       => $item['quantity'],
                        'unit_id'        => $item['unit_id'],
                        'unit_price'     => $item['unit_price'],
                        'total'          => $item['total']
                    ]
                );
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow Actions
    |--------------------------------------------------------------------------
    */
    public function canReview(int $id):bool{
        $req = Requisition::find($id);
        if($req->status == 'pending'){
            if($req->requested_by_id != Auth::id()){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function review(int $requisitionId, int $userId): void
    {
        if(!$this->canReview($requisitionId)){
            throw new Exception('You are not allowed to review this requisition');
        }
        DB::transaction(function () use ($requisitionId, $userId) {

            $requisition = Requisition::findOrFail($requisitionId);

            if ($requisition->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Only pending requisitions can be reviewed.'
                ]);
            }

            $requisition->review($userId);
        });
    }

    public function canApprove(int $id):bool{
        $req = Requisition::find($id);
        if($req->status == 'reviewed'){
            if($req->requested_by_id != Auth::id() && $req->reviewed_by != Auth::id()){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function approve(int $requisitionId, int $userId): void
    {
        if(!$this->canApprove($requisitionId)){
            throw new Exception('You are not allowed to approve this requisition');
        }
        DB::transaction(function () use ($requisitionId, $userId) {

            $requisition = Requisition::findOrFail($requisitionId);

            if ($requisition->status !== 'reviewed') {
                throw ValidationException::withMessages([
                    'status' => 'Only reviewed requisitions can be approved.'
                ]);
            }

            $requisition->approve($userId);
        });
    }

    public function canFund(int $id):bool{
        $req = Requisition::find($id);
        if($req->status == 'approved'){
            if($req->requested_by_id != Auth::id() && $req->reviewed_by != Auth::id() && $req->approved_by_id != Auth::id()){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function fund(int $requisitionId, int $userId, float $fundAmount, int $fundedTo): void
    {
        if(!$this->canFund($requisitionId)){
            throw new Exception('You are not allowed to fund this requisition');
        }
        DB::transaction(function () use ($requisitionId, $userId, $fundAmount,$fundedTo) {

            $requisition = Requisition::with('items')->findOrFail($requisitionId);

            if ($requisition->status !== 'approved') {
                throw ValidationException::withMessages([
                    'status' => 'Only approved requisitions can be funded.'
                ]);
            }

            // // Deduct stock
            // $stockPayload = $requisition->items->map(function ($item) {
            //     return [
            //         'item_id' => $item->item_id,
            //         'quantity' => $item->quantity,
            //     ];
            // })->toArray();

            // $this->itemService->decreaseStock($stockPayload);

            $requisition->fund($userId, $fundAmount,$fundedTo);
        });
    }

    public function canReject(int $id):bool{
        $req = Requisition::find($id);
        if($req->status != 'funded'){
            if($req->requested_by_id != Auth::id() && $req->reviewed_by != Auth::id() && $req->approved_by_id != Auth::id()){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function reject(int $requisitionId, int $userId, string $reason): void
    {
        if(!$this->canReject($requisitionId)){
            throw new Exception('You are not allowed to reject this requisition');
        }
        DB::transaction(function () use ($requisitionId, $userId, $reason) {

            $requisition = Requisition::findOrFail($requisitionId);

            if ($requisition->status === 'funded') {
                throw ValidationException::withMessages([
                    'status' => 'Funded requisition cannot be rejected.'
                ]);
            }

            $requisition->reject($userId, $reason);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Basic Retrieval
    |--------------------------------------------------------------------------
    */

    public function getById(int $id): Requisition
    {
        return Requisition::with('items')->findOrFail($id);
    }

    public function getUnpurchased(){
        return Requisition::with('items')
            ->select(['id','department_id','cost'])
            ->funded()
            ->unpurchased()
            ->get();
    }

    public function markAsPurchased(int $id){
        $req = $this->getById($id);
        $req->update([
            'is_purchased' => true,
        ]);
    }

    public function delete(int $id): void
    {
        Requisition::where('id', $id)->delete();
    }

    public function bulkDelete(array $ids): void
    {
        Requisition::whereIn('id', $ids)->delete();
    }
}
