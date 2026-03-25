<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $fillable = ['sale_id','payment_method','amount','received_by'];

    public function receivedBy(){
        return $this->belongsTo(User::class,'received_by');
    }

    public function sale(){
        return $this->belongsTo(Sale::class,'sale_id');
    }
}
