<?php

namespace App\Entity;

//Appel de la classe ApiResource
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;

use App\Repository\ProduitsRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: ProduitsRepository::class)]
//Ajout de la directive ApiResource
#[ApiResource( operations: [ new GetCollection(normalizationContext: ['groups' => ['produits:list']]), new Get(normalizationContext: ['groups' => ['produits:detail']]), new Post(denormalizationContext: ['groups' => ['produits:write']]), new Put(denormalizationContext: ['groups' => ['produits:write']]), new Delete() ], )]
class Produits
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['produits:list', 'produits:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['produits:list', 'produits:detail', 'produits:write'])]
    private ?string $produit_nom = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['produits:list', 'produits:detail', 'produits:write'])]
    private ?string $produit_description = "" ?? null;

    #[ORM\Column]
    #[Groups(['produits:list', 'produits:detail', 'produits:write'])]
    private ?float $produit_prix = 0 ?? null;

    #[ORM\Column(length: 255)]
    #[Groups(['produits:list', 'produits:detail', 'produits:write'])]
    private ?string $produit_slug = "" ?? null;

    #[ORM\Column]
    #[Groups(['produits:list', 'produits:detail'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['produits:list', 'produits:detail'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'produits', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['produits:detail', 'produits:write'])]
    private ?References $reference = null;

    #[ORM\ManyToOne(inversedBy: 'produits', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['produits:list', 'produits:write'])]
    private ?Categories $categorie = null;

    #[ORM\ManyToMany(targetEntity: Distributeurs::class, inversedBy: 'produits')]
    #[Groups(['produits:detail', 'produits:write'])]
    private Collection $distributeur;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Images::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['produits:list', 'produits:write'])]
    private Collection $images;

    #[ORM\ManyToOne(inversedBy: 'produits', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['produits:detail', 'produits:write'])]
    private ?User $user = null;

    /**
     * @var Collection<int, CommandeDetails>
     */
    #[ORM\OneToMany(targetEntity: CommandeDetails::class, mappedBy: 'produits')]
    #[Groups(['produits:detail'])]
    private Collection $commandeDetails;

    public function __construct()
    {
        $this->distributeur = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->commandeDetails = new ArrayCollection();
    }

    // --- Getters / Setters ---
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduitNom(): ?string
    {
        return $this->produit_nom;
    }

    public function setProduitNom(string $produit_nom): self
    {
        $this->produit_nom = $produit_nom;
        return $this;
    }

    public function getProduitDescription(): ?string
    {
        return $this->produit_description;
    }

    public function setProduitDescription(string $produit_description): self
    {
        $this->produit_description = $produit_description;
        return $this;
    }

    public function getProduitPrix(): ?float
    {
        return $this->produit_prix;
    }

    public function setProduitPrix(float $produit_prix): self
    {
        $this->produit_prix = $produit_prix;
        return $this;
    }

    public function getProduitSlug(): ?string
    {
        return $this->produit_slug;
    }

    public function setProduitSlug(string $produit_slug): self
    {
        $this->produit_slug = $produit_slug;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getReference(): ?References
    {
        return $this->reference;
    }

    public function setReference(?References $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getCategorie(): ?Categories
    {
        return $this->categorie;
    }

    public function setCategorie(?Categories $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getDistributeur(): Collection
    {
        return $this->distributeur;
    }

    public function addDistributeur(Distributeurs $distributeur): self
    {
        if (!$this->distributeur->contains($distributeur)) {
            $this->distributeur->add($distributeur);
        }
        return $this;
    }

    public function removeDistributeur(Distributeurs $distributeur): self
    {
        $this->distributeur->removeElement($distributeur);
        return $this;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Images $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProduit($this);
        }
        return $this;
    }

    public function removeImage(Images $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getProduit() === $this) {
                $image->setProduit(null);
            }
        }
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function __toString(): string
    {
        return $this->produit_nom ?? 'Produit';
    }

    /**
     * @return Collection<int, CommandeDetails>
     */
    public function getCommandeDetails(): Collection
    {
        return $this->commandeDetails;
    }

    public function addCommandeDetail(CommandeDetails $commandeDetail): self
    {
        if (!$this->commandeDetails->contains($commandeDetail)) {
            $this->commandeDetails->add($commandeDetail);
            $commandeDetail->setProduits($this);
        }

        return $this;
    }

    public function removeCommandeDetail(CommandeDetails $commandeDetail): self
    {
        if ($this->commandeDetails->removeElement($commandeDetail)) {
            // set the owning side to null (unless already changed)
            if ($commandeDetail->getProduits() === $this) {
                $commandeDetail->setProduits(null);
            }
        }

        return $this;
    }
}
