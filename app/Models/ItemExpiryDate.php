<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemExpiryDate extends Model
{
    protected $fillable = ['item_id', 'expiry_date'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
