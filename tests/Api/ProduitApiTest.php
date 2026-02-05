<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class ProduitApiTest extends ApiTestCase
{
    /*
     *  Depuis API Platform 4.1, le kernel n’est plus automatiquement booté lors de createClient().
        Pour éviter la dépréciation, il suffit d’ajouter une seule ligne dans ta classe de test :
     */
    protected static ?bool $alwaysBootKernel = true;
    //Test pour afficher tous les produits de l'API
    public function testGetProduitsCollection(): void
    {
        //Creer un client HTTP
        $client = static::createClient();
        //Une requète methode GET qui pointe vers le point d'entrée de l'API
        $client->request('GET', '/api/produits');
        //Restourne une reponse code 200
        $this->assertResponseIsSuccessful();
        //L'entete de la requète est application/ld+json
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        //Contient des objets de type Collection
        $this->assertJsonContains([
            '@type' => 'Collection' //Api Platforme retourne des objet de type Collection (hydra:Collection) pour les versions plus recente
        ]);
    }

    //Tester l'ajout d'un produit
    public function testCreateProduit(): void
    {
        //Creer un client HTTP
        $client = static::createClient();
        //Acceder au conteneur principale et a ORM Doctrine Manager
        $em = static::getContainer()->get('doctrine')->getManager();

        // --- Création des entités obligatoires ---
        $categorie = new \App\Entity\Categories();
        $categorie->setCategorieNom('Catégorie Test');
        $em->persist($categorie);

        $reference = new \App\Entity\References();
        $reference->setReferenceValue('REF-POST');
        $em->persist($reference);

        $user = new \App\Entity\User();
        $user->setEmail('post@test.com');
        $user->setPassword('test'); // pas important en test
        $em->persist($user);
        //Executer la requète DQL
        $em->flush();

        // --- Requête POST ---
        $client->request('POST', '/api/produits', [
            //Entete de la requète ->ld+json est obligatoire
            'headers' => [ 'Content-Type' => 'application/ld+json', 'Accept' => 'application/ld+json' ],
            'json' => [
                'produit_nom' => 'Produit POST',
                'produit_description' => 'Description POST',
                'produit_prix' => 99.99,
                'produit_slug' => 'produit-post',
                'categorie' => '/api/categories/'.$categorie->getId(),
                'reference' => '/api/references/'.$reference->getId(),
                'user' => '/api/users/'.$user->getId(),
            ]
        ]);
        //La Reponse recupere l'entete et ajoute le content-type ld+json
        $contentType = $client->getResponse()->getHeaders()['content-type'][0] ?? 'ld+json';
        //Verifié que le produit creer n'est pas null
        $this->assertNotNull($contentType);
        //Verifié le contenu de l'entete
        $this->assertStringContainsString('ld+json', $contentType);
        //La reponse retourne un HTTP code 201 -> creation d'un objet
        $this->assertResponseStatusCodeSame(201);

        //Produit fictif
        $this->assertJsonContains([
            'produit_nom' => 'Produit POST',
            'produit_description' => 'Description POST',
            'produit_prix' => 99.99,
            'produit_slug' => 'produit-post',
        ]);
    }

    //Test PUT /api/produits/{id}
    public function testUpdateProduit(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        // --- Création des dépendances ---
        $categorie = new \App\Entity\Categories();
        $categorie->setCategorieNom('Catégorie PUT');
        $em->persist($categorie);

        $reference = new \App\Entity\References();
        $reference->setReferenceValue('REF-PUT');
        $em->persist($reference);

        $user = new \App\Entity\User();
        $user->setEmail('put@test.com');
        $user->setPassword('test');
        $em->persist($user);

        // --- Création du produit ---
        $produit = new \App\Entity\Produits();
        $produit->setProduitNom('Produit Avant PUT');
        $produit->setProduitDescription('Desc');
        $produit->setProduitPrix(10);
        $produit->setProduitSlug('avant-put');
        $produit->setCategorie($categorie);
        $produit->setReference($reference);
        $produit->setUser($user);

        $em->persist($produit);
        $em->flush();

        $id = $produit->getId();

        // --- Requête PUT ---
        $client->request('PUT', '/api/produits/'.$id, [
            //Entete de la requète ->ld+json est obligatoire
            'headers' => [ 'Content-Type' => 'application/ld+json', 'Accept' => 'application/ld+json' ],
            'json' => [
                'produit_nom' => 'Produit Après PUT',
                'produit_description' => 'Desc', 'produit_prix' => 10,
                'produit_slug' => 'avant-put',
                'categorie' => '/api/categories/'.$categorie->getId(),
                'reference' => '/api/references/'.$reference->getId(),
                'user' => '/api/users/'.$user->getId(),
            ]
        ]);
        //La Reponse recupere l'entete et ajoute le content-type ld+json
        $contentType = $client->getResponse()->getHeaders()['content-type'][0] ?? 'ld+json';
        $this->assertResponseIsSuccessful();

        $this->assertJsonContains([
            'produit_nom' => 'Produit Après PUT',
            'produit_description' => 'Desc',
            'produit_prix' => 10,
            'produit_slug' => 'avant-put',
        ]);
    }

    //Test DELETE /api/produits/{id}
    public function testDeleteProduit(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        // --- Création des dépendances ---
        $categorie = new \App\Entity\Categories();
        $categorie->setCategorieNom('Catégorie DELETE');
        $em->persist($categorie);

        $reference = new \App\Entity\References();
        $reference->setReferenceValue('REF-DEL');
        $em->persist($reference);

        $user = new \App\Entity\User();
        $user->setEmail('delete@test.com');
        $user->setPassword('test');
        $em->persist($user);

        // --- Création du produit ---
        $produit = new \App\Entity\Produits();
        $produit->setProduitNom('Produit DELETE');
        $produit->setProduitDescription('Desc');
        $produit->setProduitPrix(20);
        $produit->setProduitSlug('delete');
        $produit->setCategorie($categorie);
        $produit->setReference($reference);
        $produit->setUser($user);

        $em->persist($produit);
        $em->flush();

        $id = $produit->getId();

        // --- Requête DELETE ---
        $client->request('DELETE', '/api/produits/'.$id);

        $this->assertResponseStatusCodeSame(204);

        // Vérification que le produit n'existe plus
        $this->assertNull(
            $em->getRepository(\App\Entity\Produits::class)->find($id)
        );
    }

    //Test sur 1 objet de l'API
    public function testGetProduitItem(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        // --- Création des dépendances obligatoires ---
        $categorie = new \App\Entity\Categories();
        $categorie->setCategorieNom('Catégorie ITEM');
        $em->persist($categorie);

        $reference = new \App\Entity\References();
        $reference->setReferenceValue('REF-ITEM');
        $em->persist($reference);

        $user = new \App\Entity\User();
        $user->setEmail('item@test.com');
        $user->setPassword('test');
        $em->persist($user);

        // --- Création du produit ---
        $produit = new \App\Entity\Produits();
        $produit->setProduitNom('Produit ITEM');
        $produit->setProduitDescription('Description ITEM');
        $produit->setProduitPrix(42.50);
        $produit->setProduitSlug('produit-item');
        $produit->setCategorie($categorie);
        $produit->setReference($reference);
        $produit->setUser($user);

        $em->persist($produit);
        $em->flush();

        $id = $produit->getId();

        // --- Requête GET ---
        $client->request('GET', '/api/produits/' . $id);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@type' => 'Produits',
            'id' => $id,
            'produit_nom' => 'Produit ITEM'
        ]);
    }


}
