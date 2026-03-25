<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItemKitItem extends Model
{
    protected $fillable = ['sale_id','sale_item_kit_id','item_id','unit_id','quantity','cost_at_sale'];

    public function saleKit(){
        return $this->belongsTo(SaleItemKit::class,'sale_item_kit_id');
    }

    public function item(){
        return $this->belongsTo(Item::class,'item_id');
    }

    public function unit(){
        return $this->belongsTo(Unit::class,'unit_id');
    }

    public function batchUsages(){
        return $this->hasMany(SaleItemBatch::class,'sale_item_kit_item_id');
    }
}
