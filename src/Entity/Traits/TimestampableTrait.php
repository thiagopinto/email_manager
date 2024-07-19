<?php

namespace App\Entity\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait TimestampableTrait
{
    #[ORM\Column(type: 'datetime')]
    #[Assert\DateTime()]
    private $createdAt;

    #[ORM\Column(type: 'datetime')]
    #[Assert\DateTime()]
    private $updatedAt;

    /**
     * Get the value of createdAt
     */
    public function getCreatedAt()
    {
        // return $this->createdAt;
        return $this->createdAt->format('Y-m-d H:i:s');
    }

    /**
     * Set the value of createdAt
     *
     * @return  self
     */
    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new DateTime();
    }

    /**
     * Get the value of updatedAt
     */
    public function getUpdatedAt()
    {
        // return $this->updatedAt;
        return $this->updatedAt->format('Y-m-d H:i:s');
    }

    /**
     * Set the value of updatedAt
     *
     * @return  self
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new DateTime();
    }
}
