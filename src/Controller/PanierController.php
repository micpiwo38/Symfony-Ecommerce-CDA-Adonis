<?php


namespace App\Controller;


use App\Entity\Produits;
use App\Repository\ProduitsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class PanierController extends AbstractController
{
    #[Route('/ajouter-au-panier/{id}', name:'app_panier')]
    public function ajouterPanier(Produits $produits, SessionInterface $session):Response{
        //Récuperer l'id du produit concerné
        $produit_id = $produits->getId();
        //On stock dans une variable une session panier => tableau associatif (cle => valeur)
        $panier = $session->get('panier', []);
        //Si le panier est vide
        if(empty($panier[$produit_id])){
            //Le panier = 1
            $panier[$produit_id] = 1;
        }else{
            //Sinon on recupere le panier et on incremente
            $panier[$produit_id]++;
        }
        //On ajoute le contenu du panier a la session avec le mutateur
        $session->set('panier', $panier);
        //Lors du clic sur le bouton panier => on redirige vers l'affichage du panier
        return $this->redirectToRoute('app_afficher_panier');
    }
    #[Route('/afficher-panier', name:'app_afficher_panier')]
    public function afficherPanier(
        SessionInterface $session,
        ProduitsRepository $produitsRepository):Response{
        //Récuperer le panier a partir de la session
        $panier = $session->get('panier', []);
        //dd($panier);
        //Stocker les produits de la session dans un nouveau tableau de commande
        $commande = [];
        //Par defaut le total de la commande vaut 0
        $total = 0;
        //Itérer le centenu du tableau du panier par référence (quantité)
        foreach($panier as $id => $quantite){
            //Recuperer un produit par id a l'aide du repository
            $produit = $produitsRepository->find($id);
            //Stocker la commande dans un tableau associatif de commande => cle : valeur
            $commande[] = [
                'produit' => $produit,
                'quantite' => $quantite
            ];
            //Calcul du total de la commande
            $total += $produit->getProduitPrix() * $quantite;
        }
        //Appel de la vue
        return $this->render('panier/afficher_panier.html.twig',[
            'panier' => $commande,
            'total' => $total
        ]);
    }

    //Ajouter une quantitée
    #[Route('/ajouter-quantite-panier/{id}', name:'app_ajouter_quantite_panier')]
    public function ajouterQuantitePanier(
        Produits $produits,
        SessionInterface $session) : Response {
        //Id du produit concerné
        //Récuperer l'id du produit concerner
        $id = $produits->getId();
        //Recuperer le panier existant a l'aide du getter de la session
        //Par defaut dans la session le panier est vide (tableau associatif cle => valeur)
        $panier = $session->get('panier', []);
        //Si le panier est vide on ajoute l'id du produit => sinon on incremente
        if(!empty($panier[$id])){
            $panier[$id] += 1;
        }else{
            $panier[$id] = 1;
        }
        //On ajoute le panier a la session a l'aide du setter => (tableau associatif cle => valeur)
        $session->set('panier', $panier);
        //dd($session->get('panier'));
        //Une fois le produit ajouter on redirige vers la page panier
        return $this->redirectToRoute('app_afficher_panier');
    }

    //Supprimer une quantitée
    #[Route('/supprimer-quantite-panier/{id}', name:'app_supprimer_quantite_panier')]
    public function supprimerQuantitePanier(
        Produits $produits,
        SessionInterface $session) : Response {
        //Id du produit concerné
        //Récuperer l'id du produit concerner
        $id = $produits->getId();
        //Recuperer le panier existant a l'aide du getter de la session
        //Par defaut dans la session le panier est vide (tableau associatif cle => valeur)
        $panier = $session->get('panier', []);
        //On retire le produit du panier si il y a 1 seul exemplaire => Sinon on decremente
        if(!empty($panier[$id])){
            //Si la quantite est > 1
            if($panier[$id] > 1){
                $panier[$id]--;
            }else{
                //Defaire une variable php
                //unset() détruit la ou les variables dont le nom a été passé en argument var. =>
                // unset(mixed $var, mixed ...$vars): void
                unset($panier[$id]);
            }
        }
        //On ajoute le panier a la session a l'aide du setter => (tableau associatif cle => valeur)
        $session->set('panier', $panier);
        //Une fois le produit ajouter on redirige vers la page panier
        return $this->redirectToRoute('app_afficher_panier');
    }

    //Supprimer un produit du panier
    #[Route('/supprimer-produit-panier/{id}', name:'app_supprimer_produit_panier')]
    public function supprimerProduitPanier(
        Produits $produits,
        SessionInterface $session
    ):Response{
        //Id du produit concerné
        //Récuperer l'id du produit concerner
        $id = $produits->getId();
        //Recuperer le panier existant a l'aide du getter de la session
        //Par defaut dans la session le panier est vide (tableau associatif cle => valeur)
        $panier = $session->get('panier', []);
        //Si le panier n'est pas vide, on detruit la variable de session via unset()
        if(!empty($panier[$id])){
            unset($panier[$id]);
        }
        //On ajoute le panier a la session a l'aide du setter => (tableau associatif cle => valeur)
        $session->set('panier', $panier);
        $this->addFlash('success', 'Le produit à bien été supprimer du panier !');
        //Une fois le produit ajouter on redirige vers la page panier
        return $this->redirectToRoute('app_afficher_panier');
    }

    #[Route('/vider-panier/', name:'app_vider_panier')]
    public function viderPanier(SessionInterface $session):Response{
        $session->remove('panier');
        $this->addFlash('success', 'Votre panier à été vidé !');
        return $this->redirectToRoute('app_afficher_panier');
    }
}
