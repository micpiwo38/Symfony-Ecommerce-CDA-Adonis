<?php

namespace App\Controller;

use App\Entity\CommandeDetails;
use App\Entity\Commandes;
use App\Entity\Produits;
use App\Repository\ProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class StripePaymentController extends AbstractController
{
    #[Route('/commande/creer-session-stripe/{numero_cmd}', name: 'app_paiement_stripe')]
    public function stripeCheckout(
        string $numero_cmd,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator
    ): RedirectResponse
    {
        //Tableau vide des commandes
        $produits_stripe = [];
        //Recuperer le numero de commande depuis la table commandes
        $commande = $em->getRepository(Commandes::class)->findOneBy(['numero_cmd' => $numero_cmd]);
        //dd($commande);
        if(!$commande){
            $this->addFlash('danger', 'Cette commande est introuvable !');
            $this->redirectToRoute('app_produits_index');
        }

        //Parcours du tableau de commandes
        foreach ($commande->getCommandeDetails() as $produit){
            $produits_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => intval($produit->getPrix() * 100), //La valeur en centimes d'€.
                    'product_data' => [
                        'name' => $produit->getProduits()->getProduitNom()
                    ]
                ],
                'quantity' => $produit->getQuantite()
            ];
        }
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']); //Clé secrete de l'API

        //Creer une session de paiement
        try {
            $checkout_session = Session::create([
                'customer_email' => $this->getUser()->getUserIdentifier(),
                'payment_method_types' => ['card'],
                'line_items' =>
                //Tableau de recapitulatif de la commande passée dans la session panier
                    $produits_stripe
                ,
                'mode' => 'payment',
                'metadata' => [
                    'numero_cmd' => $commande->getNumeroCmd()
                ],
                //En cas de succès on redirige ves URL app_paiement_success
                'success_url' => $urlGenerator->generate('app_paiment_success', [
                    'numero_cmd' => $commande->getNumeroCmd()
                ], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $urlGenerator->generate('app_paiment_error', [
                    'numero_cmd' => $commande->getNumeroCmd()
                ], UrlGeneratorInterface::ABSOLUTE_URL),

            ]);
            //dd($checkout_session);
        } catch (ApiErrorException $e) {
            echo "Erreur du paiement de la commande !";

        }
        return new RedirectResponse($checkout_session->url);
    }

    //En cas de succès
    #[Route('/commande/paiement-valider/{numero_cmd}', name: 'app_paiment_success')]
    public function stripeSuccesPaiement(string $numero_cmd, EntityManagerInterface $em):Response{
        $commande = $em->getRepository(Commandes::class)->findOneBy(['numero_cmd' => $numero_cmd]);
        return $this->render('stripe_payment/paiement_success.html.twig',[
            'numero_cmd' => $commande
        ]);
    }

    //En cas d'erreur
    #[Route('/commande/paiement-erreur/{numero_cmd}', name: 'app_paiment_error')]
    public function stripeErrorPaiement():Response{
        return $this->render('stripe_payment/paiement_error.html.twig');
    }

    //Webhook de confirmation
    #[Route('/stripe/webhook', name: 'stripe_webhook', methods:['POST'])]
    public function stripeWebhook(
        Request $request,
    EntityManagerInterface $em
    ):Response{
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');
        $endPointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'];

        try{
                $event = Webhook::constructEvent(
                $payload, $sigHeader, $endPointSecret
            );
        }catch (\Exception $e){
            return new Response('Signature Stripe invalide !', 400);
        }
        if($event->type === 'checkout.session.completed'){
            $session = $event->data->object;
            $numero_cmd = $session->metadata->numero_cmd;
            $commande = $em->getRepository(Commandes::class)->findOneBy(['numero_cmd' => $numero_cmd]);
            if($commande){
                $commande->setStatut("paid");
                $em->flush();
            }
        }
        return new Response("Ok", 200);
    }

    //Route de test
    #[Route('/test/commande', name: 'test_commande')]
    public function testCommande(
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        // 1. Récupérer un utilisateur (ou en créer un)
        $user = $this->getUser();
        if (!$user) {
            throw new \Exception("Connecte-toi pour générer une commande de test.");
        }

        // 2. Créer une commande
        $commande = new Commandes();
        $commande->setNumeroCmd(uniqid('CMD_'));
        $commande->setUser($user);
        $commande->setStatut('pending');
        $commande->setCreatedAt(new \DateTimeImmutable());

        // 3. Générer des produits de test
        for ($i = 1; $i <= 3; $i++) {
            $detail = new CommandeDetails();
            $detail->setCommandes($commande);
            $detail->setQuantite(rand(1, 3));
            $detail->setPrix(rand(10, 500)); // prix en euros
            $detail->setProduits($em->getRepository(Produits::class)->find(18)); // un produit existant

            $em->persist($detail);
        }

        $em->persist($commande);
        $em->flush();

        // 4. Redirection vers Stripe Checkout
        return $this->redirectToRoute('app_paiement_stripe', [
            'numero_cmd' => $commande->getNumeroCmd()
        ]);
    }

}

//stripe listen --forward-to localhost:4242 http://localhost:8000/stripe/webhook

