<?php

namespace Sovic\Cms\Email;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Sovic\Cms\Email\Model\EmailModelInterface;
use Sovic\Cms\Entity\Email;
use Sovic\Cms\Entity\EmailLog;
use Sovic\Common\Project\Settings;
use Sovic\Common\Validator\EmailValidator;
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
    protected Settings $settings;

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

    #[Required]
    public function setSettings(Settings $settings): void
    {
        $this->settings = $settings;
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

        $message = $this->buildMessage($email, $data, $emailTo, $sender, $replyTo);
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

    public function sendTest(Email $email, string $emailTo): bool
    {
        $message = $this->buildMessage($email, [], $emailTo);
        $message->subject('[TEST] ' . $email->getSubject());

        $error = $this->sendMessage($message);

        return null === $error;
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

    private function buildMessage(
        Email   $email,
        array   $data,
        string  $emailTo,
        ?string $sender = null,
        ?string $replyTo = null,
    ): TemplatedEmail {
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

        $emailSignature = $this->settings->get('mailer', 'email_signature');
        if (!empty($emailSignature)) {
            $data['email_signature'] = $theme->getFormattedFooterHtml($emailSignature);
        }

        $senderAddress = null;
        if ($sender && EmailValidator::validate($sender) === true) {
            $senderAddress = new Address($sender);
        }

        $fromEmail = $email->getFromEmail() ?? $emailTo;
        $fromAddress = new Address($fromEmail, $email->getFromName() ?? '');
        $message = new TemplatedEmail();
        $message->text(html_entity_decode(strip_tags($body)));
        $message->htmlTemplate('@CmsBundle/email/default.html.twig');
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

        return $message;
    }
}
