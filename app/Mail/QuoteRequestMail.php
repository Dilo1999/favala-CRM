<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\UploadedFile;

class QuoteRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public ?string $company,
        public string $email,
        public ?string $phone,
        public ?string $industry,
        public ?string $details,
        public ?UploadedFile $file = null,
    ) {}

    public function envelope(): Envelope
    {
        $to = config('mail.contact_to') ?: config('mail.from.address');

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            replyTo: [new Address($this->email, $this->name)],
            to: [$to],
            subject: '[Quote Request] ' . ($this->company ?: 'New RFQ'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-request',
        );
    }

    public function attachments(): array
    {
        if (! $this->file) {
            return [];
        }

        return [
            Attachment::fromPath($this->file->getRealPath())
                ->as($this->file->getClientOriginalName())
                ->withMime($this->file->getMimeType() ?: 'application/octet-stream'),
        ];
    }
}

