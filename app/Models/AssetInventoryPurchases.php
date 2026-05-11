<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetInventoryPurchases extends Model
{
    protected $fillable = ['total'];

    public function items()
    {
        return $this->hasMany(AssetInventoryPurchaseItems::class, 'purchase_id');
    }
}
