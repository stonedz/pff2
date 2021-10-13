<?php

namespace pff\modules;

/**
 * Helper module to send mail
 *
 * @author marco.sangiorgi<at>neropaco.net
 */

use pff\Abs\AModule;

require_once(ROOT . DS . 'vendor/swiftmailer/swiftmailer/lib/swift_init.php');

class Mail extends AModule
{
    private $mailer;

    private $transport;

    private $message;

    public function __construct($confFile = 'mail/module.conf.yaml')
    {
        $this->loadConfig($this->readConfig($confFile));

        $this->mailer = new \Swift_Mailer($this->transport);
    }

    /**
     * Parse the configuration file
     *
     * @param array $parsedConfig
     */
    private function loadConfig($parsedConfig)
    {
        if (isset($parsedConfig['moduleConf']['Type']) && $parsedConfig['moduleConf']['Type'] == "smtp") {
            $this->transport = \Swift_SmtpTransport::newInstance();

            if (isset($parsedConfig['moduleConf']['Host']) && $parsedConfig['moduleConf']['Host'] != "") {
                $this->transport->setHost($parsedConfig['moduleConf']['Host']);
            }

            if (isset($parsedConfig['moduleConf']['Port']) && $parsedConfig['moduleConf']['Port'] != "") {
                $this->transport->setPort($parsedConfig['moduleConf']['Port']);
            }

            if (isset($parsedConfig['moduleConf']['Username']) && $parsedConfig['moduleConf']['Username'] != "") {
                $this->transport->setUsername($parsedConfig['moduleConf']['Username']);
            }

            if (isset($parsedConfig['moduleConf']['Password']) && $parsedConfig['moduleConf']['Password'] != "") {
                $this->transport->setPassword($parsedConfig['moduleConf']['Password']);
            }

            if (isset($parsedConfig['moduleConf']['Encryption']) && $parsedConfig['moduleConf']['Encryption'] != "") {
                $this->transport->setEncryption($parsedConfig['moduleConf']['Encryption']);
            }
        } elseif (isset($parsedConfig['moduleConf']['Type']) && $parsedConfig['moduleConf']['Type'] == "sendmail") {
            $this->transport = \Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
        } else {
            $this->transport = \Swift_MailTransport::newInstance();
        }
    }

    public function sendMail($to, $from, $fromName, $subject, $body, $addressReply = null, $attachment = null, $attachment_name = 'attachment.pdf', $cc = null, $bcc = null, $attachment_type = 'application/pdf')
    {
        $this->message = new \Swift_Message();
        $this->message->setTo($to);
        $this->message->setFrom([$from => $fromName]);
        $this->message->setSubject($subject);
        $this->message->setBody($body);
        $this->message->setCharset("UTF-8");
        $this->message->setContentType("text/html");
        if (null !== $addressReply) {
            $this->message->setReplyTo($addressReply);
        }
        if (null !== $attachment) {
            $attachment = \Swift_Attachment::newInstance($attachment, $attachment_name, 'application/pdf');
            $attachment = \Swift_Attachment::newInstance($attachment, $attachment_name, $attachment_type);
            $this->message->attach($attachment);
        }
        if (null !== $cc) {
            $this->message->setCc($cc);
        }
        if (null !== $bcc) {
            $this->message->setBcc($bcc);
        }
        return $this->mailer->send($this->message);
    }

}
