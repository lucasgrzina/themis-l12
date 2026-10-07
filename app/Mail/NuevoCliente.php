<?php

namespace App\Mail;

use App\Models\Cliente;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NuevoCliente extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $cliente;
    public function __construct(Cliente $cliente)
    {
        $this->cliente = $cliente;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $emails = [];
        foreach ($this->cliente->emails as $row) {
            $emails[] = $row['val'];
        }
        return $this->to($emails)
                    ->from(env('MAIL_FROM_ADDRESS'))
                    ->subject('Nuevo cliente')
                    ->markdown('emails.nuevo-cliente');
    }
}
