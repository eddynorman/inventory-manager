<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedItems extends Model
{
    protected $fillable = ['item_id','location_id','recorded_by','quantity','closing_stock_session_id'];

    public function recordedBy(){
        return $this->belongsTo(User::class,'recorded_by');
    }

    public function location()
    {
        return $this->belongsTo(Location::class,'location_id');
    }

    public function item(){
        return $this->belongsTo(Item::class,'item_id');
    }
}
