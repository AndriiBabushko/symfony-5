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
use App\Action\Post\RemovePostCommentsAction;
use App\Action\Post\SetPostPublishedAction;
use App\Repository\PostRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'posts')]
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
        new Delete(),
        new ApiPost(
            uriTemplate: '/posts/publish',
            controller: SetPostPublishedAction::class,
            denormalizationContext: ['groups' => ['post:write']],
            read: false,
            write: true,
            name: 'set_post_published'
        ),
        new Delete(
            uriTemplate: '/posts/comments/remove/{id}',
            requirements: ['id' => '\d+'],
            controller: RemovePostCommentsAction::class,
            name: 'remove_post_comments'
        )
    ],
    normalizationContext: ['groups' => ['post:read']],
    denormalizationContext: ['groups' => ['post:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['content' => 'partial', 'user.username' => 'partial', 'status' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'], arguments: ['orderParameterName' => 'order'])]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['post:read', 'reaction:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Content should not be blank.")]
    #[Assert\NotNull(message: "Content cannot be null.")]
    #[Assert\Length(
        min: 1,
        max: 5000,
        minMessage: "Content must be at least {{ limit }} character long.",
        maxMessage: "Content cannot exceed {{ limit }} characters."
    )]
    #[Groups(['post:read', 'post:write'])]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "ImageUrl must be a valid URL.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "ImageUrl cannot exceed {{ limit }} characters."
    )]
    #[Groups(['post:read', 'post:write'])]
    private ?string $imageUrl = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "CreatedAt cannot be null.")]
    #[Assert\Type(\DateTimeInterface::class, message: "CreatedAt must be a valid datetime.")]
    #[Groups(['post:read'])]
    private ?\DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\Type(\DateTimeInterface::class, message: "UpdatedAt must be a valid datetime.")]
    #[Groups(['post:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "User must be specified.")]
    #[Groups(['post:read', 'post:write'])]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Status should not be blank.")]
    #[Assert\NotNull(message: "Status cannot be null.")]
    #[Assert\Choice(
        choices: ["draft", "published", "archived"],
        message: "Status must be one of 'draft', 'published', or 'archived'."
    )]
    #[Assert\Length(
        max: 50,
        maxMessage: "Status cannot exceed {{ limit }} characters."
    )]
    #[Groups(['post:read', 'post:write'])]
    private ?string $status = null;

    #[ORM\OneToMany(targetEntity: Reaction::class, mappedBy: 'post', cascade: ['persist', 'remove'])]
    #[Groups(['post:read'])]
    private Collection $reactions;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', cascade: ['persist', 'remove'])]
    #[Groups(['post:read'])]
    private Collection $comments;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->reactions = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Reaction>
     */
    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    public function addReaction(Reaction $reaction): self
    {
        if (!$this->reactions->contains($reaction)) {
            $this->reactions->add($reaction);
            $reaction->setPost($this);
        }

        return $this;
    }

    public function removeReaction(Reaction $reaction): self
    {
        if ($this->reactions->removeElement($reaction)) {
            if ($reaction->getPost() === $this) {
                $reaction->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }
}