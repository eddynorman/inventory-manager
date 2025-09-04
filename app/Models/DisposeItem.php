<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisposeItem extends Model
{
    protected $fillable = ['item_id', 'location_id', 'user_id', 'quantity', 'reason'];

    public function item(){
        return $this->belongsTo(Item::class);
    }

    public function location(){
        return $this->belongsTo(Location::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
