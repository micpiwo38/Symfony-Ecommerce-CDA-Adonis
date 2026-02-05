<?php

namespace App\Tests\Fixtures;

use App\Entity\Categories;
use App\Entity\References;
use App\Entity\User;
use App\Entity\Produits;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProduitsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // --- Catégorie unique ---
        $categorie = new Categories();
        $categorie->setCategorieNom('Catégorie Fixtures');
        $manager->persist($categorie);

        // --- User unique ---
        $user = new User();
        $user->setEmail('fixtures@test.com');
        $user->setPassword('test');
        $manager->persist($user);

        // --- 20 produits ---
        for ($i = 1; $i <= 20; $i++) {

            // Référence unique par produit
            $reference = new References();
            $reference->setReferenceValue('REF-' . $i);
            $manager->persist($reference);

            $produit = new Produits();
            $produit->setProduitNom('Produit ' . $i);
            $produit->setProduitDescription('Description du produit ' . $i);
            $produit->setProduitPrix(mt_rand(10, 200));
            $produit->setProduitSlug('produit-' . $i);

            $produit->setCategorie($categorie);
            $produit->setReference($reference);
            $produit->setUser($user);

            $manager->persist($produit);
        }

        $manager->flush();
    }
}
