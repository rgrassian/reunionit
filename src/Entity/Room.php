<?php

namespace App\Entity;


use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomRepository")
 * @UniqueEntity(fields={"name"}, message="Ce nom de salle est déjà pris")
 * @Gedmo\SoftDeleteable(fieldName="active")
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
     * @var bool
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Vous devez saisir un nom de salle.")
     * @Assert\Length(
     *     max="80",
     *     maxMessage="Le nom de la salle ne doit pas dépasser {{ limit }} caractères."
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @Assert\NotBlank(message="Vous devez indiquer une capacité.")
     * @Assert\Range(min=2, minMessage="La salle doit pouvoir accueillir au moins {{ limit }} personnes.")
     */
    private $capacity;

    /**
     * @ORM\Column(type="array")
     */
    private $features = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Unavailability", mappedBy="room")
     */
    private $unavailabilities;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Vous devez ajouter une image.")
     * @Assert\Image(
     *     mimeTypesMessage="Vérifiez le format de votre image",
     *     maxSize="1M", maxSizeMessage="Attention, votre image est trop lourde."
     * )
     */
    private $picture;

    public function __construct()
    {
        $this->unavailabilities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
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
    public function getUnavailabilities(): Collection
    {
        return $this->unavailabilities;
    }

    public function addUnavailabilities(Unavailability $unavailability): self
    {
        if (!$this->unavailabilities->contains($unavailability)) {
            $this->unavailabilities[] = $unavailability;
            $unavailability->setRoom($this);
        }

        return $this;
    }

    public function removeUnavailabilities(Unavailability $unavailability): self
    {
        if ($this->unavailabilities->contains($unavailability)) {
            $this->unavailabilities->removeElement($unavailability);
            // set the owning side to null (unless already changed)
            if ($unavailability->getRoom() === $this) {
                $unavailability->setRoom(null);
            }
        }

        return $this;
    }

    public function hasUpcomingUnavailabilities() : bool
    {
        $now = new \DateTime();
        foreach ($this->unavailabilities as $unavailability) {
            if ($now < $unavailability->getStartDate()) {
                return true;
            }
        }
        return false;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function setPicture($picture): self
    {
        $this->picture = $picture;

        return $this;
    }
}
