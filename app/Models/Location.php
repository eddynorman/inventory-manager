<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name', 'location_type', 'address', 'phone', 'email', 'staff_responsible', 'description'];

    public function items()
    {
        return $this->belongsToMany(
            Item::class,        // Related model
            ItemLocation::class, // Pivot table
            'location_id',      // Foreign key on pivot table referencing Location
            'item_id'           // Foreign key on pivot table referencing Item
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'staff_responsible');
    }

    public function receivings()
    {
        return $this->hasMany(Receiving::class);
    }

    public function disposals()
    {
        return $this->hasMany(DisposeItem::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function transfersFrom()
    {
        return $this->hasMany(Transfer::class, 'from_location_id');
    }

    public function transfersTo()
    {
        return $this->hasMany(Transfer::class, 'to_location_id');
    }

    public function issuesFrom()
    {
        return $this->hasMany(Issue::class, 'from_location_id');
    }

    public function issuesTo()
    {
        return $this->hasMany(Issue::class, 'to_location_id');
    }
}
