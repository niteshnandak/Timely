<?php

namespace App\Services;

use App\Mail\SetPasswordMail;
use Illuminate\Support\Facades\Mail;

class SetPasswordEmailService
{
    public function send_email($email, $fullname, $id, $url)
    {

        if (Mail::to($email)->send(new SetPasswordMail($fullname, $id, $url))) {
            return true;
        } else {
            return false;
        }
    }
}
