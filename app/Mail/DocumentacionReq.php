<?php

namespace App\Mail;

use App\Http\Controllers\Traits\GenerarDocumentacionTrait;
use App\Models\RequerimientoCliente;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentacionReq extends Mailable
{
    use Queueable, SerializesModels, GenerarDocumentacionTrait;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $req;
    public function __construct(RequerimientoCliente $req)
    {
        $this->req = $req;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $emails = [];
        if (env('APP_ENV','local') !== 'local') 
        {
            foreach ($this->req->cliente->emails as $row) 
            {
                $emails[] = $row['val'];
            }        
        } else {
            $emails[] = 'lucasgrzina@gmail.com';
        }
        
        return $this->to($emails)
                    ->with([
                        'contenido' => $this->getContenidoPorArea($this->req)
                    ])
                    ->from(env('MAIL_FROM_ADDRESS'))
                    ->subject('Documentación requerida')
                    ->markdown('emails.docu-req');
    }
}
