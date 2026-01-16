<?php

namespace App\Entity;

use App\Repository\ReferencesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReferencesRepository::class)]
#[ORM\Table(name: '`references`')]
class References
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference_value = null;

    #[ORM\OneToOne(mappedBy: 'reference', cascade: ['persist', 'remove'])]
    private ?Produits $produits = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReferenceValue(): ?string
    {
        return $this->reference_value;
    }

    public function setReferenceValue(string $reference_value): static
    {
        $this->reference_value = $reference_value;

        return $this;
    }

    public function getProduits(): ?Produits
    {
        return $this->produits;
    }

    public function setProduits(Produits $produits): static
    {
        // set the owning side of the relation if necessary
        if ($produits->getReference() !== $this) {
            $produits->setReference($this);
        }

        $this->produits = $produits;

        return $this;
    }

    //Conversion la clé etrangère Produits.reference_id en chaine de caractère
    public function __toString()
    {
        return $this->reference_value;
    }
}
