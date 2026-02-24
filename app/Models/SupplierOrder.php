<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOrder extends Model
{
    protected $fillable = [
        'requisition_id',
        'supplier_id',
        'total_amount',
        'amount_paid',
        'amount_pending',
        'payment_status',
        'created_by'
    ];

    public function requisition(){
        return $this->belongsTo(Requisition::class,'requisition_id');
    }

    public function createdBy(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function items(){
        return $this->hasMany(SupplierOrderItem::class,'supplier_order_id');
    }

    public function payments(){
        return $this->hasMany(SupplierOrderPayments::class,'supplier_order_id');
    }

    public function supplier(){
        return $this->belongsTo(Supplier::class,'supplier_id');
    }
}
