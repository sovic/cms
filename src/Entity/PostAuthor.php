<?php

namespace SovicCms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SovicCms\Entity\Tag
 *
 * @ORM\Table(name="posts_authors")
 * @ORM\Entity
 */
class PostAuthor
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\Column(name="posts_id", type="integer")
     */
    protected int $postId;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected string $name;

    /**
     * @ORM\Column(name="short_description", type="string", length=1000, nullable=true)
     */
    protected ?string $shortDescription;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }
}
