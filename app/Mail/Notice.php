<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Satu template email untuk semua kabar ke penitip. */
class Notice extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $judul,
        public array $baris = [],
        public ?string $tombol = null,
        public ?string $url = null,
        public ?string $kode = null,
        public array $opt = [],
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->opt['subject'] ?? $this->judul);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.notice');
    }
}
