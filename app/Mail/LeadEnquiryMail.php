<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeadEnquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead, public string $audience) {}

    public function envelope(): Envelope
    {
        $subject = $this->audience === 'admin'
            ? 'New enquiry from '.$this->lead->name
            : 'We received your enquiry - CEBINOVA Technologies';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.lead-enquiry');
    }
}
