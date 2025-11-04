<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sovic\Cms\Repository\EmailRepository;
use Sovic\Common\Entity\Trait\CreatedAtTrait;
use Sovic\Common\Entity\Trait\IdentityColumnTrait;

#[Table(name: 'email')]
#[Entity(repositoryClass: EmailRepository::class)]
class Email
{
    use IdentityColumnTrait;
    use CreatedAtTrait;

    #[Column(name: 'name', type: Types::STRING, length: 200, nullable: false)]
    private string $name;

    #[Column(name: 'description', type: Types::TEXT, length: 65535, nullable: true, options: ['default' => null])]
    private ?string $description = null;

    #[Column(name: 'subject', type: Types::STRING, length: 200, nullable: false)]
    private string $subject;

    #[Column(name: 'body', type: Types::TEXT, length: 16777215, nullable: false)]
    private string $body;

    #[Column(name: 'from_email', type: Types::STRING, length: 150, nullable: true, options: ['default' => null])]
    private ?string $fromEmail = null;

    #[Column(name: 'from_name', type: Types::STRING, length: 150, nullable: true, options: ['default' => null])]
    private ?string $fromName = null;

    #[Column(name: 'lang', type: Types::STRING, length: 5, nullable: true, options: ['default' => 'cs'])]
    private ?string $lang = 'cs';

    #[Column(
        name: 'email_id',
        type: Types::STRING,
        length: 100,
        nullable: true,
        options: ['default' => null]
    )]
    private ?string $emailId = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(?string $fromEmail): void
    {
        $this->fromEmail = $fromEmail;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function setFromName(?string $fromName): void
    {
        $this->fromName = $fromName;
    }

    public function getEmailId(): ?string
    {
        return $this->emailId;
    }

    public function setEmailId(?string $emailId): void
    {
        $this->emailId = $emailId;
    }
}
