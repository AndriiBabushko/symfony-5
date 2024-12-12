<?php

namespace App\Entity;

use App\Repository\InteractionRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InteractionRepository::class)]
#[ORM\Table(name: 'interactions')]
class Interaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "User must be specified.")]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Interaction type should not be blank.")]
    #[Assert\NotNull(message: "Interaction type cannot be null.")]
    #[Assert\Choice(
        choices: ["share", "follow"],
        message: "Interaction type must be one of 'share' or 'follow'."
    )]
    #[Assert\Length(
        max: 50,
        maxMessage: "Interaction type cannot exceed {{ limit }} characters."
    )]
    private ?string $interactionType = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(
        type: "integer",
        message: "Target ID must be a valid integer."
    )]
    private ?int $targetId = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: "CreatedAt cannot be null.")]
    #[Assert\Type(DateTimeInterface::class, message: "CreatedAt must be a valid datetime.")]
    private ?DateTimeInterface $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getInteractionType(): ?string
    {
        return $this->interactionType;
    }

    public function setInteractionType(string $interactionType): self
    {
        $this->interactionType = $interactionType;

        return $this;
    }

    public function getTargetId(): ?int
    {
        return $this->targetId;
    }

    public function setTargetId(?int $targetId): self
    {
        $this->targetId = $targetId;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
