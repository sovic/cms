<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Sovic\Cms\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 180, nullable: true, options: ['default' => null])]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: Types::STRING)]
    private string $password;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true, nullable: false)]
    private string $email;

    #[ORM\Column(name: 'created_date', type: Types::DATE_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $createdDate;

    #[ORM\Column(name: 'is_active', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    private bool $isActive = false;

    #[ORM\Column(name: 'activated_date', type: Types::DATE_IMMUTABLE, nullable: true, options: ['default' => null])]
    private ?DateTimeImmutable $activatedDate;

    #[ORM\Column(name: 'activation_code', type: Types::STRING, length: 32, unique: true, nullable: true, options: ['default' => null])]
    private ?string $activationCode;

    #[ORM\Column(name: 'country_code', type: Types::STRING, length: 2, nullable: true, options: ['default' => null])]
    private ?string $countryCode;

    #[ORM\Column(name: 'currency', type: Types::STRING, length: 3, nullable: true, options: ['default' => null])]
    private ?string $defaultCurrency;

    #[ORM\Column(name: 'is_geo_ip_limited', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    private bool $isGeoIpLimited = false;

    #[ORM\Column(name: 'emailing', type: Types::BOOLEAN, nullable: false, options: ['default' => true])]
    private bool $emailing = true;

    #[ORM\Column(name: 'logins', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    private int $logins = 0;

    #[ORM\Column(name: 'last_login_date', type: Types::DATE_IMMUTABLE, nullable: true, options: ['default' => null])]
    private ?DateTimeImmutable $lastLoginDate;

    #[ORM\Column(name: 'forgot_password_code', type: Types::STRING, length: 32, unique: true, nullable: true, options: ['default' => null])]
    private ?string $forgotPasswordCode;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getCreatedDate(): DateTimeImmutable
    {
        return $this->createdDate;
    }

    public function setCreatedDate(DateTimeImmutable $createdDate): void
    {
        $this->createdDate = $createdDate;
    }

    public function isIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getActivatedDate(): ?DateTimeImmutable
    {
        return $this->activatedDate;
    }

    public function setActivatedDate(?DateTimeImmutable $activatedDate): void
    {
        $this->activatedDate = $activatedDate;
    }

    public function getActivationCode(): ?string
    {
        return $this->activationCode;
    }

    public function setActivationCode(?string $activationCode): void
    {
        $this->activationCode = $activationCode;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    public function getDefaultCurrency(): ?string
    {
        return $this->defaultCurrency;
    }

    public function setDefaultCurrency(?string $defaultCurrency): void
    {
        $this->defaultCurrency = $defaultCurrency;
    }

    public function isIsGeoIpLimited(): bool
    {
        return $this->isGeoIpLimited;
    }

    public function setIsGeoIpLimited(bool $isGeoIpLimited): void
    {
        $this->isGeoIpLimited = $isGeoIpLimited;
    }

    public function isEmailing(): bool
    {
        return $this->emailing;
    }

    public function setEmailing(bool $emailing): void
    {
        $this->emailing = $emailing;
    }

    public function getLogins(): int
    {
        return $this->logins;
    }

    public function setLogins(int $logins): void
    {
        $this->logins = $logins;
    }

    public function getLastLoginDate(): ?DateTimeImmutable
    {
        return $this->lastLoginDate;
    }

    public function setLastLoginDate(?DateTimeImmutable $lastLoginDate): void
    {
        $this->lastLoginDate = $lastLoginDate;
    }

    public function getForgotPasswordCode(): ?string
    {
        return $this->forgotPasswordCode;
    }

    public function setForgotPasswordCode(?string $forgotPasswordCode): void
    {
        $this->forgotPasswordCode = $forgotPasswordCode;
    }

    #[Pure]
    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }
}
