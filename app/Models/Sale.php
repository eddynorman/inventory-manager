<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['sale_date', 'customer_id', 'user_id', 'location_id', 'created_by','total_amount', 'payment_status'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function payments(){
        return $this->hasMany(SalePayment::class,'sale_id');
    }

    public function servedBy()
    {
        return $this->belongsToMany(User::class, 'sale_served_bies')
                    ->withTimestamps();
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'sale_locations')
                    ->withTimestamps();
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class,'sale_id');
    }

    public function kits(){
        return $this->hasMany(SaleItemKit::class,'sale_id');
    }
}


