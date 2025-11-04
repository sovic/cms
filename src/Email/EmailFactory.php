<?php

namespace Sovic\Cms\Email;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Sovic\Cms\Entity\Email;
use Sovic\Cms\Repository\EmailRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Symfony\Contracts\Service\Attribute\Required;

class EmailFactory
{
    private EntityManagerInterface $em;
    private ?string $defaultFrom;
    private ?string $defaultReplyTo;

    public function __construct()
    {
        $this->defaultFrom = $_ENV['MAILER_DEFAULT_FROM'] ?? null;
        $this->defaultReplyTo = $_ENV['MAILER_DEFAULT_REPLY_TO'] ?? null;
    }

    #[Required]
    public function setEntityManager(EntityManagerInterface $em): void
    {
        $this->em = $em;
    }

    public function getMessage(EmailIdInterface $emailId, array $context = []): Message
    {
        /** @var EmailRepository $repo */
        $repo = $this->em->getRepository(Email::class);
        $email = $repo->findByEmailId($emailId);

        if ($email === null) {
            throw new RuntimeException('Email not found: ' . $emailId->getId());
        }

        $body = $email->getBody();
        $subject = $email->getSubject();

        $variables = $emailId->getVariables();
        foreach ($variables as $variable) {
            $body = str_replace('{' . $variable . '}', $context[$variable] ?? '', $body);
        }

        $context['body'] = $body;
        $context['subject'] = $subject;

        $message = (new TemplatedEmail());
        $message->htmlTemplate('emails/default.html.twig');
        $message->context($context);
        $message->subject($subject);

        $from = $email->getFromEmail() ?: $this->defaultFrom;
        $fromName = $email->getFromName() ?: '';
        $address = new Address($from, $fromName);
        $message->from($address);

        $replyTo = $this->defaultReplyTo;
        if ($replyTo) {
            $message->replyTo($replyTo);
        }

        return $message;
    }
}
