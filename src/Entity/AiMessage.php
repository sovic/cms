<?php

namespace Sovic\Cms\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Sovic\Cms\Ai\Enum\AiMessageRole;
use Sovic\Cms\Repository\AiMessageRepository;
use Sovic\Common\Entity\Trait\CreatedAtTrait;
use Sovic\Common\Entity\Trait\IdentityColumnTrait;

#[Table(name: 'ai_message')]
#[Index(name: 'conversation_id', columns: ['conversation_id'])]
#[Entity(repositoryClass: AiMessageRepository::class)]
class AiMessage
{
    use CreatedAtTrait;
    use IdentityColumnTrait;

    #[ManyToOne(targetEntity: AiConversation::class)]
    #[JoinColumn(name: 'conversation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private AiConversation $conversation;

    #[Column(name: 'role', type: Types::STRING, length: 16, enumType: AiMessageRole::class)]
    private AiMessageRole $role;

    #[Column(name: 'content', type: Types::TEXT, length: 4294967295)]
    private string $content;

    #[Column(name: 'html', type: Types::TEXT, length: 4294967295, nullable: true, options: ['default' => null])]
    private ?string $html = null;

    #[Column(name: 'input_tokens', type: Types::INTEGER, options: ['default' => 0])]
    private int $inputTokens = 0;

    #[Column(name: 'output_tokens', type: Types::INTEGER, options: ['default' => 0])]
    private int $outputTokens = 0;

    public function __construct(AiConversation $conversation, AiMessageRole $role, string $content, ?string $html = null)
    {
        $this->conversation = $conversation;
        $this->role = $role;
        $this->content = $content;
        $this->html = $html;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getConversation(): AiConversation
    {
        return $this->conversation;
    }

    public function getRole(): AiMessageRole
    {
        return $this->role;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function getInputTokens(): int
    {
        return $this->inputTokens;
    }

    public function setInputTokens(int $inputTokens): void
    {
        $this->inputTokens = $inputTokens;
    }

    public function getOutputTokens(): int
    {
        return $this->outputTokens;
    }

    public function setOutputTokens(int $outputTokens): void
    {
        $this->outputTokens = $outputTokens;
    }
}
