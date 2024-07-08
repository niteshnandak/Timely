<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    use HasFactory;

    protected $table ="timesheet";

    protected $primaryKey = 'timesheet_id';

    protected $fillable = [
        'company_id',
        'organisation_id',
        'timesheet_num',
        'timesheet_name',
        'num_of_rows',
        'invoice_status',
        'invoice_date',
        'period_end_date',
        'upload_type',
        'is_deleted',
        'created_by',
        'updated_by',
        'updated_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($timesheet) {
            if (empty($timesheet->timesheet_num)) {
                $timesheet->timesheet_num = 'TM00';
            }
        });

        static::created(function ($timesheet) {
            $timesheet->timesheet_num = 'TM00' . $timesheet->timesheet_id;
            $timesheet->save();
        });
    }
}
