<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customer';

    protected $primaryKey = 'customer_id';

    protected $fillable =
    [
        'company_id',
        'organisation_id',
        'customer_name',
        'email_address',
        'phone_number',
        'no_of_assignments',
        'address_id',
        'customer_vat_percentage',
        'is_deleted',
        'created_by',
        'updated_by'
    ];

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'customer_id');
    }

}
