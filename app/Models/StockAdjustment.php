<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = ['user_id', 'location_id','description'];

    public function createdBy()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function location(){
        return $this->belongsTo(Location::class,'location_id');
    }

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }
}
