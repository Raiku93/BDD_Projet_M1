<?php

namespace App\Entity;

use App\Enum\JoueurPost;
use App\Enum\SelectionType;
use App\Repository\SelectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SelectionRepository::class)]
class Selection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: SelectionType::class)]
    private ?SelectionType $type = null;

    #[ORM\Column]
    private ?int $but = null;

    #[ORM\Column]
    private ?int $passe = null;

    #[ORM\Column]
    private ?int $cartonJaune = null;

    #[ORM\Column]
    private ?int $cartonRouge = null;

    #[ORM\ManyToOne(inversedBy: 'selections')]
    private ?Matchs $match = null;

    #[ORM\ManyToOne(inversedBy: 'selections')]
    private ?Joueur $joueur = null;

    #[ORM\ManyToOne(inversedBy: 'selections')]
    private ?Equipe $equipe = null;

    #[ORM\Column(enumType: JoueurPost::class)]
    private ?JoueurPost $post = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?SelectionType
    {
        return $this->type;
    }

    public function setType(SelectionType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getBut(): ?int
    {
        return $this->but;
    }

    public function setBut(int $but): static
    {
        $this->but = $but;

        return $this;
    }

    public function getPasse(): ?int
    {
        return $this->passe;
    }

    public function setPasse(int $passe): static
    {
        $this->passe = $passe;

        return $this;
    }

    public function getCartonJaune(): ?int
    {
        return $this->cartonJaune;
    }

    public function setCartonJaune(int $cartonJaune): static
    {
        $this->cartonJaune = $cartonJaune;

        return $this;
    }

    public function getCartonRouge(): ?int
    {
        return $this->cartonRouge;
    }

    public function setCartonRouge(int $cartonRouge): static
    {
        $this->cartonRouge = $cartonRouge;

        return $this;
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

    public function getJoueur(): ?Joueur
    {
        return $this->joueur;
    }

    public function setJoueur(?Joueur $joueur): static
    {
        $this->joueur = $joueur;

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

    public function getPost(): ?JoueurPost
    {
        return $this->post;
    }

    public function setPost(JoueurPost $post): static
    {
        $this->post = $post;

        return $this;
    }
}
