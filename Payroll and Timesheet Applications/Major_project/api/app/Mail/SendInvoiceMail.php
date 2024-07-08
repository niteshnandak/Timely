<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInvoiceMail extends Mailable
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
        ->to($this->data['to_email'])
        ->attachData($this->data['attachment'], $this->data['template_data']['invoice']['invoice_number']. '.pdf' , [
            'mime' => 'application/pdf',
        ])
        ->view('invoice-pdf.invoice-mail', ['data' => $this->data]);
}

}
