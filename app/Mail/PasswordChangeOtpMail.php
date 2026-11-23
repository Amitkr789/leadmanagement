<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordChangeOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $otp,
        public int $minutes = 10,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->otp . ' is your verification code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-otp',
        );
    }
}