<?php

namespace App\Entity;

use App\Enum\OfficielRole;
use App\Repository\OfficielRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OfficielRepository::class)]
class Officiel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(enumType: OfficielRole::class)]
    private ?OfficielRole $role = null;

    /**
     * @var Collection<int, InscriptionOfficiel>
     */
    #[ORM\OneToMany(targetEntity: InscriptionOfficiel::class, mappedBy: 'officiel')]
    private Collection $inscriptionOfficiels;

    public function __construct()
    {
        $this->inscriptionOfficiels = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getRole(): ?OfficielRole
    {
        return $this->role;
    }

    public function setRole(OfficielRole $role): static
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection<int, InscriptionOfficiel>
     */
    public function getInscriptionOfficiels(): Collection
    {
        return $this->inscriptionOfficiels;
    }

    public function addInscriptionOfficiel(InscriptionOfficiel $inscriptionOfficiel): static
    {
        if (!$this->inscriptionOfficiels->contains($inscriptionOfficiel)) {
            $this->inscriptionOfficiels->add($inscriptionOfficiel);
            $inscriptionOfficiel->setOfficiel($this);
        }

        return $this;
    }

    public function removeInscriptionOfficiel(InscriptionOfficiel $inscriptionOfficiel): static
    {
        if ($this->inscriptionOfficiels->removeElement($inscriptionOfficiel)) {
            // set the owning side to null (unless already changed)
            if ($inscriptionOfficiel->getOfficiel() === $this) {
                $inscriptionOfficiel->setOfficiel(null);
            }
        }

        return $this;
    }
}
