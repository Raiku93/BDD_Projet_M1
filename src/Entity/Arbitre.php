<?php

namespace App\Entity;

use App\Enum\ArbitreRole;
use App\Repository\ArbitreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArbitreRepository::class)]
class Arbitre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, MatchArbitre>
     */
    #[ORM\OneToMany(targetEntity: MatchArbitre::class, mappedBy: 'arbitre')]
    private Collection $matchArbitres;

    /**
     * @var Collection<int, SaisonArbitre>
     */
    #[ORM\OneToMany(targetEntity: SaisonArbitre::class, mappedBy: 'arbitre')]
    private Collection $saisonArbitres;

    public function __construct()
    {
        $this->matchArbitres = new ArrayCollection();
        $this->saisonArbitres = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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
            $matchArbitre->setArbitre($this);
        }

        return $this;
    }

    public function removeMatchArbitre(MatchArbitre $matchArbitre): static
    {
        if ($this->matchArbitres->removeElement($matchArbitre)) {
            // set the owning side to null (unless already changed)
            if ($matchArbitre->getArbitre() === $this) {
                $matchArbitre->setArbitre(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SaisonArbitre>
     */
    public function getSaisonArbitres(): Collection
    {
        return $this->saisonArbitres;
    }

    public function addSaisonArbitre(SaisonArbitre $saisonArbitre): static
    {
        if (!$this->saisonArbitres->contains($saisonArbitre)) {
            $this->saisonArbitres->add($saisonArbitre);
            $saisonArbitre->setArbitre($this);
        }

        return $this;
    }

    public function removeSaisonArbitre(SaisonArbitre $saisonArbitre): static
    {
        if ($this->saisonArbitres->removeElement($saisonArbitre)) {
            // set the owning side to null (unless already changed)
            if ($saisonArbitre->getArbitre() === $this) {
                $saisonArbitre->setArbitre(null);
            }
        }

        return $this;
    }


}
