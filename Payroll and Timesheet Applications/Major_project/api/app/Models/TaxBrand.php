<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxBrand extends Model
{
    use HasFactory;
    protected $table ="tax_brand";

    protected $primaryKey = 'tax_brand_id';
}
