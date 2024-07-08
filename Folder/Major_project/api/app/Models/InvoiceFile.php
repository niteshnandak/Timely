<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceFile extends Model
{
    use HasFactory;
    protected $table = 'invoice_pdf_file';
    protected $primaryKey = 'invoice_file_id';
    use HasFactory;
    protected $fillable = [
        'file_name',
        'file_path',
        'invoice_id',
    ];
}
