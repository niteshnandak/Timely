<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{

    protected $table = "invoice";

    protected $fillable = [
        'timesheet_id',
        'worker_id',
        'worker_name',
        'total_amount',
        'taxed_amount',
        'organisation',
    ];
}
