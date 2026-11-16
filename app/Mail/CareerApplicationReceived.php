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

class CareerApplicationReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public function __construct(public CareerApplication $application) {}
    public function envelope(): Envelope { return new Envelope(subject: 'New application: '.$this->application->job->title); }
    public function content(): Content { return new Content(view: 'mail.careers.received'); }
    public function failed(\Throwable $exception): void { Log::error('Recruitment notification failed.', ['application_id' => $this->application->id, 'error' => $exception->getMessage()]); }
}
