<?php

namespace Sovic\Cms\Email;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailManager
{
    private MailerInterface $mailer;

    /**
     * @param MailerInterface $mailer
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send(Email $email): void
    {
        // TODO add default email configuration
//        if (empty($email->getFrom())) {
//            $email->addFrom('');
//        }
//        if (empty($email->getReplyTo())) {
//            $email->addReplyTo('');
//        }

        // $message = EmailHelper::signMessage($email); // not working for now

        $this->mailer->send($email);
    }
}
