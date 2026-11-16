<?php

namespace App\Mail;

use App\Models\CareerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CareerApplicationAcknowledgement extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public function __construct(public CareerApplication $application) {}
    public function envelope(): Envelope { return new Envelope(subject: 'We received your CEBINOVA application'); }
    public function content(): Content { return new Content(view: 'mail.careers.acknowledgement'); }
    public function failed(\Throwable $exception): void { Log::error('Applicant acknowledgement failed.', ['application_id' => $this->application->id, 'error' => $exception->getMessage()]); }
}
