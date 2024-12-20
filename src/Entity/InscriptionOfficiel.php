<?php

namespace App\Entity;

use App\Repository\InscriptionOfficielRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionOfficielRepository::class)]
class InscriptionOfficiel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptionOfficiels')]
    private ?Officiel $officiel = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptionOfficiels')]
    private ?Equipe $equipe = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptionOfficiels')]
    private ?Saison $saison = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getOfficiel(): ?Officiel
    {
        return $this->officiel;
    }

    public function setOfficiel(?Officiel $officiel): static
    {
        $this->officiel = $officiel;

        return $this;
    }

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): static
    {
        $this->equipe = $equipe;

        return $this;
    }

    public function getSaison(): ?Saison
    {
        return $this->saison;
    }

    public function setSaison(?Saison $saison): static
    {
        $this->saison = $saison;

        return $this;
    }
}
