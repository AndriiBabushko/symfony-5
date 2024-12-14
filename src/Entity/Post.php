<?php
//
//namespace App\Entity;
//
//use App\Repository\PostRepository;
//use Doctrine\DBAL\Types\Types;
//use Doctrine\ORM\Mapping as ORM;
//use Symfony\Component\Validator\Constraints as Assert;
//
//#[ORM\Entity(repositoryClass: PostRepository::class)]
//#[ORM\Table(name: 'posts')]
//class Post
//{
//    #[ORM\Id]
//    #[ORM\GeneratedValue]
//    #[ORM\Column]
//    private ?int $id = null;
//
//    #[ORM\Column(type: Types::TEXT)]
//    #[Assert\NotBlank(message: "Content should not be blank.")]
//    #[Assert\NotNull(message: "Content cannot be null.")]
//    #[Assert\Length(
//        min: 1,
//        max: 5000,
//        minMessage: "Content must be at least {{ limit }} character long.",
//        maxMessage: "Content cannot exceed {{ limit }} characters."
//    )]
//    private ?string $content = null;
//
//    #[ORM\Column(length: 255, nullable: true)]
//    #[Assert\Url(message: "ImageUrl must be a valid URL.")]
//    #[Assert\Length(
//        max: 255,
//        maxMessage: "ImageUrl cannot exceed {{ limit }} characters."
//    )]
//    private ?string $imageUrl = null;
//
//    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
//    #[Assert\NotNull(message: "CreatedAt cannot be null.")]
//    #[Assert\Type(\DateTimeInterface::class, message: "CreatedAt must be a valid datetime.")]
//    private ?\DateTimeInterface $createdAt = null;
//
//    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
//    #[Assert\Type(\DateTimeInterface::class, message: "UpdatedAt must be a valid datetime.")]
//    private ?\DateTimeInterface $updatedAt = null;
//
//    #[ORM\ManyToOne(targetEntity: User::class)]
//    #[ORM\JoinColumn(nullable: false)]
//    #[Assert\NotNull(message: "User must be specified.")]
//    private ?User $user = null;
//
//    public function getId(): ?int
//    {
//        return $this->id;
//    }
//
//    public function getContent(): ?string
//    {
//        return $this->content;
//    }
//
//    public function setContent(string $content): self
//    {
//        $this->content = $content;
//
//        return $this;
//    }
//
//    public function getImageUrl(): ?string
//    {
//        return $this->imageUrl;
//    }
//
//    public function setImageUrl(?string $imageUrl): self
//    {
//        $this->imageUrl = $imageUrl;
//
//        return $this;
//    }
//
//    public function getCreatedAt(): ?\DateTimeInterface
//    {
//        return $this->createdAt;
//    }
//
//    public function setCreatedAt(\DateTimeInterface $createdAt): self
//    {
//        $this->createdAt = $createdAt;
//
//        return $this;
//    }
//
//    public function getUpdatedAt(): ?\DateTimeInterface
//    {
//        return $this->updatedAt;
//    }
//
//    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
//    {
//        $this->updatedAt = $updatedAt;
//
//        return $this;
//    }
//
//    public function getUser(): ?User
//    {
//        return $this->user;
//    }
//
//    public function setUser(?User $user): self
//    {
//        $this->user = $user;
//
//        return $this;
//    }
//}
