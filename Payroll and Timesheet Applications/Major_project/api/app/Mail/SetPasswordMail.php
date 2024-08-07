<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Log;

class SetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public function __construct($data)
    {
        $this->data = $data;

    }
    public function build()
    {
        return $this->subject($this->data['subject'])
            ->to($this->data['to_mail'])
            ->view('mail.set-password-mail', ['data' => $this->data]);
    }
}
