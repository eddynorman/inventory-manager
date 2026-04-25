<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['department_id','expense_category_id','amount','recorded_by'];

    public function department(){
        return $this->belongsTo(Department::class,'department_id');
    }

    public function category(){
        return $this->belongsTo(ExpenseCategory::class,'expense_category_id');
    }

    public function recordedBy(){
        return $this->belongsTo(User::class,'recorded_by');
    }

    public function receipts(){
        return $this->hasMany(ExpenseReceipt::class,'expense_id');
    }

    public function items(){
        return $this->hasMany(ExpenseItem::class,'expense_id');
    }
}
