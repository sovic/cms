<?php

namespace Sovic\Cms\Entity;

use Sovic\Cms\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=180, nullable=true, options={"default": NULL})
     */
    private ?string $username = null;

    /**
     * @ORM\Column()
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\Column(type="string", length=180, unique=true, nullable=false)
     */
    private string $email;

    /**
     * @ORM\Column(name="created_date", type="datetime_immutable", nullable=false)
     */
    private DateTimeImmutable $createdDate;

    /**
     * @ORM\Column(name="active", type="smallint", nullable=false, options={"default"=0})
     */
    private bool $active = false;

    /**
     * @ORM\Column(name="activated_date", type="datetime_immutable", nullable=true, options={"default": NULL})
     */
    private ?DateTimeImmutable $activatedDate;

    /**
     * @ORM\Column(
     *     name="activation_code",
     *     type="string",
     *     length=32,
     *     unique=true,
     *     nullable=true,
     *     options={"default": null}
     * )
     */
    private ?string $activationCode;

    /**
     * @ORM\Column(name="country_code", type="string", length=2, nullable=true, options={"default": NULL})
     */
    private ?string $countryCode;

    /**
     * @ORM\Column(name="default_currency", type="string", length=3, nullable=true, options={"default": NULL})
     */
    private ?string $defaultCurrency;

    /**
     * @ORM\Column(name="geo_ip_limited", type="smallint", nullable=false, options={"default"=0})
     */
    private bool $geoIpLimited = false;

    /**
     * @ORM\Column(name="emailing", type="smallint", nullable=false, options={"default"=1})
     */
    private bool $emailing = true;

    /**
     * @ORM\Column(name="logins", type="integer", nullable=false, options={"default":0})
     */
    private int $logins = 0;

    /**
     * @ORM\Column(name="last_login_date", type="datetime_immutable", nullable=true, options={"default": NULL})
     */
    private ?DateTimeImmutable $lastLoginDate;

    /**
     * @ORM\Column(
     *     name="forgot_password_code",
     *     type="string",
     *     length=32,
     *     unique=true,
     *     nullable=true,
     *     options={"default": NULL}
     * )
     */
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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
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

    public function isGeoIpLimited(): bool
    {
        return $this->geoIpLimited;
    }

    public function setGeoIpLimited(bool $geoIpLimited): void
    {
        $this->geoIpLimited = $geoIpLimited;
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
