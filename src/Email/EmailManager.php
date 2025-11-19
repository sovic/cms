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

class EmailManager
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

        $themeData = $this->emailTheme->getTheme();

        // update links style
        $linkStyle = $themeData['link_style'] ?? '';
        $data['body'] = str_replace('<a ', '<a style="' . $linkStyle . '" ', $data['body']);
        // update paragraph style
        $paragraphStyle = $themeData['paragraph_style'] ?? '';
        $data['body'] = str_replace('<p>', '<p style="' . $paragraphStyle . '">', $data['body']);

        $senderAddress = null;
        if ($sender && EmailValidator::validate($sender) === true) {
            $senderAddress = new Address($sender);
        }

        $fromAddress = new Address($email->getFromEmail(), $email->getFromName());
        $message = new TemplatedEmail();
        $template = $template ?? '@CommonUiBundle/email/default.html.twig';
        $message->htmlTemplate($template);
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

        $sent = false;
        $error = null;
        try {
            $this->mailer->send($message);
            $sent = true;
        } catch (TransportExceptionInterface $e) {
            $error = 'Transport error [' . $e->getCode() . ']: ' . $e->getMessage();
        }
        if ($log) {
            $this->log($model->getId(), $message, $error);
        }

        return $sent;
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
