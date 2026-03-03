<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;

class MailTest extends TestCase
{
    public function testSendMailBuildsMessageAndReturnsToRecipientCount()
    {
        $transport = new TestInMemoryTransport();
        $mailer = new Mailer($transport);
        $module = new \pff\modules\Mail('mail/module.conf.yaml', $mailer);

        $recipientCount = $module->sendMail(
            ['alice@example.com' => 'Alice', 'bob@example.com' => 'Bob'],
            'sender@example.com',
            'Sender Name',
            'Test Subject',
            '<p>HTML body</p>',
            'reply@example.com',
            'BINARY-PAYLOAD',
            'document.pdf',
            ['cc1@example.com', 'cc2@example.com' => 'CC Name'],
            ['bcc@example.com'],
            'application/pdf'
        );

        $this->assertSame(2, $recipientCount);

        $sent = $transport->getSent();
        $this->assertCount(1, $sent);

        $message = $sent[0]->getOriginalMessage();
        $this->assertInstanceOf(Email::class, $message);

        /** @var Email $message */
        $this->assertSame('sender@example.com', $message->getFrom()[0]->getAddress());
        $this->assertSame('Sender Name', $message->getFrom()[0]->getName());
        $this->assertSame('reply@example.com', $message->getReplyTo()[0]->getAddress());

        $this->assertCount(2, $message->getTo());
        $this->assertSame('alice@example.com', $message->getTo()[0]->getAddress());
        $this->assertSame('Alice', $message->getTo()[0]->getName());
        $this->assertSame('bob@example.com', $message->getTo()[1]->getAddress());
        $this->assertSame('Bob', $message->getTo()[1]->getName());

        $this->assertCount(2, $message->getCc());
        $this->assertSame('cc1@example.com', $message->getCc()[0]->getAddress());
        $this->assertSame('cc2@example.com', $message->getCc()[1]->getAddress());
        $this->assertSame('CC Name', $message->getCc()[1]->getName());

        $this->assertCount(1, $message->getBcc());
        $this->assertSame('bcc@example.com', $message->getBcc()[0]->getAddress());

        $this->assertSame('<p>HTML body</p>', $message->getHtmlBody());
        $this->assertNull($message->getTextBody());

        $attachments = $message->getAttachments();
        $this->assertCount(1, $attachments);
        $this->assertSame('document.pdf', $attachments[0]->getFilename());
        $this->assertSame('application', $attachments[0]->getMediaType());
        $this->assertSame('pdf', $attachments[0]->getMediaSubtype());
        $this->assertSame('BINARY-PAYLOAD', $attachments[0]->getBody());
    }

    public function testSendMailReturnsZeroOnTransportFailure()
    {
        $failingTransport = new FailingTransport();
        $mailer = new Mailer($failingTransport);
        $module = new \pff\modules\Mail('mail/module.conf.yaml', $mailer);

        $recipientCount = $module->sendMail(
            'alice@example.com',
            'sender@example.com',
            'Sender Name',
            'Test Subject',
            '<p>HTML body</p>'
        );

        $this->assertSame(0, $recipientCount);
    }
}

/**
 * Symfony 7.4 does not include a built-in InMemoryTransport class,
 * so this small transport captures SentMessage instances for assertions.
 */
class TestInMemoryTransport extends AbstractTransport
{
    /** @var SentMessage[] */
    private array $sent = [];

    /** @return SentMessage[] */
    public function getSent(): array
    {
        return $this->sent;
    }

    public function __toString(): string
    {
        return 'test+memory://default';
    }

    protected function doSend(SentMessage $message): void
    {
        $this->sent[] = $message;
    }
}

class FailingTransport extends AbstractTransport
{
    public function __toString(): string
    {
        return 'test+failing://default';
    }

    protected function doSend(SentMessage $message): void
    {
        throw new TransportException('Simulated transport failure');
    }
}
