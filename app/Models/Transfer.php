<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = ['transfer_date', 'from_location_id', 'to_location_id', 'user_id', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function items()
    {
        return $this->hasMany(TransferItem::class);
    }
}


