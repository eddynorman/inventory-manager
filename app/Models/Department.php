<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function categories(){
        return $this->hasMany(Category::class);
    }

    public function items(){
        return $this->hasManyThrough(Item::class, Category::class);
    }

    public function __toString()
    {
        return strtoupper($this->name);
    }
}
