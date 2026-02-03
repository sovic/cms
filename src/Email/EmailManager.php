<?php

namespace Sovic\Cms\Email;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Sovic\Cms\Email\Model\EmailModelInterface;
use Sovic\Cms\Entity\Email;
use Sovic\Cms\Entity\EmailLog;
use Sovic\Common\Validator\EmailValidator;
use Sovic\CommonUi\Email\EmailThemeInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Service\Attribute\Required;

class EmailManager implements EmailManagerInterface
{
    protected EmailThemeInterface $emailTheme;
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

    #[Required]
    public function setTheme(EmailThemeInterface $theme): void
    {
        $this->emailTheme = $theme;
    }

    public function send(
        EmailModelInterface $model,
        string              $emailTo,
        ?string             $sender = null,
        ?string             $replyTo = null,
        ?string             $template = null,
        ?bool               $log = false,
        ?string             $transportId = null,
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

        $theme = $this->emailTheme;
        $body = $email->getBody();
        $subject = $email->getSubject();
        foreach ($data as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
            $subject = str_replace('{' . $key . '}', $value, $subject);
        }
        $data['body'] = $this->formatHtml($body);
        $data['subject'] = $subject;
        $data['theme'] = $theme->getTheme();
        $data['recipient_email'] = $emailTo;
        if (!empty($data['email_signature'])) {
            $data['email_signature'] = $theme->getFormattedFooterHtml($data['email_signature']);
        }

        $senderAddress = null;
        if ($sender && EmailValidator::validate($sender) === true) {
            $senderAddress = new Address($sender);
        }

        $fromAddress = new Address($email->getFromEmail(), $email->getFromName());
        $message = new TemplatedEmail();
        $message->text(html_entity_decode(strip_tags($body)));
        $message->htmlTemplate('@CommonUiBundle/email/default.html.twig');
        $message->context($data);
        $message->from($fromAddress);
        if ($senderAddress) {
            $message->sender($senderAddress);
        }
        $message->to($emailTo);
        if ($replyTo && EmailValidator::validate($replyTo) === true) {
            $message->replyTo($replyTo);
        }
        $message->subject($subject);
        $message->html($body);

        $error = $this->sendMessage($message, $transportId);

        if ($log) {
            $this->log($model->getId(), $message, $error);
        }

        return null === $error;
    }

    public function sendMessage(\Symfony\Component\Mime\Email $message, ?string $transportId = null): ?string
    {
        $error = null;
        try {
            if ($transportId) {
                $message->getHeaders()->addTextHeader('X-Transport', $transportId);
            }
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $e) {
            $error = 'Transport error [' . $e->getCode() . ']: ' . $e->getMessage();
        }

        return $error;
    }

    public function formatHtml(string $html): string
    {
        return $this->emailTheme->getFormattedHtml($html);
    }

    public function log(EmailIdInterface $emailId, \Symfony\Component\Mime\Email $message, ?string $error = null): void
    {
        $log = new EmailLog();
        $log->setEmailTo($message->getTo()[0]->getAddress());
        $log->setEmailName($emailId->getLabel());
        $log->setEmailId($emailId->getId());
        $log->setSubject($message->getSubject());
        $log->setError($error);

        $this->em->persist($log);
        $this->em->flush();
    }
}
