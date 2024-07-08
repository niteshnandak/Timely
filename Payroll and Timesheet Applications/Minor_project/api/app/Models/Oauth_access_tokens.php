<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oauth_access_tokens extends Model
{
    use HasFactory;

    protected $table = "oauth_access_tokens";
    public $timestamps = false;
    protected $primaryKey ="id";
}
