<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItemBatch extends Model
{
    protected $fillable = ['sale_item_id','sale_item_kit_item_id','stock_batch_id','quantity','unit_cost','total','type','type_id'];
}
