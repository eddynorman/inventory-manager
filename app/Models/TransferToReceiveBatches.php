<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferToReceiveBatches extends Model
{
    protected $fillable = ['transfer_id','to_location','item_id','batch_id','is_received','quantity','unit_cost'];
}
