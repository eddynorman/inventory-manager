<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name', 'location_type', 'address', 'phone', 'email', 'staff_responsible', 'description'];

    public function items()
    {
        return $this->hasMany(ItemLocation::class);
    }

    public function receivings()
    {
        return $this->hasMany(Receiving::class);
    }

    public function disposals()
    {
        return $this->hasMany(DisposeItem::class);
    }
}
