<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = ['bank_name','account_number','balance'];

    public function transactions(){
        return $this->hasMany(Banking::class,'bank_account');
    }
}
