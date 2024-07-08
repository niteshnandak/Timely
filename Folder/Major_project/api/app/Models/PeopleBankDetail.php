<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeopleBankDetail extends Model
{
    use HasFactory;

    protected $table = 'people_bank_details';
    CONST STATUS_DELETED = 0;

    protected $fillable = [
        'organisation_id',
        'people_id',
        'people_name',
        'company_id',
        'company_name',
        'bank_name',
        'bank_branch',
        'account_number',
        'bank_ifsc_code',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'is_deleted',
    ];

    public function people(){
        return $this->belongsTo(People::class,'people_id');
    }
}
