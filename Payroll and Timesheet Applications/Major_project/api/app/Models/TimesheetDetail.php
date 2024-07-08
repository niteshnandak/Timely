<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetDetail extends Model
{
    use HasFactory;

    protected $table ="timesheet_detail";

    protected $primaryKey = 'timesheet_detail_id';

    protected $fillable = [
        'timesheet_id',
        'assignment_num',
        'people_id',
        'people_name',
        'quantity',
        'description',
        'unit_price',
        'mapping_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'updated_at'
    ];
}
