<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetInventoryDamagedItems extends Model
{
    protected $fillable = [
        'item_id',
        'quantity',
        'average_unit_cost',
        'notes'
    ];

    public function item()
    {
        return $this->belongsTo(AssetInventoryItems::class, 'item_id');
    }

}
