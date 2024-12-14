<?php

namespace App\Entity;

use App\Repository\SaisonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SaisonRepository::class)]
class Saison
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['saison:read','league:read'])]
    private ?int $id = null;


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['saison:read','league:read'])]
    private ?\DateTimeInterface $debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['saison:read','league:read'])]
    private ?\DateTimeInterface $fin = null;

    #[ORM\Column]
    #[Groups(['saison:read','league:read'])]
    private ?int $nbEquipe = null;



    #[ORM\Column]
    #[Groups(['saison:read','league:read'])]
    private ?int $nbArbitre = null;

    #[ORM\ManyToOne(inversedBy: 'saisons')]
    private ?League $league = null;

    /**
     * @var Collection<int, Inscription>
     */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'saison')]
    #[Groups(['saison:read','league:read'])]
    private Collection $inscriptions;

    /**
     * @var Collection<int, Journee>
     */
    #[ORM\OneToMany(targetEntity: Journee::class, mappedBy: 'saison')]
    #[Groups(['saison:read','league:read'])]
    private Collection $journees;

    #[ORM\Column]
    #[Groups(['saison:read','league:read'])]
    private ?int $nbRemplacement = null;

    /**
     * @var Collection<int, SaisonArbitre>
     */
    #[ORM\OneToMany(targetEntity: SaisonArbitre::class, mappedBy: 'saison')]
    #[Groups(['saison:read','league:read'])]
    private Collection $saisonArbitres;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
        $this->journees = new ArrayCollection();
        $this->saisonArbitres = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }



    public function getFin(): ?\DateTimeInterface
    {
        return $this->fin;
    }

    public function setFin(\DateTimeInterface $fin): static
    {
        $this->fin = $fin;

        return $this;
    }

    public function getNbEquipe(): ?int
    {
        return $this->nbEquipe;
    }

    public function setNbEquipe(int $nbEquipe): static
    {
        $this->nbEquipe = $nbEquipe;

        return $this;
    }

    public function getDebut(): ?\DateTimeInterface
    {
        return $this->debut;
    }

    public function setDebut(\DateTimeInterface $debut): static
    {
        $this->debut = $debut;

        return $this;
    }

    public function getNbArbitre(): ?int
    {
        return $this->nbArbitre;
    }

    public function setNbArbitre(int $nbArbitre): static
    {
        $this->nbArbitre = $nbArbitre;

        return $this;
    }

    public function getLeague(): ?League
    {
        return $this->league;
    }

    public function setLeague(?League $league): static
    {
        $this->league = $league;

        return $this;
    }

    /**
     * @return Collection<int, Inscription>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setSaison($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            // set the owning side to null (unless already changed)
            if ($inscription->getSaison() === $this) {
                $inscription->setSaison(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Journee>
     */
    public function getJournees(): Collection
    {
        return $this->journees;
    }

    public function addJournee(Journee $journee): static
    {
        if (!$this->journees->contains($journee)) {
            $this->journees->add($journee);
            $journee->setSaison($this);
        }

        return $this;
    }

    public function removeJournee(Journee $journee): static
    {
        if ($this->journees->removeElement($journee)) {
            // set the owning side to null (unless already changed)
            if ($journee->getSaison() === $this) {
                $journee->setSaison(null);
            }
        }

        return $this;
    }

    public function getNbRemplacement(): ?int
    {
        return $this->nbRemplacement;
    }

    public function setNbRemplacement(int $nbRemplacement): static
    {
        $this->nbRemplacement = $nbRemplacement;

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
            $saisonArbitre->setSaison($this);
        }

        return $this;
    }

    public function removeSaisonArbitre(SaisonArbitre $saisonArbitre): static
    {
        if ($this->saisonArbitres->removeElement($saisonArbitre)) {
            // set the owning side to null (unless already changed)
            if ($saisonArbitre->getSaison() === $this) {
                $saisonArbitre->setSaison(null);
            }
        }

        return $this;
    }

}
