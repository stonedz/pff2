<?php

declare(strict_types=1);

namespace pff\modules;

/**
 * Helper module to send mail
 *
 * @author marco.sangiorgi<at>neropaco.net
 */

use pff\Abs\AModule;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class Mail extends AModule
{
    private readonly Mailer $mailer;

    private string $transportDsn;

    private ?Email $message = null;

    public function __construct(string $confFile = 'mail/module.conf.yaml')
    {
        $this->loadConfig($this->readConfig($confFile));

        $this->mailer = new Mailer(Transport::fromDsn($this->transportDsn));
    }

    /**
     * Parse the configuration file
     *
     * @param array $parsedConfig
     */
    private function loadConfig(array $parsedConfig): void
    {
        $moduleConf = isset($parsedConfig['moduleConf']) && is_array($parsedConfig['moduleConf']) ? $parsedConfig['moduleConf'] : [];

        $type = strtolower((string) ($this->getConfigValue($moduleConf, 'Type', 'smtp')));
        if ($type === 'smtp') {
            $host = (string) $this->getConfigValue($moduleConf, 'Host', '127.0.0.1');
            $port = (int) $this->getConfigValue($moduleConf, 'Port', 25);
            $username = (string) $this->getConfigValue($moduleConf, 'Username', '');
            $password = (string) $this->getConfigValue($moduleConf, 'Password', '');
            $encryption = strtolower((string) $this->getConfigValue($moduleConf, 'Encryption', ''));

            if ($encryption === 'tls' || $encryption === 'ssl') {
                $scheme = 'smtps';
            } else {
                $scheme = 'smtp';
            }

            if ($username !== '' && $password !== '') {
                $userInfo = rawurlencode($username) . ':' . rawurlencode($password) . '@';
            } else {
                $userInfo = '';
            }

            $this->transportDsn = $scheme . '://' . $userInfo . $host . ':' . $port;
        } elseif ($type === 'sendmail') {
            $this->transportDsn = 'sendmail://default';
        } else {
            $this->transportDsn = 'native://default';
        }
    }

    /**
     * @param array<string, mixed> $conf
     * @param string $key
     * @return mixed
     */
    private function getConfigValue(array $conf, string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $conf)) {
            return $conf[$key];
        }

        $lowerKey = strtolower($key);
        foreach ($conf as $configKey => $value) {
            if (strtolower((string) $configKey) === $lowerKey) {
                return $value;
            }
        }

        return $default;
    }

    public function sendMail(string|array $to, string $from, string $fromName, string $subject, string $body, ?string $addressReply = null, mixed $attachment = null, string $attachment_name = 'attachment.pdf', string|array|null $cc = null, string|array|null $bcc = null, string $attachment_type = 'application/pdf'): bool
    {
        $this->message = (new Email())
            ->subject((string) $subject)
            ->from(new Address((string) $from, (string) $fromName))
            ->html((string) $body);

        if (is_array($to)) {
            foreach ($to as $recipient) {
                $this->message->addTo((string) $recipient);
            }
        } else {
            $this->message->to((string) $to);
        }

        if (null !== $addressReply) {
            $this->message->replyTo((string) $addressReply);
        }

        if (null !== $attachment) {
            if (is_string($attachment) && file_exists($attachment)) {
                $this->message->addPart(DataPart::fromPath($attachment, (string) $attachment_name, (string) $attachment_type));
            } else {
                $this->message->addPart(new DataPart($attachment, (string) $attachment_name, (string) $attachment_type));
            }
        }

        if (null !== $cc) {
            if (is_array($cc)) {
                foreach ($cc as $email) {
                    $this->message->addCc((string) $email);
                }
            } else {
                $this->message->cc((string) $cc);
            }
        }

        if (null !== $bcc) {
            if (is_array($bcc)) {
                foreach ($bcc as $email) {
                    $this->message->addBcc((string) $email);
                }
            } else {
                $this->message->bcc((string) $bcc);
            }
        }

        try {
            $this->mailer->send($this->message);
            return true;
        } catch (TransportExceptionInterface) {
            return false;
        }
    }

}
