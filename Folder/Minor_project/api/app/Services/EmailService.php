<?php

namespace App\Services;

use App\Mail\CustomerMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;

class EmailService
{
    public function sendEmail($data)
    {
        try {
            if (Mail::send(new CustomerMail($data))) {
                return true;
            }
            // Email sent successfully
        } catch (\Exception $e) {
            // return $e->getMessage();
            return $e->getMessage();
        }
    }
}
