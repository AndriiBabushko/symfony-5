<?php

namespace App\Entity;

use App\Repository\GroupMemberRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GroupMemberRepository::class)]
#[ORM\Table(name: 'group_members')]
class GroupMember
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Group must be specified.")]
    private ?Group $group = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "User must be specified.")]
    private ?User $user = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Role should not be blank.")]
    #[Assert\NotNull(message: "Role cannot be null.")]
    #[Assert\Choice(
        choices: ["admin", "member"],
        message: "Role must be one of 'admin' or 'member'."
    )]
    #[Assert\Length(
        max: 20,
        maxMessage: "Role cannot exceed {{ limit }} characters."
    )]
    private ?string $role = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: "JoinedAt cannot be null.")]
    #[Assert\Type(DateTimeInterface::class, message: "JoinedAt must be a valid datetime.")]
    private ?DateTimeInterface $joinedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): self
    {
        $this->group = $group;

        return $this;
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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getJoinedAt(): ?DateTimeInterface
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(DateTimeInterface $joinedAt): self
    {
        $this->joinedAt = $joinedAt;

        return $this;
    }
}
