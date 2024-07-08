<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class InvoiceDetail extends Model
{
    protected $table = "invoice_detail";
    protected $fillable = [
        'timesheet_id',
        'timesheet_detail_id',
        'invoice_id',
        'worker_id',
        'worker_name',
        'invoice_date',
        'total_amount',
        'organisation',
        'hourly_pay',
        'hours_worked'
    ];


    public function timesheetDetail()
    {
        return $this->belongsTo(TimesheetDetail::class, 'timesheet_detail_id');
    }

}
