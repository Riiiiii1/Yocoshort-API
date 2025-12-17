<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $url;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $url)
    {
        $this->user = $user;
        $this->url = $url;
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifica tu dirección de correo electrónico - ' . config('app.name'),
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-email', 
            with: [
                'user' => $this->user,
                'url' => $this->url,
            ],
        );
    }
    public function attachments(): array
    {
        return [];
    }
}
