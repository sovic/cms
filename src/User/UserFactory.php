<?php

namespace SovicCms\User;

use SovicCms\Entity\User as UserEntity;
use SovicCms\ORM\EntityModelFactory;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserFactory extends EntityModelFactory
{
    protected MailerInterface $mailer;
    protected UserPasswordHasherInterface $passwordHasher;

    #[Pure]
    public function __construct(
        EntityManagerInterface      $entityManager,
        MailerInterface             $mailer,
        TranslatorInterface         $translator,
        RouterInterface             $router,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct($entityManager, $router, $translator);
        $this->mailer = $mailer;
        $this->passwordHasher = $passwordHasher;
    }

    public function loadByAuthUser(UserInterface $user): User
    {
        // our user entity used for authentication implements UserInterface, simply load model
        $user = $this->loadEntityModel($user, User::class);
        if (null === $user) {
            throw new RuntimeException('Unable to load user model');
        }

        return $user;
    }

    public function loadByEmail(string $email): ?User
    {
        return $this->loadModelBy(
            UserEntity::class,
            User::class,
            [
                'email' => $email,
            ]
        );
    }

    public function loadByUsername(string $username): ?User
    {
        return $this->loadModelBy(
            UserEntity::class,
            User::class,
            [
                'username' => $username,
            ]
        );
    }

    public function loadByActivationCode(string $code): ?User
    {
        /** @var User $user */
        $user = $this->loadModelBy(
            UserEntity::class,
            User::class,
            [
                'activationCode' => $code,
            ]
        );
        if (null === $user) {
            return null;
        }
        $createdDate = DateTime::createFromImmutable($user->getEntity()->getCreatedDate());
        $now = new DateTime();
        if ($now > $createdDate->modify('+7 days')) {
            return null;
        }

        return $user;
    }

    public function loadByForgotPasswordCode(string $code): ?User
    {
        /** @var User $user */
        $user = $this->loadModelBy(
            UserEntity::class,
            User::class,
            [
                'forgotPasswordCode' => $code,
            ]
        );

        return $user ?? null;
    }

    public function createNew(string $email, string $password): User
    {
        $user = new UserEntity();
        $user->setEmail($email);
        $user->setUsername(explode('@', $email, 2)[0]);
        $user->setCreatedDate(new DateTimeImmutable());
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        return $this->loadEntityModel($user, User::class);
    }
}
