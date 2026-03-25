<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleServedBy extends Model
{
    protected $fillable = ['sale_id','user_id'];

    public function sale(){
        return $this->belongsTo(Sale::class,'sale_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
