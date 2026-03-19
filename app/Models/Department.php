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
        return $this->hasManyThrough(
            Item::class,
            Category::class,
            'department_id', // Foreign key on categories table
            'category_id',   // Foreign key on items table
            'id',            // Local key on departments table
            'id'             // Local key on categories table
        );
    }

    public function lowStockItems()
    {
        return $this->items()->lowStock();
    }

    public function requisitions(){
        return $this->hasMany(Requisition::class,'department_id','id');
    }

    public function highStockItems()
    {
        return $this->items()->hightock();
    }

    public function __toString()
    {
        return strtoupper($this->name);
    }
}
