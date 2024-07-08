<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PayrollCustomerInvoice extends Model
{
    use HasFactory;
    CONST STATUS_UNDELETED = 0;
    CONST STATUS_DELETED = 1;
    CONST STATUS_SELECTED = 1;
    CONST STATUS_UNSELECTED = 0;
    CONST STATUS_PAYROLLED = 1;
    CONST STATUS_UNPAYROLLED = 0;
   
    protected $table = 'payroll_customer_invoice';
    protected $primaryKey = 'payroll_customer_invoice_id';
    protected $fillable =[
        'payroll_customer_id',
        'payroll_batch_id',
        'invoice_id',
        'organisation_id',
        'company_id',
        'people_id',
        'invoice_selected_status',
        'invoice_payrolled_status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'is_deleted',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
