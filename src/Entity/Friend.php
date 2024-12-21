<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post as ApiPost;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\FriendRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FriendRepository::class)]
#[ORM\Table(name: 'friends')]
#[ApiResource(
    operations: [
        new GetCollection(
            paginationEnabled: true,
            paginationItemsPerPage: 10,
            paginationClientItemsPerPage: true
        ),
        new ApiPost(),
        new Get(),
        new Patch(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['friend:read']],
    denormalizationContext: ['groups' => ['friend:write']]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'user.username' => 'partial',
    'friend.username' => 'partial',
    'status' => 'exact'
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt'], arguments: ['orderParameterName' => 'order'])]
class Friend
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['friend:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "User cannot be null.")]
    #[Groups(['friend:read', 'friend:write'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Friend cannot be null.")]
    #[Groups(['friend:read', 'friend:write'])]
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
    #[Groups(['friend:read', 'friend:write'])]
    private ?string $status = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: "CreatedAt cannot be null.")]
    #[Assert\Type(DateTimeInterface::class, message: "CreatedAt must be a valid datetime.")]
    #[Groups(['friend:read'])]
    private ?DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

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
