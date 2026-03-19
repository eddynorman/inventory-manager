<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustmentItem extends Model
{
    protected $fillable = ['stock_adjustment_id', 'item_id', 'unit_id', 'quantity'];

    public function stockAdjustment()
    {
        return $this->belongsTo(StockAdjustment::class,'stock_adjustment_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class,'item_id');
    }
}
