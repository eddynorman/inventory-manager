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
            'item_locations',
            'item_id',         // foreign key on pivot for this model
            'location_id'      // foreign key on pivot for related model
        );
    }


    public function units()
    {
        return $this->hasMany(Unit::class);
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

    /**
     * Scope a query to only include low stock items.
    */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'reorder_level');
    }

    public function scopeHighStock($query)
    {
        return $query->whereColumn('current_stock', '>', 'reorder_level');
    }

}
