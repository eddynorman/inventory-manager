<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferItem extends Model
{
    protected $fillable = ['transfer_id', 'item_id', 'unit_id', 'quantity'];

    public function transfer()
    {
        return $this->belongsTo(Transfer::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}


