<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClosingStockSession extends Model
{
    protected $fillable = ['location_id','recorded_by','date'];
}
