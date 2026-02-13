<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemKit extends Model
{
    protected $fillable = ['name', 'description', 'selling_price','selling_price_includes_tax'];

    public function items(){
        return $this->belongsToMany(Item::class, 'item_kit_items')->withPivot('id','quantity', 'unit_id', 'item_id')->withTimestamps();
    }

    public function kitItems()
    {
        return $this->hasMany(ItemKitItem::class,'item_kit_id');
    }
}
