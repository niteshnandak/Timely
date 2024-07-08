<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskSchedular extends Model
{
    use HasFactory;

    protected $primarykey = "task_schedular_id";

    protected $table = "task_schedular";

    protected $fillable = [
        'task_schedular_id',
        'task_id',
        'status',
        'param',
        'timesheet_id',
        'timesheet_detail_id',
        'timesheet_id',
        'message',
        'exception',
        'created_by',
        'updated_by'
    ];

}
