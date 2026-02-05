<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ReferencesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReferencesRepository::class)]
#[ORM\Table(name: '`references`')]
#[ApiResource]

class References
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['produits:detail', 'produits:write'])]
    private ?string $reference_value = "REF-N";

    #[ORM\OneToMany(mappedBy: 'reference', targetEntity: Produits::class)]
    private Collection $produits;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReferenceValue(): ?string
    {
        return $this->reference_value;
    }

    public function setReferenceValue(string $reference_value): self
    {
        $this->reference_value = $reference_value;

        return $this;
    }

    /**
     * @return Collection<int, Produits>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produits $produit): self
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setReference($this);
        }

        return $this;
    }

    public function removeProduit(Produits $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            if ($produit->getReference() === $this) {
                $produit->setReference(null);
            }
        }

        return $this;
    }


    //Conversion la clé etrangère Produits.reference_id en chaine de caractère
    public function __toString()
    {
        return $this->reference_value ?? "";
    }
}
