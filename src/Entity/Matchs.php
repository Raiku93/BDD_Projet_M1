<?php

namespace App\Entity;

use App\Repository\MatchsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatchsRepository::class)]
class Matchs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $scoreEquipe1 = null;

    #[ORM\Column]
    private ?int $scoreEquipe2 = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'matchs')]
    private ?Journee $journee = null;

    #[ORM\ManyToOne(inversedBy: 'matchs')]
    private ?Equipe $equipe1 = null;

    #[ORM\ManyToOne]
    private ?Equipe $equipe2 = null;

    /**
     * @var Collection<int, Selection>
     */
    #[ORM\OneToMany(targetEntity: Selection::class, mappedBy: 'match')]
    private Collection $selections;

    /**
     * @var Collection<int, MatchArbitre>
     */
    #[ORM\OneToMany(targetEntity: MatchArbitre::class, mappedBy: 'match')]
    private Collection $matchArbitres;

    public function __construct()
    {
        $this->selections = new ArrayCollection();
        $this->matchArbitres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScoreEquipe1(): ?int
    {
        return $this->scoreEquipe1;
    }

    public function setScoreEquipe1(int $scoreEquipe1): static
    {
        $this->scoreEquipe1 = $scoreEquipe1;

        return $this;
    }

    public function getScoreEquipe2(): ?int
    {
        return $this->scoreEquipe2;
    }

    public function setScoreEquipe2(int $scoreEquipe2): static
    {
        $this->scoreEquipe2 = $scoreEquipe2;

        return $this;
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

    public function getJournee(): ?Journee
    {
        return $this->journee;
    }

    public function setJournee(?Journee $journee): static
    {
        $this->journee = $journee;

        return $this;
    }

    public function getEquipe1(): ?Equipe
    {
        return $this->equipe1;
    }

    public function setEquipe1(?Equipe $equipe1): static
    {
        $this->equipe1 = $equipe1;

        return $this;
    }

    public function getEquipe2(): ?Equipe
    {
        return $this->equipe2;
    }

    public function setEquipe2(?Equipe $equipe2): static
    {
        $this->equipe2 = $equipe2;

        return $this;
    }

    /**
     * @return Collection<int, Selection>
     */
    public function getSelections(): Collection
    {
        return $this->selections;
    }

    public function addSelection(Selection $selection): static
    {
        if (!$this->selections->contains($selection)) {
            $this->selections->add($selection);
            $selection->setMatch($this);
        }

        return $this;
    }

    public function removeSelection(Selection $selection): static
    {
        if ($this->selections->removeElement($selection)) {
            // set the owning side to null (unless already changed)
            if ($selection->getMatch() === $this) {
                $selection->setMatch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MatchArbitre>
     */
    public function getMatchArbitres(): Collection
    {
        return $this->matchArbitres;
    }

    public function addMatchArbitre(MatchArbitre $matchArbitre): static
    {
        if (!$this->matchArbitres->contains($matchArbitre)) {
            $this->matchArbitres->add($matchArbitre);
            $matchArbitre->setMatch($this);
        }

        return $this;
    }

    public function removeMatchArbitre(MatchArbitre $matchArbitre): static
    {
        if ($this->matchArbitres->removeElement($matchArbitre)) {
            // set the owning side to null (unless already changed)
            if ($matchArbitre->getMatch() === $this) {
                $matchArbitre->setMatch(null);
            }
        }

        return $this;
    }
}
