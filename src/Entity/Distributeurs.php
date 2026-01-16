<?php

namespace App\Entity;

use App\Repository\DistributeursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DistributeursRepository::class)]
class Distributeurs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $distributeur_nom = null;

    /**
     * @var Collection<int, Produits>
     */
    #[ORM\ManyToMany(targetEntity: Produits::class, mappedBy: 'distributeur')]
    private Collection $produits;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDistributeurNom(): ?string
    {
        return $this->distributeur_nom;
    }

    public function setDistributeurNom(string $distributeur_nom): static
    {
        $this->distributeur_nom = $distributeur_nom;

        return $this;
    }

    /**
     * @return Collection<int, Produits>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produits $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->addDistributeur($this);
        }

        return $this;
    }

    public function removeProduit(Produits $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            $produit->removeDistributeur($this);
        }

        return $this;
    }
    //Conversion la clé etrangère Produits.distributeur_id en chaine de caractère
    public function __toString()
    {
        return $this->getDistributeurNom();
    }
}
