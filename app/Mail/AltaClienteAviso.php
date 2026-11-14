<?php

namespace App\Mail;

use App\Models\AvisoCliente;
use App\Models\Cliente;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Mail al destinatario de un alta rapida de cliente: datos del cliente, motivo del aviso,
 * observacion inicial y los documentos adjuntos (los mismos que quedan en la solapa Docs).
 * Se envia un mail por destinatario (un aviso por destinatario).
 */
class AltaClienteAviso extends Mailable
{
    use Queueable, SerializesModels;

    public $cliente;
    public $aviso;
    public $destinatario;
    public $creador;
    public $observacion;
    public $documentos;

    /**
     * @param Cliente       $cliente
     * @param AvisoCliente  $aviso
     * @param User          $destinatario
     * @param User          $creador
     * @param string|null   $observacion
     * @param \Illuminate\Support\Collection|array $documentos DocumentoCliente ya guardados (carpeta docs)
     */
    public function __construct(Cliente $cliente, AvisoCliente $aviso, User $destinatario, User $creador, $observacion, $documentos)
    {
        $this->cliente = $cliente;
        $this->aviso = $aviso;
        $this->destinatario = $destinatario;
        $this->creador = $creador;
        $this->observacion = $observacion;
        $this->documentos = $documentos;
    }

    public function build()
    {
        $mail = $this->to($this->destinatario->email)
            ->from(env('MAIL_FROM_ADDRESS'))
            ->subject('Nuevo cliente: ' . $this->cliente->nombre_completo)
            ->markdown('emails.alta-cliente-aviso');

        foreach ($this->documentos as $doc) {
            $path = Storage::disk('uploads')->path('docs/' . $doc->nombre_archivo);
            if (is_file($path)) {
                $mail->attach($path, ['as' => $doc->nombre_real ?: $doc->nombre_archivo]);
            }
        }

        return $mail;
    }
}
