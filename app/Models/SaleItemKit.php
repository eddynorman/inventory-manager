<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItemKit extends Model
{
    protected $fillable = ['sale_id','item_kit_id','quantity','cost_at_sale','selling_price','total'];

    public function items(){
        return $this->hasMany(SaleItemKitItem::class,'sale_item_kit_id');
    }

    public function sale(){
        return $this->belongsTo(Sale::class,'sale_id');
    }

    public function kit(){
        return $this->belongsTo(ItemKit::class,'item_kit_id');
    }
}
