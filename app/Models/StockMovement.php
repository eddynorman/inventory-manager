<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = ['item_id','from_location','to_location','quantity','type','reference_type','reference_id','created_by'];

    public function item(){
        return $this->belongsTo(Item::class,'item_id');
    }

    public function to(){
        return $this->belongsTo(Location::class,'to_location');
    }

     public function from(){
        return $this->belongsTo(Location::class,'from_location');
    }
}
