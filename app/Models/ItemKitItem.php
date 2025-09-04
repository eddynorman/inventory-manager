<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemKitItem extends Model
{
    protected $fillable = ['item_kit_id','item_id','unit_id','quantity'];

    public function kit(){
        return $this->belongsTo(ItemKit::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
