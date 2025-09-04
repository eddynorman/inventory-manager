<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $fillable = ['requested_by_id', 'approved_by_id', 'cost', 'status', 'description', 'date_requested', 'date_approved'];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }
}
