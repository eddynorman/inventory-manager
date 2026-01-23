<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'description','department_id'];
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
