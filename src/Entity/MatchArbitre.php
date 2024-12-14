<?php

namespace App\Entity;

use App\Repository\MatchArbitreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatchArbitreRepository::class)]
class MatchArbitre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'matchArbitres')]
    private ?Matchs $match = null;

    #[ORM\ManyToOne(inversedBy: 'matchArbitres')]
    private ?Arbitre $arbitre = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatch(): ?Matchs
    {
        return $this->match;
    }

    public function setMatch(?Matchs $match): static
    {
        $this->match = $match;

        return $this;
    }

    public function getArbitre(): ?Arbitre
    {
        return $this->arbitre;
    }

    public function setArbitre(?Arbitre $arbitre): static
    {
        $this->arbitre = $arbitre;

        return $this;
    }
}
