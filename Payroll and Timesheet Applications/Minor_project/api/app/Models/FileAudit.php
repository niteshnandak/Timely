<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileAudit extends Model
{
    use HasFactory;

    protected $table = 'file_audit';

    protected $fillable = [
        'file_name',
        'reference_name',
        'file_mime_type',
        'timesheet_id',
        'no_of_records',
        'file_size',
        'is_deleted',
    ];
}

