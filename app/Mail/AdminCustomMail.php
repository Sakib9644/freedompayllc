<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminCustomMail extends Mailable
{
    use SerializesModels;

    public $bodyMessage;

    public function __construct($subject, $bodyMessage)
    {
        $this->subject($subject);
        $this->bodyMessage = $bodyMessage;
    }

    public function build()
    {
        return $this->view('emails.admin_custom');
    }
}