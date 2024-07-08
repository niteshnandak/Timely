<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;

    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
    }

    public function build()
    {
        Log::info('Building PayslipMail with data: ' . json_encode($this->emailData));

        if (!isset($this->emailData['to_email'])) {
            Log::error('No recipient email address provided for payslip email.');
            throw new \InvalidArgumentException('No recipient email address provided');
        }

        if (!isset($this->emailData['attachment'])) {
            Log::error('No attachment provided for payslip email.');
            throw new \InvalidArgumentException('No attachment provided');
        }

        if (!isset($this->emailData['payslip_data'])) {
            Log::error('No payslip data provided for email.');
            throw new \InvalidArgumentException('No payslip data provided');
        }

        return $this->view('mail.payslip-mail')
            ->subject($this->emailData['subject'] ?? 'Your Payslip')
            ->to($this->emailData['to_email'])
            ->with('data', $this->emailData['payslip_data'])
            ->attachData($this->emailData['attachment'], 'payslip.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
