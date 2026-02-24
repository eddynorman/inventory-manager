<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOrderItem extends Model
{
    protected $fillable = [
        'supplier_order_id',
        'item_id',
        'unit_id',
        'quantity',
        'requested_quantity',
        'requested_unit_price',
        'actual_unit_price',
        'total'
    ];

    public function unit(){
        return $this->belongsTo(Unit::class,'unit_id')->get('name')[0]->name;
    }

    public function item(){
        return $this->belongsTo(Item::class,'item_id')->get('name')[0]->name;
    }

    public function supplierOrder(){
        return $this->belongsTo(SupplierOrder::class,'supplier_order_id');
    }
}
