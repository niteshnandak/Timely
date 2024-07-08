<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PayrollBatch extends Model
{
    use HasFactory;

    protected $table = 'payroll_batch';

    protected $primaryKey = 'payroll_batch_id';
    public $incrementing = true;

    protected $fillable = [
        'payroll_batch_number',
        'payroll_batch_name',
        'payroll_batch_date',
        'no_of_payroll',
        'payroll_batch_status',
        'company_id',
        'organisation_id',
        'is_deleted',
        'created_by',
        'updated_by'
    ];

    public function payrollDetails()
    {
        return $this->hasMany(PayrollBatchDetail::class, 'payroll_batch_id', 'payroll_batch_id');
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::check()) {
                $user = Auth::user();
                $model->created_by = $user->user_id;
                $model->updated_by = $user->user_id;
                $model->organisation_id = $user->organisation_id;
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    // Define the inverse of the relationship if needed
    public function payrollHistories()
    {
        return $this->hasMany(PayrollHistory::class, 'payroll_batch_id');
    }
}
