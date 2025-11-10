<?php

namespace Sovic\Cms\Email;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Sovic\Cms\Email\Model\EmailModelInterface;
use Sovic\Cms\Entity\Email;
use Sovic\Common\Validator\EmailValidator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Service\Attribute\Required;

class EmailManager
{
    protected EntityManagerInterface $em;
    protected MailerInterface $mailer;

    #[Required]
    public function setEntityManager(EntityManagerInterface $em): void
    {
        $this->em = $em;
    }

    #[Required]
    public function setMailer(MailerInterface $mailer): void
    {
        $this->mailer = $mailer;
    }

    public function send(
        EmailModelInterface $model,
        string              $emailTo,
        ?string             $replyTo = null,
        ?string             $template = null,
    ): bool {
        if (EmailValidator::validate($emailTo) !== true) {
            throw new InvalidArgumentException('Invalid email address: ' . $emailTo);
        }

        $data = $model->getData();
        $email = $this->em
            ->getRepository(Email::class)
            ->findOneBy(['emailId' => $model->getId()->getId()]);

        if (!$email) {
            throw new InvalidArgumentException('Email not found for ID: ' . $model->getId()->getId());
        }

        $body = $email->getBody();
        $subject = $email->getSubject();
        foreach ($data as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
            $subject = str_replace('{' . $key . '}', $value, $subject);
        }
        $data['body'] = $body;
        $data['subject'] = $subject;

        $fromAddress = new Address($email->getFromEmail(), $email->getFromName());
        $message = new TemplatedEmail();
        $template = $template ?? '@CommonUi/emails/default.html.twig';
        $message->htmlTemplate($template);
        $message->context($data);
        $message->from($fromAddress);
        $message->to($emailTo);
        if ($replyTo && EmailValidator::validate($replyTo) === true) {
            $message->replyTo($replyTo);
        }
        $message->subject($email->getSubject());
        $message->html($body);

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface) {
            return false;
        }

        return true;
    }
}
