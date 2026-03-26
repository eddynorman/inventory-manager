<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleLocations extends Model
{
    protected $fillable = ['sale_id','location_id'];

    public function sale(){
        return $this->belongsTo(Sale::class,'sale_id');
    }

    public function location(){
        return $this->belongsTo(Location::class,'location_id');
    }
}
