<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    use HasFactory;

    CONST STATUS_DELETED = 0;
    CONST STATUS_ACTIVE = 1;

    protected $table = 'people';

    protected $primaryKey = 'people_id';

    protected $fillable = [
        'people_name',
        'birth_date',
        'email_address',
        'phone_number',
        'job_title',
        'gender',
        'organisation_id',
        'status',
        'address_id',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by' ,
        'is_deleted', 
    ];

    public function organisation(){
        return $this->belongsTo(Organisation::class,'organisation_id');
    }

    public function address(){
        return $this->belongsTo(Address::class,'address_id');
    }

}
