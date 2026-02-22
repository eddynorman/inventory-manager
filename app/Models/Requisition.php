<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $fillable = [
        'department_id',
        'requested_by_id',
        'approved_by_id',
        'reviewed_by',
        'funded_by',
        'rejected_by',

        'cost',
        'status',
        'description',

        'date_requested',
        'reviewed_on',
        'date_approved',
        'funded_on',

        'fund_amount',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date_requested' => 'datetime',
        'reviewed_on'    => 'datetime',
        'date_approved'  => 'datetime',
        'funded_on'      => 'datetime',
        'rejected_at'    => 'datetime',
        'fund_amount'    => 'decimal:2',
    ];

    public function department(){
        return $this->belongsTo(Department::class,'department_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function review(int $userId): void
    {
        $this->update([
            'status'      => 'reviewed',
            'reviewed_by' => $userId,
            'reviewed_on' => now(),
        ]);
    }

    public function approve(int $userId): void
    {
        $this->update([
            'status'           => 'approved',
            'approved_by_id'   => $userId,
            'date_approved'    => now(),
        ]);
    }

    public function fund(int $userId, float $fundAmount): void
    {
        $this->update([
            'status'      => 'funded',
            'funded_by'   => $userId,
            'funded_on'   => now(),
            'fund_amount' => $fundAmount,
        ]);
    }

    public function reject(int $userId, string $reason): void
    {
        $this->update([
            'status'           => 'rejected',
            'rejected_by'      => $userId,
            'rejected_at'      => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
