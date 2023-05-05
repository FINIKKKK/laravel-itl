<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable {
    use Queueable, SerializesModels;

    public $mailData;

    public function __construct($mailData) {
        $this->mailData = $mailData;
    }

    public function build() {
        return $this
            ->subject('Test Email')
            ->view('emails.index');
    }
}
