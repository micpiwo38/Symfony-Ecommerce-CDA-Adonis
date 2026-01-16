<?php


namespace App\Controller;


use App\Entity\CommandeDetails;
use App\Entity\Commandes;
use App\Entity\User;
use App\Repository\CommandeDetailsRepository;
use App\Repository\CommandesRepository;
use App\Repository\ProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\Length;

class CommandesController extends AbstractController
{
    /**
     * @param SessionInterface $session
     * @param ProduitsRepository $produitsRepository
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/commandes', name:'app_valider_commandes')]
    public function ajouterCommande(
        SessionInterface $session,
        ProduitsRepository $produitsRepository,
        EntityManagerInterface $em
    ):Response{
        //Imposer une connexion utilisateur
        $this->denyAccessUnlessGranted('ROLE_USER');
        //Récuperer le panier à l'aide de SessionInterface
        $panier = $session->get('panier', []);
        //Le panier n'est pas vide on creer la commande
        //Instance de l'entité Commandes
        $commande = new Commandes();
        //On remplit la commande à l'aide des setters
        //Utilisateur concerné
        $commande->setUser($this->getUser());
        //Numero de la commande => id randrom
        $commande->setNumeroCmd(uniqid());
        //Le statut de la commande
        $commande->setStatut("En attende de paiement");
        //Calcul du total
        $total = 0;
        //Parcous du panier par reférence pour les details de la commande
        foreach($panier as $item => $quantite){
            //Instance de l'entité CommandeDetails
            $commande_details = new CommandeDetails();
            $produit = $produitsRepository->find($item);
            //dd($produit);
            $prix = $produit->getProduitPrix();
            //Remplir les details de la commande
            $commande_details->setProduits($produit);
            $commande_details->setPrix($prix);
            $commande_details->setQuantite($quantite);
            //Ajout des details a la commande principale (parente)
            //Utilisation de la methode addCommandeDetails => genere par la relation OnToMany
            $commande->addCommandeDetail($commande_details);
        }

        $em->persist($commande);
        $em->flush();

        //On vide le panier
        $session->remove('panier');
        $this->addFlash('success', 'Votre commande à bien été validée !');
        return $this->redirectToRoute('app_resume_commande');
    }

    /**
     * @param CommandesRepository $commandesRepository
     * @param CommandeDetailsRepository $commandeDetailsRepository
     * @return Response
     */
    #[Route('/resume-commandes', name:'app_resume_commande')]
    public function resumeCommande(
        CommandesRepository $commandesRepository,
        CommandeDetailsRepository $commandeDetailsRepository
    ):Response{
        //Recuperer l'utilisateur connecté
        $user = $this->getUser();
        //Les commandes par l'utilisateur
        $commandes = $commandesRepository->findBy(['user' => $user]);
        //Tableau des details des commandes
        $details_commande = [];
        //Le total
        $total = 0;

        //Parcours des commandes de l'utilisateur connecté
        foreach ($commandes as $commande){
            $details = $commandeDetailsRepository->findBy(['commandes' => $commande]);
            //Remplir le tableau des details de la commande
            $details_commande[$commande->getId()] = $details;

            //Calculer le total
            foreach ($details as $detail){
                $total += $detail->getPrix() * $detail->getQuantite();
            }
        }
        return $this->render('commandes/resume_commande.html.twig',[
            'commandes' => $commandes,
            'details_commande' => $details_commande,
            'total' => $total,
            'statut' => $commande->getStatut()
        ]);
    }

    /**
     * @param Request $request
     * @param Commandes $commandes
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[Route('/supprimer-commandes/{id}', name:'app_supprimer_commande', methods:['POST'])]
    public function supprimerCommande(
        Request $request,
        Commandes $commandes,
        EntityManagerInterface $em
    ){
        if ($this->isCsrfTokenValid('delete' . $commandes->getId(), $request->getPayload()->get('_token'))) {
            $em->remove($commandes);
            $em->flush();
        }

        return $this->redirectToRoute('app_produits', [], Response::HTTP_SEE_OTHER);
    }
}
