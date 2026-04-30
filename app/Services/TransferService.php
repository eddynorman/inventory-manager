<?php

namespace App\Services;

use App\Enums\StockBatchType;
use App\Models\Issue;
use App\Models\ItemLocation;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\TransferToReceiveBatches;
use App\Models\Unit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class TransferService
{
    protected IssueService $issueService;
    protected StockBatchService $batchService;
    protected StockMovementService $movementService;
    /**
     * Create a new class instance.
     */
    public function __construct(IssueService $issueService,StockBatchService $batchService, StockMovementService $movementService)
    {
        $this->issueService = $issueService;
        $this->batchService = $batchService;
        $this->movementService = $movementService;
    }

    public function rules(){
        return [
            'selectedIssue' => ['required','integer','exists:issues,id'],
            'locationId' => ['required','integer','exists:locations,id'],
            'destinationId' => ['required','integer','exists:locations,id'],
            'description' => ['nullable','string'],
            'transferItems' => ['required','array','min:1'],
            'transferItems.*' => ['required','array','min:3'],
            'transferItems.*.item_id' => ['required','integer','exists:items,id'],
            'transferItems.*.selected_unit_id' => ['required','integer','exists:units,id'],
            'transferItems.*.quantity' => ['required','numeric'],
        ];
    }

    public function getById(int $id):Transfer{
        return Transfer::with(['items.item','receivedBy','user','fromLocation','toLocation'])->findOrFail($id);
    }

    public function getItemStock(int $itemId, int $locationId){
        return ItemLocation::where('item_id',$itemId)->where('location_id',$locationId)->get()->first()->quantity;
    }

    public function getIssue(int $id):Issue{
        return $this->issueService->getById($id);
    }

    public function loadIssues(int $destination){
        return Issue::with('fromLocation')->where('to_location_id',$destination)->where('status','pending')->get();
    }

    public function save(array $data, ?int $transferId = null){
        $issue = Issue::find($data['selectedIssue']);
        if($issue->user_id == Auth::id()){
            throw new Exception("You are not allowed to process this issue!");
        }
        return DB::transaction(function() use ($data,$transferId){
            $transfer = Transfer::updateOrCreate(
                ['id'=>$transferId ?? null],
                [
                    'description' => $data['description'],
                    'from_location_id' => $data['locationId'],
                    'to_location_id' => $data['destinationId'],
                    'issue_id' => $data['selectedIssue'],
                    'status' => 'pending',
                    'user_id' => Auth::id(),
                ]
            );

            foreach($data['transferItems'] as $item){
                $tItem = TransferItem::updateOrCreate(['id' => $item['id'] ?? null],
                    [
                        'transfer_id' => $transfer->id,
                        'item_id' => $item['item_id'],
                        'quantity' => $item['quantity'],
                        'unit_id' => $item['selected_unit_id'],
                    ]
                );
                $unit = Unit::find($item['selected_unit_id']);
                $qty = $item['quantity'] * $unit->smallest_units_number;
                $batchData  = $this->batchService->consumeBatches($item['item_id'],[$data['locationId']],$qty,StockBatchType::TRANSFER,$tItem->id);
                $bItems = $batchData['qtys'];
                foreach ($bItems as $bItem) {
                    TransferToReceiveBatches::create([
                        'transfer_id' => $transfer->id,
                        'to_location' => $data['destinationId'],
                        'item_id' => $bItem['item_id'],
                        'batch_id' => $bItem['batch_id'],
                        'is_received' => false,
                        'quantity' => $bItem['quantity'],
                        'unit_cost' => $bItem['unit_cost'],
                    ]);
                }
                $this->movementService->createMovement($item['item_id'],$data['locationId'],$data['destinationId'],-$qty,'transfer',StockBatchType::TRANSFER,$tItem->id,Auth::id());

            }
            $issue = Issue::find($data['selectedIssue']);
            $issue->processed_at = Carbon::now();
            $issue->processed_by = Auth::id();
            $issue->save();
            return $transfer;
        });
    }

    public function rejectIssue($issueId,$reason){
        $issue = Issue::find($issueId);
        if($issue->user_id == Auth::id()){
            throw new Exception("You are not allowed to reject this issue!");
        }
        $issue = Issue::find($issueId);
        $issue->rejected_at = Carbon::now();
        $issue->rejected_by = Auth::id();
        $issue->rejection_reason = $reason;
        $issue->status = 'rejected';
        $issue->save();
    }

    public function receiveTransfer(int $transferId){
        $toReceive = TransferToReceiveBatches::where('transfer_id',$transferId)->where('is_received',false)->get();
        if(count($toReceive) > 0){
            $transfer = Transfer::find($toReceive->first()->transfer_id);
            if($transfer->user_id == Auth::id()){
                throw new Exception("You are not allowed to receive this transfer!");
            }
            $locItems  = ItemLocation::where('location_id',$toReceive->first()->to_location)->get();
            foreach ($toReceive as $batch) {
                $found = false;
                foreach($locItems as $lItem){
                    if($lItem->item_id == $batch->item_id){
                        $found = true;
                        break;
                    }
                }
                if($found == false){
                    ItemLocation::create([
                        'item_id' => $batch->item_id,
                        'location_id' => $batch->to_location,
                        'quantity' => 0,//quantity will be updated by stock batch service
                    ]);
                }
                $this->batchService->createBatch($batch->item_id,$batch->to_location,$batch->quantity,$batch->unit_cost,'transfer receiving',$batch->transfer_id);
                $this->movementService->createMovement($batch->item_id,$transfer->from_location_id,$batch->to_location,$batch->quantity,'transfer receiving',StockBatchType::TRANSFER_RECEIVE,$batch->transfer_id,Auth::id());
                $batch->is_received = true;
                $batch->save();
            }
            $transfer->status = 'received';
            $transfer->save();
        }else{
            throw new Exception("Transfer already received");
        }
    }
}
