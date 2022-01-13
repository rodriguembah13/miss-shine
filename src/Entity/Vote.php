<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VoteRepository::class)
 */
class Vote
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="integer")
     */
    private $nombreVote;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $monaie;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Candidat::class, inversedBy="votes")
     */
    private $candidat;

    public function __construct()
    {
        $this->createdAt=new \DateTime('now');
        $this->updatedAt=new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getNombreVote()
    {
        return $this->nombreVote;
    }

    /**
     * @param mixed $nombreVote
     * @return Vote
     */
    public function setNombreVote($nombreVote)
    {
        $this->nombreVote = $nombreVote;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMonaie()
    {
        return $this->monaie;
    }

    /**
     * @param mixed $monaie
     * @return Vote
     */
    public function setMonaie($monaie)
    {
        $this->monaie = $monaie;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Vote
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return Vote
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return Vote
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getCandidat(): ?Candidat
    {
        return $this->candidat;
    }

    public function setCandidat(?Candidat $candidat): self
    {
        $this->candidat = $candidat;

        return $this;
    }
}
