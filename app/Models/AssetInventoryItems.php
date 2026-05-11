<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetInventoryItems extends Model
{
    protected $fillable = [
        'name',
        'department_id',
        'initial_quantity',
        'initial_purchase_date',
        'initial_unit_cost',
        'purchased_quantity',
        'average_unit_cost',
        'damaged_quantity',
        'current_quantity',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(AssetInventoryPurchaseItems::class, 'item_id');
    }

    public function damagedItems()
    {
        return $this->hasMany(AssetInventoryDamagedItems::class, 'item_id');
    }
}
