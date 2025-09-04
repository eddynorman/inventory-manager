<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receiving extends Model
{
    protected $fillable = ['purchase_id', 'received_by_id', 'location_id'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function items()
    {
        return $this->hasMany(ReceivingItem::class);
    }
}
