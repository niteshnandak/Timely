<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminSetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(private $url, private $token, private $id, private $fullname, private $userName, private $organisationName, private $firstName, private $surName, private $email)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Admin request to set your password',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'admin-mail.admin-set-password-mail',
            with: ['url'=>$this->url, 'token'=>$this->token, 'userName'=>$this->userName, 'id'=>$this->id, 'fullname'=>$this->fullname, 'organisationName'=>$this->organisationName, 'firstName'=>$this->firstName, 'surName'=>$this->surName, 'email'=>$this->email],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}