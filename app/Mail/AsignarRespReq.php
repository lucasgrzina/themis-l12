<?php

namespace App\Mail;

use App\Models\RequerimientoCliente;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AsignarRespReq extends Mailable
{
    use Queueable, SerializesModels;
    public $req;
    protected $responsables;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(RequerimientoCliente $req, $responsables=false)
    {
        $this->req = $req;
        $this->responsables = $responsables;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $responsables = [];
        foreach ($this->req->responsables as $resp) {
            if (in_array($resp->user_id, $this->responsables)) 
            {
                $responsables[] = $resp->user->email;    
            }
            
        }
        return $this->bcc($responsables)
                    ->from(env('MAIL_FROM_ADDRESS'))
                    ->subject('Fuiste asignado a un requerimiento')
                    ->markdown('emails.asignar-resp-req');
    }
}
