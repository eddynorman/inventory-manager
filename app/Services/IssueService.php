<?php

namespace App\Services;

use App\Models\Issue;
use App\Models\IssueItem;
use App\Models\Item;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class IssueService
{
    public $locationId;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function rules(){
        return [
            'locationId' => ['required','integer','exists:locations,id'],
            'destinationId' => ['required','integer','exists:locations,id'],
            'description' => ['nullable','string'],
            'issueItems' => ['required','array','min:1'],
            'issueItems.*' => ['required','array','min:4'],
            'issueItems.*.item_id' => ['required','integer','exists:items,id'],
            'issueItems.*.selected_unit_id' => ['required','integer','exists:units,id'],
            'issueItems.*.stock' => ['required','numeric'],
            'issueItems.*.quantity' => ['required','numeric'],
        ];
    }

    public function search(int $locationId,string $search):array
    {
        $this->locationId = $locationId;
        $items = Item::where('name', 'like', '%' . $search . '%')
            ->where('is_active',true)
            ->whereHas('locationItems', function ($q) {
                $q->where('location_id',$this->locationId);
            })
            ->with(['locationItems' => function ($q) {
                $q->where('location_id', $this->locationId);
            }])
            ->with('units')
            ->get()
            ->map(function ($item) {

                $stock = $item->locationItems->first()->quantity;

                return [
                    'item_id' => $item->id,
                    'name' => $item->name,
                    'stock' => $stock,
                    'quantity' => 1,
                    'units' => $item->units->toArray(),
                    'selected_unit_id' => $item->units->first()->id,
                ];
            })->toArray();
        return $items;
    }

    public function getById(int $id):Issue{
        return Issue::with(['items.item','processedBy','rejectedBy','user','fromLocation','toLocation'])->findOrFail($id);
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

    public function save(array $data, ?int $issueId = null){
       return DB::transaction(function() use ($data,$issueId){
            $issue = Issue::updateOrCreate(
                ['id'=>$issueId ?? null],
                [
                    'description' => $data['description'],
                    'from_location_id' => $data['locationId'],
                    'to_location_id' => $data['destinationId'],
                    'issue_date' => Carbon::today(),
                    'status' => 'pending',
                    'user_id' => Auth::id(),
                ]
            );

            foreach($data['issueItems'] as $item){
                IssueItem::updateOrCreate(['id' => $item['id'] ?? null],
                    [
                        'issue_id' => $issue->id,
                        'item_id' => $item['item_id'],
                        'quantity' => $item['quantity'],
                        'unit_id' => $item['selected_unit_id'],
                    ]
                );
            }
            return $issue;
        });
    }
}
