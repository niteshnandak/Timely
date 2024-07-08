<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    use HasFactory;

    protected $table = 'timesheet';

    protected $fillable = [
        'timesheet_name',
        'upload_type_csv',
        'timesheet_date',
        'created_by',
        'is_deleted',
        'timesheet_detail_count',
    ];

    public function details(){
        return $this->hasMany(TimesheetDetail::class);
    }

}
