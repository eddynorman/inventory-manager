<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['name', 'barcode', 'category_id', 'supplier_id', 'initial_stock', 'current_stock', 'reorder_level', 'is_active', 'is_sale_item'];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function disposed(){
        return $this->hasMany(DisposeItem::class);
    }

    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }

    public function expiries(){
        return $this->hasMany(ItemExpiryDate::class);
    }
    public function itemKits(){
        return $this->belongsToMany(ItemKit::class, 'item_kit_items')->withPivot('quantity', 'unit_id')->withTimestamps();
    }
    public function stockAdjustments(){
        return $this->hasMany(StockAdjustmentItem::class);
    }

    public function locations(){
        return $this->belongsToMany(
            Location::class,        // Related model
            ItemLocation::class, // Pivot table
            'location_id',      // Foreign key on pivot table referencing Location
            'item_id'           // Foreign key on pivot table referencing Item
        );
    }


    public function units()
    {
        return $this->belongsToMany(Unit::class, 'item_units')->withTimestamps();
    }

    public function requisitionItems()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function receivingItems()
    {
        return $this->hasMany(ReceivingItem::class);
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
