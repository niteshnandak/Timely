<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $table = 'address';

    protected $primaryKey = 'address_id';
    CONST STATUS_DELETED = 0;

    protected $fillable =
    [
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'pincode',
        'organisation_id',
        'people_id',
        'customer_id',
        'company_id',
        'is_deleted',
        'created_by',
        'updated_by'
    ];
}
