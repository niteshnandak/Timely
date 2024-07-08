<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $primaryKey = 'expense_id';

    protected $fillable = [
        'expense_number',
        'people_id',
        'company_id',
        'organisation_id',
        'amount',
        'expense_type_id',
        'expense_date',
        'status',
        'is_deleted',
        'created_by',
        'updated_by',
    ];

    protected static function boot()
    {
        parent::boot();

        // Ensure expense_number is set if not provided
        static::creating(function ($expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = 'EXP0';
            }
        });

        // Update expense_number after the expense is created to include the expense_id
        static::created(function ($expense) {
            $expense->expense_number = 'EXP0' . $expense->expense_id;
            $expense->save();
        });
    }

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class, 'expense_type_id');
    }

    // relationship between the expenses with the people
    public function peopleEmploymentDetail()
    {
        return $this->belongsTo(PeopleEmploymentDetail::class, 'people_id', 'people_id');
    }
}
