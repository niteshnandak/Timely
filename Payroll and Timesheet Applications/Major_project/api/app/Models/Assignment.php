<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $table ="assignment";

    protected $primaryKey = 'assignment_id';
    
    protected $fillable = [
        'assignment_no',
        'organisation_id',
        'people_id',
        'company_id',
        'customer_id',
        'start_date',
        'end_date',
        'role',
        'location',
        'description',
        'status',
        'type',
        'is_deleted',
        'created_by',
        'updated_by'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($assignment) {
            if (empty($assignment->assignment_num)) {
                $assignment->assignment_num = 'ASS';
            }
        });

        static::created(function ($assignment) {
            $assignment->assignment_num = 'ASS' . $assignment->assignment_id;
            $assignment->save();
        });
    }

    
}

