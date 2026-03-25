<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    protected $fillable = ['item_id','location_id','remaining_quantity','unit_cost','reference_type','reference_id'];

    public function item(){
        return $this->belongsTo(Item::class,'item_id');
    }

    public function location(){
        return $this->belongsTo(Location::class,'location_id');
    }
}
