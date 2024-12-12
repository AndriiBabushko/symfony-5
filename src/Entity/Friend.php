<?php

namespace App\Entity;

use App\Repository\FriendRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FriendRepository::class)]
#[ORM\Table(name: 'friends')]
class Friend
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "User cannot be null.")]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Friend cannot be null.")]
    private ?User $friend = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank(message: "Status should not be blank.")]
    #[Assert\NotNull(message: "Status cannot be null.")]
    #[Assert\Choice(
        choices: ["pending", "accepted", "declined"],
        message: "Status must be one of 'pending', 'accepted', or 'declined'."
    )]
    #[Assert\Length(
        max: 20,
        maxMessage: "Status cannot exceed {{ limit }} characters."
    )]
    private ?string $status = null;

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

    public function getFriend(): ?User
    {
        return $this->friend;
    }

    public function setFriend(?User $friend): self
    {
        $this->friend = $friend;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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
