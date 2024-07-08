<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $table = 'invoice';

    protected $primaryKey = 'invoice_id';

    protected $fillable =
    [
        'invoice_number',
        'timesheet_id',
        'people_id',
        'assignment_id',
        'customer_id',
        'company_id',
        'organisation_id',
        'period_end_date',
        'invoice_date',
        'total_amount',
        'invoice_type',
        'invoice_status',
        'email_status',
        'payroll_status',
        'is_deleted',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (Auth::check()) {
                $invoice->created_by = Auth::id();
                $invoice->updated_by = Auth::id();
            }
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'INV0';
            }
        });

        static::updating(function ($invoice) {
            if (Auth::check()) {
                $invoice->updated_by = Auth::id();
            }
        });

        static::created(function ($invoice) {
            $invoice->invoice_number = 'INV0' . $invoice->invoice_id;
            $invoice->save();
        });
    }

    // public function lineItems()
    // {
    //     return $this->hasMany(InvoiceDetails::class, 'invoice_id', 'invoice_id');
    // }
}
