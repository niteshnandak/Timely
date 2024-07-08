<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeopleEmploymentDetail extends Model
{
    use HasFactory;

    protected $table = 'people_employment_details';
    CONST STATUS_DELETED = 0;

    protected $fillable = [
        'organisation_id',
        'people_id',
        'people_name',
        'company_id',
        'company_name',
        'joining_date',
        'pay_frequency',
        'nino_number',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'is_deleted',
    ];

    public function people(){
        return $this->belongsTo(People::class,'people_id');
    }

    // creating relation of the people with their expenses
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'people_id');
    }
}
