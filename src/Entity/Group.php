<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: 'groups')]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Name should not be blank.")]
    #[Assert\NotNull(message: "Name cannot be null.")]
    #[Assert\Length(
        min: 1,
        max: 100,
        minMessage: "Name must be at least {{ limit }} character long.",
        maxMessage: "Name cannot exceed {{ limit }} characters."
    )]
    private ?string $name = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Description should not be blank.")]
    #[Assert\NotNull(message: "Description cannot be null.")]
    #[Assert\Length(
        max: 1000,
        maxMessage: "Description cannot exceed {{ limit }} characters."
    )]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Creator must be specified.")]
    private ?User $creator = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: "CreatedAt cannot be null.")]
    #[Assert\Type(DateTimeInterface::class, message: "CreatedAt must be a valid datetime.")]
    private ?DateTimeInterface $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): self
    {
        $this->creator = $creator;

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
