<?php

namespace Sovic\Cms\User;

use DateTime;
use DateTimeImmutable;
use Sovic\Cms\ORM\AbstractEntityModel;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;

/**
 * @method \Sovic\Cms\Entity\User getEntity()
 */
class User extends AbstractEntityModel
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public function getId(): int
    {
        return $this->getEntity()->getId();
    }

    public function generateActivationCode(): void
    {
        $entity = $this->getEntity();
        $activationCode = md5($entity->getEmail() . time() . '6SaNbYhF7bXGs8FbHTci');
        $entity->setActivationCode($activationCode);
        $this->flush();
    }

    public function activate(): void
    {
        $entity = $this->getEntity();
        $entity->setIsActive(true);
        $entity->setActivatedDate(new DateTimeImmutable());
        $entity->setActivationCode(null);
        $this->flush();
    }

    public function generateForgotPasswordCode(): void
    {
        $entity = $this->getEntity();
        $forgotPasswordCode = md5($entity->getEmail() . time() . '6SaNbYhF7bXGs8FbHTci');
        $entity->setForgotPasswordCode($forgotPasswordCode);
        $this->flush();
    }

    public function getRegistrationEmail(?string $password = null): Email
    {
        $this->generateActivationCode();
        $entity = $this->getEntity();
        $createdDate = DateTime::createFromImmutable($entity->getCreatedDate());

        return (new TemplatedEmail())
            ->addTo($entity->getEmail())
            ->htmlTemplate('emails/signup.html.twig')
            ->subject($this->translator->trans('user.sign_up.email_subject'))
            ->context([
                'activation_code' => !$entity->isIsActive() ? $entity->getActivationCode() : null,
                'expiration_date' => $createdDate->modify('+7 days'),
                'password' => $password,
                'subject' => $this->translator->trans('user.sign_up.email_subject'),
            ]);
    }

    public function getForgotPasswordEmail(): Email
    {
        $this->generateForgotPasswordCode();
        $entity = $this->getEntity();

        return (new TemplatedEmail())
            ->addTo($entity->getEmail())
            ->htmlTemplate('emails/forgot-password.html.twig')
            ->subject($this->translator->trans('user.forgot_password.email_subject'))
            ->context([
                'forgot_password_code' => $entity->getForgotPasswordCode(),
                'subject' => $this->translator->trans('user.forgot_password.email_subject'),
            ]);
    }
}
