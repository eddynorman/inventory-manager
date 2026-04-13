<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = ['sale_id', 'item_id', 'unit_id', 'quantity', 'cost_at_sale','number_of_items','unit_price', 'total'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function batchUsages(){
        return $this->hasMany(SaleItemBatch::class,'sale_item_id');
    }
}


