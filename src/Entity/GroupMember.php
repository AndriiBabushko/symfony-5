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
use App\Repository\GroupMemberRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GroupMemberRepository::class)]
#[ORM\Table(name: 'group_members')]
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
    normalizationContext: ['groups' => ['group_member:read']],
    denormalizationContext: ['groups' => ['group_member:write']]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'group.id' => 'exact',
    'user.username' => 'partial',
    'role' => 'exact'
])]
#[ApiFilter(OrderFilter::class, properties: ['joinedAt'], arguments: ['orderParameterName' => 'order'])]
class GroupMember
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['group_member:read', 'group:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Group must be specified.")]
    #[Groups(['group_member:read', 'group_member:write'])]
    private ?Group $group = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "User must be specified.")]
    #[Groups(['group_member:read', 'group_member:write'])]
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
    #[Groups(['group_member:read', 'group_member:write'])]
    private ?string $role = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: "JoinedAt cannot be null.")]
    #[Assert\Type(DateTimeInterface::class, message: "JoinedAt must be a valid datetime.")]
    #[Groups(['group_member:read'])]
    private ?DateTimeInterface $joinedAt = null;

    public function __construct()
    {
        $this->joinedAt = new DateTime();
    }

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
