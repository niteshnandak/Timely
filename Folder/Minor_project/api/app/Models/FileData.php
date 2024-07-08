<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileData extends Model
{
    use HasFactory;

    protected $table = 'file_data';

    protected $fillable = [
        'timesheet_id',
        'file_id',
        'worker_id',
        'worker_name',
        'timesheet_detail_date',
        'organisation',
        'hourly_pay',
        'hours_worked',
        'is_deleted',
    ];
}
