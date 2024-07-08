<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Company extends Model
{
    use HasFactory;


    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $table = 'company';

    protected $primaryKey = 'company_id';

    protected $fillable = [
        'organisation_id',
        'company_name',
        'email_address',
        'address_id',
        'phone_number',
        'vat_percent',
        'company_description',
        'status',
        'is_deleted',
        'created_by',
        'updated_by'
    ];

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'address_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
