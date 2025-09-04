<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemLocation extends Model
{
    protected $fillable = ['item_id', 'location_id', 'quantity'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
