<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['purchase_date', 'requisition_id', 'purchased_by_id', 'supplier_id', 'total_amount', 'payment_status'];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function purchaser()
    {
        return $this->belongsTo(User::class, 'purchased_by_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(PurchaseReceipt::class);
    }
}
