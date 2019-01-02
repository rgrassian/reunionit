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
     * @ORM\OneToMany(targetEntity="App\Entity\Unavailability", mappedBy="room")
     */
    private $unavailabilities;

    public function __construct()
    {
        $this->unavailabilities = new ArrayCollection();
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
     * @return Collection|Unavailability[]
     */
    public function getOccupieds(): Collection
    {
        return $this->unavailabilities;
    }

    public function addOccupied(Unavailability $occupied): self
    {
        if (!$this->unavailabilities->contains($occupied)) {
            $this->unavailabilities[] = $occupied;
            $occupied->setRoom($this);
        }

        return $this;
    }

    public function removeOccupied(Unavailability $occupied): self
    {
        if ($this->unavailabilities->contains($occupied)) {
            $this->unavailabilities->removeElement($occupied);
            // set the owning side to null (unless already changed)
            if ($occupied->getRoom() === $this) {
                $occupied->setRoom(null);
            }
        }

        return $this;
    }
}
