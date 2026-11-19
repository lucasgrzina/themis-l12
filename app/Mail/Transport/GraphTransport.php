<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

/**
 * Envio de mails por Microsoft Graph (OAuth2, client credentials).
 * Se envia el MIME completo, asi viajan adjuntos, HTML y cabeceras tal cual los arma Laravel.
 * El mensaje queda en "Elementos enviados" del buzon remitente.
 */
class GraphTransport extends AbstractTransport
{
    public function __construct(
        private string $tenantId,
        private string $clientId,
        private string $clientSecret,
        private string $sender
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        // El buzon desde el que se envia es siempre el configurado (el From debe coincidir con el)
        $response = Http::withToken($this->token())
            ->withBody(base64_encode($message->toString()), 'text/plain')
            ->post('https://graph.microsoft.com/v1.0/users/' . rawurlencode($this->sender) . '/sendMail');

        if ($response->status() !== 202) {
            throw new TransportException('Microsoft Graph sendMail fallo (' . $response->status() . '): ' . $response->body());
        }
    }

    private function token(): string
    {
        return Cache::remember('msgraph_mail_token', 3000, function () {
            $r = Http::asForm()->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", [
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope'         => 'https://graph.microsoft.com/.default',
                'grant_type'    => 'client_credentials',
            ]);

            if (!$r->successful() || !$r->json('access_token')) {
                throw new TransportException('No se pudo obtener token de Microsoft Graph: ' . $r->body());
            }

            return $r->json('access_token');
        });
    }

    public function __toString(): string
    {
        return 'graph';
    }
}
