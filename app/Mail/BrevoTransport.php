<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

/**
 * Envía correos con la API HTTPS de Brevo (plan gratuito: 300 al día).
 *
 * Los hostings gratuitos como Render bloquean los puertos SMTP (25, 465 y
 * 587); por HTTPS (443) el correo sale sin problema y sin paquetes extra.
 */
class BrevoTransport extends AbstractTransport
{
    private const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    public function __construct(private readonly string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $remitente = $email->getFrom()[0] ?? null;

        $payload = array_filter([
            'sender' => $remitente ? $this->direccion($remitente) : null,
            'to' => array_map([$this, 'direccion'], $email->getTo()),
            'cc' => array_map([$this, 'direccion'], $email->getCc()) ?: null,
            'bcc' => array_map([$this, 'direccion'], $email->getBcc()) ?: null,
            'replyTo' => ($email->getReplyTo()[0] ?? null) ? $this->direccion($email->getReplyTo()[0]) : null,
            'subject' => $email->getSubject(),
            'htmlContent' => $email->getHtmlBody(),
            'textContent' => $email->getTextBody(),
        ], fn ($valor) => $valor !== null && $valor !== '');

        $respuesta = Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
        ])->timeout(10)->post(self::ENDPOINT, $payload);

        if ($respuesta->failed()) {
            throw new TransportException('Brevo respondió '.$respuesta->status().': '.$respuesta->body());
        }
    }

    /** @return array{email: string, name?: string} */
    private function direccion(Address $direccion): array
    {
        return array_filter([
            'email' => $direccion->getAddress(),
            'name' => $direccion->getName() ?: null,
        ]);
    }

    public function __toString(): string
    {
        return 'brevo+api';
    }
}
