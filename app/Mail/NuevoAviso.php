<?php

namespace App\Mail;

use App\Models\AvisoCliente;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NuevoAviso extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $aviso;
    public $destinatario;
    public function __construct(AvisoCliente $aviso)
    {
        $this->aviso = $aviso;
        $this->destinatario = User::find($aviso->type_id);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->destinatario->email)
                    ->from(env('MAIL_FROM_ADDRESS'))
                    ->subject('Nuevo aviso asignado.')
                    ->markdown('emails.nuevo-aviso');        
    }
}
