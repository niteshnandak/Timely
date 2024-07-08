<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceDetails extends Model
{
    //links the elequent model for the model factory
    use HasFactory;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $table = 'invoice_details';

    protected $primaryKey = 'invoice_details_id';

    protected $fillable =
    [
        'invoice_details_id',
        'invoice_id',
        'people_id',
        'assignment_id',
        'customer_id',
        'company_id',
        'timesheet_id',
        'timesheet_detail_id',
        'organisation_id',
        'period_end_date',
        'description',
        'quantity',
        'unit_price',
        'vat_percent',
        'gross_amount',
        'is_deleted',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice_details) {
            if (Auth::check()) {
                $invoice_details->created_by = Auth::id();
                $invoice_details->updated_by = Auth::id();
                Log::info('Creating: ' . Auth::id());
            }
        });

        static::updating(function ($invoice_details) {
            if (Auth::check()) {
                $invoice_details->updated_by = Auth::id();
            }
        });
    }

    // public function invoice()
    // {
    //     return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    // }
}
