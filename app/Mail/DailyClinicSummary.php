<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyClinicSummary extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $summary
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Daily clinic summary — '
                . $this->summary['clinic_name']
                . ' — '
                . $this->summary['date_label'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-clinic-summary',
        );
    }
}