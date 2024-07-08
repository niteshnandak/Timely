<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailTrack extends Model
{
    use HasFactory;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $table = 'mail_track';
    public $timestamps = false; 

    protected $primaryKey = 'mail_track_id';

    protected $fillable =
    [
        'sender_email',
        'recipient_name',
        'recipient_email',
        'subject',
        'opens',
        'clicks',
        'type',
        'type_id',
        'message_id',
        'status',
        'opened_at',
        'clicked_at',
        'created_at',
    ];

}
