<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PayrollCustomer extends Model
{
    use HasFactory;
    CONST STATUS_UNDELETED = 0;
    CONST STATUS_DELETED = 1;
    protected $table = 'payroll_customer';
    protected $primaryKey = 'payroll_customer_id';

    protected $fillable =[
        'payroll_batch_id',
        'customer_id',
        'organisation_id',
        'company_id',
        'no_of_invoice',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'is_deleted',
    ];

    public function payroll_batch(){
        return $this->belongsTo(PayrollBatch::class,'payroll_batch_id');
    }
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
