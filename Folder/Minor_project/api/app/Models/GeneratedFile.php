<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'file_path',
        'invoice_id'
    ];

    // Define the relationship with the Invoice model
    // public function invoice()
    // {
    //     return $this->belongsTo(Invoice::class);
    // }

}
