<?php

namespace App\Entity;

use App\Repository\MailRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\TimestampableTrait;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: MailRepository::class)]
#[ORM\HasLifecycleCallbacks]

class Mail
{
    use TimestampableTrait;

    public const PENDING_ADDITION = 'Pending Addition';
    public const PENDING_REMOVAL = 'Pending Removal';
    public const ADDED = 'Added';
    public const REMOVED = 'Removed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, columnDefinition: "ENUM('Pending Addition', 'Pending Removal', 'Added', 'Removed')")]
    private ?string $status = null;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, array(self::PENDING_ADDITION, self::PENDING_REMOVAL, self::ADDED, self::REMOVED))) {
            throw new InvalidArgumentException("Invalid status");
        }

        $this->status = $status;

        return $this;
    }
}
