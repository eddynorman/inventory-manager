<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['sale_date', 'customer_id', 'user_id', 'location_id', 'total_amount', 'payment_status'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}


