<?php

namespace App\Mail;

use App\Models\TramiteCliente;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TramiteIniciado extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $tramite;
    public function __construct(TramiteCliente $tramite)
    {
        $this->tramite = $tramite;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $emails = [];
        foreach ($this->tramite->cliente->emails as $row) {
            $emails[] = $row['val'];
        }
        return $this->to($emails)
                    ->from(env('MAIL_FROM_ADDRESS'))
                    ->subject('Aviso de trámite iniciado.')
                    ->markdown('emails.tramite-iniciado');
    }
}
