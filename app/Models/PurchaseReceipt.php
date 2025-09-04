<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReceipt extends Model
{
    protected $fillable = ['purchase_id', 'receipt_path'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
