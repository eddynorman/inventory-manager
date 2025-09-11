<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name', 'buying_price', 'selling_price', 'is_smallest_unit', 'smallest_units_number', 'buying_price_includes_tax', 'selling_price_includes_tax', 'is_active', 'item_id'];

    public function requisitionItems()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function item(){
        return $this->belongsTo(Item::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function receivingItems()
    {
        return $this->hasMany(ReceivingItem::class);
    }

    public function itemKitItems()
    {
        return $this->hasMany(ItemKitItem::class);
    }

    public function stockAdjustmentItems()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function transferItems()
    {
        return $this->hasMany(TransferItem::class);
    }

    public function issueItems()
    {
        return $this->hasMany(IssueItem::class);
    }
}
