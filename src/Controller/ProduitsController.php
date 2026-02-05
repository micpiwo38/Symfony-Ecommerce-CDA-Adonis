<?php

namespace App\Controller;

use App\Entity\Produits;
use App\Form\ProduitsType;
use App\Repository\ProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ProduitsController extends AbstractController
{
    #[Route('/produits', name: 'app_produits_index', methods: ['GET'])]
    public function index(ProduitsRepository $produitsRepository): Response
    {
        return $this->render('produits/index.html.twig', [
            'produits' => $produitsRepository->findAll(),
        ]);
    }

    #[Route('/produit/{id}', name: 'app_produits_show', methods: ['GET'])]
    public function show(Produits $produit): Response
    {
        return $this->render('produits/show.html.twig', [
            'produit' => $produit,
        ]);
    }
    #[Route('/produits/api', name: 'app_produits_api', methods: ['GET'])]
    public function productApi(
        ProduitsRepository $produitsRepository, //Acces au répository
        SerializerInterface $serializer //La classe de serialisation
    ):Response{
        $produits = $produitsRepository->findAll(); //Recuperer tous les produits dans une variables
        //Utilisé les methodes de la classe Serializer + format + le groupe concerné
        $produit_api = $serializer->serialize($produits, "json", ['groups' => ['produits:list']]);
        //Retourne une r"ponse au format Json
        return JsonResponse::fromJsonString($produit_api);
    }
}
