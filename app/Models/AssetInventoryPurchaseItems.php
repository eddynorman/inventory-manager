<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetInventoryPurchaseItems extends Model
{
    protected $fillable = [
        'purchase_id',
        'item_id',
        'quantity',
        'unit_cost',
    ];

    public function purchase()
    {
        return $this->belongsTo(AssetInventoryPurchases::class);
    }

    public function item()
    {
        return $this->belongsTo(AssetInventoryItems::class, 'item_id');
    }
}
