<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewDocumentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Document $document)
    {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo documento laboral disponible - SWAT Protection 51',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.documents.new_notification',
        );
    }
}
