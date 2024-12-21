<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
//use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
//use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
//#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource(
    operations: [
        new GetCollection(
            paginationEnabled: true,
            paginationItemsPerPage: 10,
            paginationClientItemsPerPage: true
        ),
        new Post(),
        new Get(),
        new Patch(),
        new Put(),
        new Delete()
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['username' => 'partial', 'email' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'username'], arguments: ['orderParameterName' => 'order'])]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Username should not be blank.")]
    #[Assert\NotNull(message: "Username cannot be null.")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Username must be at least {{ limit }} characters long.",
        maxMessage: "Username cannot exceed {{ limit }} characters."
    )]
    private ?string $username = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: "Email should not be blank.")]
    #[Assert\NotNull(message: "Email cannot be null.")]
    #[Assert\Email(message: "The email '{{ value }}' is not a valid email.")]
    #[Assert\Length(
        max: 100,
        maxMessage: "Email cannot exceed {{ limit }} characters."
    )]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Password should not be blank.")]
    #[Assert\NotNull(message: "Password cannot be null.")]
    #[Assert\Length(
        min: 8,
        max: 255,
        minMessage: "Password must be at least {{ limit }} characters long.",
        maxMessage: "Password cannot exceed {{ limit }} characters."
    )]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "CreatedAt cannot be null.")]
    #[Assert\Type(\DateTimeInterface::class, message: "CreatedAt must be a valid datetime.")]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "ProfilePicture must be a valid URL.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "ProfilePicture cannot exceed {{ limit }} characters."
    )]
    private ?string $profilePicture = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): self
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

//    public function eraseCredentials(): void
//    {
//        // Clear sensitive data if needed
//    }
}
