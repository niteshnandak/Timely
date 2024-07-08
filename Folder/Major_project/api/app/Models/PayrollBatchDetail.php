<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollBatchDetail extends Model
{
    use HasFactory;

    protected $table = 'payroll_batch_detail';

    protected $primaryKey = 'payroll_detail_id';

    protected $fillable = [
        'payroll_batch_id',  // Add this if not present
        'company_id',
        'organisation_id',
        'people_id',
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
        'is_rollback',
        'is_deleted',
        'created_at',
        'updated_at',
        'updated_by',
        'created_by'
    ];

    public function payrollBatch()
    {
        return $this->belongsTo(PayrollBatch::class, 'payroll_batch_id', 'payroll_batch_id');
    }
    
    public function people()
    {
        return $this->belongsTo(People::class, 'people_id', 'people_id');
    }
}
