<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $fillable = ['issue_date', 'from_location_id', 'to_location_id', 'user_id', 'description','status'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function processedBy(){
        return $this->belongsTo(User::class,'processed_by');
    }

    public function rejectedBy(){
        return $this->belongsTo(User::class,'rejected_by');
    }


    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function items()
    {
        return $this->hasMany(IssueItem::class);
    }
}


