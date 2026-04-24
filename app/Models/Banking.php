<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banking extends Model
{
    protected $fillable = ['bank_account','amount','type','receipt_path','description','recorded_by'];

    public function recordedBy(){
        return $this->belongsTo(User::class,'recorded_by');
    }

    public function account(){
        return $this->belongsTo(BankAccount::class,'bank_account');
    }
}
