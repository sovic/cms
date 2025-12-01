<?php

namespace Sovic\Cms\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Sovic\Common\Entity\Trait\IdentityColumnTrait;

#[Table(name: 'email_log')]
#[Index(name: 'email_id', columns: ['email_id'], options: ['lengths' => [191]])]
#[Index(name: 'email_to', columns: ['email_to'], options: ['lengths' => [191]])]
#[Index(name: 'send_date', columns: ['send_date'])]
#[Entity]
class EmailLog
{
    use IdentityColumnTrait;

    #[Column(name: 'send_date', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $createdAt;

    #[Column(name: 'email_id', type: Types::STRING, length: 255, nullable: false)]
    private string $emailId;

    #[Column(name: 'email_name', type: Types::STRING, length: 255, nullable: false)]
    private string $emailName;

    #[Column(name: 'email_to', type: Types::STRING, length: 255, nullable: false)]
    private string $emailTo;

    #[Column(name: 'subject', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    private ?string $subject = null;

    /**
     * @var resource
     */
    #[Column(name: 'data', type: Types::BLOB, length: 16777215, nullable: true, options: ['default' => null])]
    private $data;

    #[Column(name: 'error', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    private ?string $error = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return resource
     */
    public function getData()
    {
        return $this->data;
    }

    public function setData(?string $data): void
    {
        $this->data = $data;
    }

    public function getEmailId(): string
    {
        return $this->emailId;
    }

    public function setEmailId(string $emailId): void
    {
        $this->emailId = $emailId;
    }

    public function getEmailTo(): string
    {
        return $this->emailTo;
    }

    public function setEmailTo(string $emailTo): void
    {
        $this->emailTo = $emailTo;
    }

    public function getEmailName(): string
    {
        return $this->emailName;
    }

    public function setEmailName(string $emailName): void
    {
        $this->emailName = $emailName;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): void
    {
        $this->error = $error;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }
}
