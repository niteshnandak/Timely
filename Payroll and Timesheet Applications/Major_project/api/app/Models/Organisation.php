<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    public $timestamps = false;
    protected $table = "organisation";
    protected $primaryKey = 'organisation_id';
    use HasFactory;
    protected $fillable = [
        'name',
        'email_address',
        'address_id',
        'description',
        'contact_number',
        'status',
        'org_logo_path',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'is_deleted'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'organisation_id', 'organisation_id');
    }
}
