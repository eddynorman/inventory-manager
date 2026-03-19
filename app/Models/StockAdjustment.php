<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = ['user_id', 'description'];

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function location(){
        return $this->belongsTo(Location::class,'location_id');
    }

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }
}
