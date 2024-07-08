<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PayrollHistory extends Model
{
    use HasFactory;
    protected $table = 'payroll_history';
    CONST STATUS_ROLLBACK = 1;
    CONST STATUS_UNROLLBACK = 0;

    protected $primaryKey = 'payroll_history_id';

    protected $fillable = [
        'payroll_batch_id',
        'payroll_batch_detail_id',
        'task_schedular_id',
        'people_id',
        'company_id',
        'organisation_id',
        'gross_salary',
    	'taxable_amount',
    	'total_payment_amount',
        'er_tax',
    	'ee_tax',
    	'total_tax_deduction',
    	'net_pay',
        'total_hours',
        'hourly_pay',
        'expense_amount',
        'expenses',
        'invoices',
        'margin',
    	'payrolled_status',
        'payslip_id',
        'payslip_status',
    	'is_deleted',
        'is_rollback',
    	'created_at',
        'created_by',
    	'updated_at',
        'updated_by'
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();

            }
            Log::debug(Auth::id());
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    public function payrollBatch()
    {
        return $this->belongsTo(PayrollBatch::class, 'payroll_batch_id');
    }
}
