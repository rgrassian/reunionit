<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomRepository")
 */
class Room
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $capacity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="array")
     */
    private $features = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Occupied", mappedBy="room")
     */
    private $occupieds;

    public function __construct()
    {
        $this->occupieds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
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

    public function getFeatures(): ?array
    {
        return $this->features;
    }

    public function setFeatures(array $features): self
    {
        $this->features = $features;

        return $this;
    }

    /**
     * @return Collection|Occupied[]
     */
    public function getOccupieds(): Collection
    {
        return $this->occupieds;
    }

    public function addOccupied(Occupied $occupied): self
    {
        if (!$this->occupieds->contains($occupied)) {
            $this->occupieds[] = $occupied;
            $occupied->setRoom($this);
        }

        return $this;
    }

    public function removeOccupied(Occupied $occupied): self
    {
        if ($this->occupieds->contains($occupied)) {
            $this->occupieds->removeElement($occupied);
            // set the owning side to null (unless already changed)
            if ($occupied->getRoom() === $this) {
                $occupied->setRoom(null);
            }
        }

        return $this;
    }
}
