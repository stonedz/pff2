<?php

namespace pff\modules;

/**
 * Helper module to send mail
 *
 * @author marco.sangiorgi<at>neropaco.net
 */

use pff\Abs\AModule;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class Mail extends AModule
{
    private MailerInterface $mailer;

    private TransportInterface $transport;

    private Email $message;

    public function __construct($confFile = 'mail/module.conf.yaml', ?MailerInterface $mailer = null)
    {
        $this->loadConfig($this->readConfig($confFile));

        $this->mailer = $mailer ?? new Mailer($this->transport);
    }

    /**
     * Parse the configuration file
     *
     * @param array|null $parsedConfig
     */
    private function loadConfig($parsedConfig)
    {
        $moduleConf = [];
        if (is_array($parsedConfig) && isset($parsedConfig['moduleConf']) && is_array($parsedConfig['moduleConf'])) {
            $moduleConf = $parsedConfig['moduleConf'];
        }

        $this->transport = Transport::fromDsn($this->buildDsnFromConfig($moduleConf));
    }

    public function sendMail($to, $from, $fromName, $subject, $body, $addressReply = null, $attachment = null, $attachment_name = 'attachment.pdf', $cc = null, $bcc = null, $attachment_type = 'application/pdf')
    {
        $toAddresses = $this->normalizeAddresses($to);

        $this->message = new Email();
        $this->message->to(...$toAddresses);
        $this->message->from(new Address((string)$from, (string)$fromName));
        $this->message->subject((string)$subject);
        $this->message->html((string)$body, 'utf-8');

        if (null !== $addressReply) {
            $replyToAddresses = $this->normalizeAddresses($addressReply);
            if (count($replyToAddresses) > 0) {
                $this->message->replyTo(...$replyToAddresses);
            }
        }
        if (null !== $attachment) {
            $this->message->attach($attachment, (string)$attachment_name, (string)$attachment_type);
        }
        if (null !== $cc) {
            $ccAddresses = $this->normalizeAddresses($cc);
            if (count($ccAddresses) > 0) {
                $this->message->cc(...$ccAddresses);
            }
        }
        if (null !== $bcc) {
            $bccAddresses = $this->normalizeAddresses($bcc);
            if (count($bccAddresses) > 0) {
                $this->message->bcc(...$bccAddresses);
            }
        }

        try {
            $this->mailer->send($this->message);
        } catch (TransportExceptionInterface $e) {
            return 0;
        }

        return count($toAddresses);
    }

    /**
     * @param array<string, mixed> $moduleConf
     */
    private function buildDsnFromConfig(array $moduleConf): string
    {
        $type = strtolower(trim((string)($moduleConf['Type'] ?? '')));

        if ($type === 'smtp') {
            $host = (string)($moduleConf['Host'] ?? 'localhost');
            if ($host === '') {
                $host = 'localhost';
            }

            $port = 25;
            if (isset($moduleConf['Port']) && $moduleConf['Port'] !== '') {
                $port = (int)$moduleConf['Port'];
            }

            $username = (string)($moduleConf['Username'] ?? '');
            $password = (string)($moduleConf['Password'] ?? '');
            $encryption = strtolower(trim((string)($moduleConf['Encryption'] ?? '')));

            $scheme = ($encryption === 'ssl') ? 'smtps' : 'smtp';
            $auth = '';
            if ($username !== '') {
                $auth = rawurlencode($username);
                if ($password !== '') {
                    $auth .= ':' . rawurlencode($password);
                }
                $auth .= '@';
            }

            $dsn = $scheme . '://' . $auth . $host . ':' . $port;

            if ($encryption === 'tls') {
                $dsn .= '?encryption=tls';
            }

            return $dsn;
        }

        if ($type === 'sendmail') {
            return 'sendmail://default?command=' . rawurlencode('/usr/sbin/sendmail -bs');
        }

        return 'native://default';
    }

    /**
     * @param mixed $addresses
     * @return Address[]
     */
    private function normalizeAddresses($addresses): array
    {
        if ($addresses instanceof Address) {
            return [$addresses];
        }

        if (is_string($addresses)) {
            $trimmedAddress = trim($addresses);
            return $trimmedAddress !== '' ? [new Address($trimmedAddress)] : [];
        }

        if (!is_array($addresses)) {
            return [];
        }

        $normalized = [];
        foreach ($addresses as $key => $value) {
            if (is_int($key)) {
                if ($value instanceof Address) {
                    $normalized[] = $value;
                } elseif (is_string($value) && trim($value) !== '') {
                    $normalized[] = new Address(trim($value));
                }
                continue;
            }

            $email = trim((string)$key);
            if ($email === '') {
                continue;
            }

            if ($value instanceof Address) {
                $normalized[] = $value;
            } elseif ($value === null || $value === '') {
                $normalized[] = new Address($email);
            } else {
                $normalized[] = new Address($email, (string)$value);
            }
        }

        return $normalized;
    }

}
