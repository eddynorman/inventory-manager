<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOrderPayments extends Model
{
    protected $fillable = [
        'supplier_order_id',
        'amount',
        'reference',
        'paid_by'
    ];

    public function supplierOrder(){
        return $this->belongsTo(SupplierOrder::class,'supplier_order_id');
    }

    public function paidBy(){
        return $this->belongsTo(User::class,'user_id');
    }
    
}
