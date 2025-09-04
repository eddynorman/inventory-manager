<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'email', 'zip_code','street','city'];

    public function phones()
    {
        return $this->hasMany(CustomerPhone::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
