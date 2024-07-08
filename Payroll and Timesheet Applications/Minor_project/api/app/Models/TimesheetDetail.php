<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetDetail extends Model
{
    use HasFactory;

    protected $table = 'timesheet_detail';

    protected $fillable = [
        'timesheet_id',
        'worker_id',
        'worker_name',
        'timesheet_detail_date',
        'organisation',
        'hourly_pay',
        'hours_worked',
        'draft_status',
        'invoice_status',
        'is_deleted',
    ];

    public function timesheet(){
        return $this->belongsTo(Timesheet::class);
    }

    public function invoiceDetail()
    {
        return $this->hasOne(InvoiceDetail::class, 'timesheet_detail_id');
    }



}
