<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueItem extends Model
{
    protected $fillable = ['issue_id', 'item_id', 'unit_id', 'quantity'];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}


