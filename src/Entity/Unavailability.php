<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Availability as UnavailabilityAssert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UnavailabilityRepository")
 * @UnavailabilityAssert
 */
class Unavailability
{
    const REUNION = 0;
    const AUTRE = 1;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="datetime")
     *
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endDate;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $guests = [];

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Vous devez saisir un objet")
     */
    private $object;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="unavailabilities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organiser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Room", inversedBy="unavailabilities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $room;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getGuests()
    {
        return $this->guests;
    }

    public function setGuests($guests): void
    {
        $this->guests = $guests;
    }

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function setObject(string $object): self
    {
        $this->object = $object;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOrganiser(): ?User
    {
        return $this->organiser;
    }

    public function setOrganiser(?User $organiser): self
    {
        $this->organiser = $organiser;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;

        return $this;
    }

}
