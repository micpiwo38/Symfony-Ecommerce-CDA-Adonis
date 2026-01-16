<?php

namespace App\Controller\Admin;

use App\Entity\Produits;
use App\Form\ImagesType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;

class ProduitsCrudController extends AbstractCrudController
{

    //Constructeur => Security Bundle donne acces a l'utilisateur connecté
    public function __construct(private Security $security) {}

    public static function getEntityFqcn(): string
    {
        return Produits::class;
    }

    //Afficher les boutons d'action
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // Ajoute le bouton "detail" sur l'index
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(), //Id visible seulement dans l'index
            TextField::new('produit_nom', 'Nom du produit'), //Champ input type texte + label
            TextEditorField::new('produit_description', 'Description du produit'), //Champ textarea + label
            MoneyField::new('produit_prix', 'Prix')
                ->setCurrency('EUR')
                ->setStoredAsCents(), //Prix + Type de devise avec 00.00 deux decimale
            TextField::new('produit_slug', 'Slug'), //Le nom du produit pour une URL Propre SEO

            // Collection pour les images (formulaire)
            CollectionField::new('images', 'Images') //Produits =>  private Collection $images;
                ->setEntryType(ImagesType::class) // Utilisateion se src/form/ImagesType
                ->allowAdd() //  Autorisé l'ajout
                ->allowDelete() //Autorisé la supression
                ->onlyOnForms(), //Visible seulement dans les formulaires

            // Collection pour l'affichage des images sur index => //Produits =>  private Collection $images;
            CollectionField::new('images', 'Images')
                ->onlyOnIndex() //Visible seulement dans l'index
                ->formatValue(function ($value, $entity) { //Formater les valeur avec un callback
                    $html = '';
                    foreach ($entity->getImages() as $image) { //Parcours des images lié au produit
                        $html .= sprintf(
                            '<img src="/img/produits/%s" style="max-width:100px;margin-right:10px;">',
                            $image->getImagePath() //Recuperer l'image depuis l'entité Images
                        );
                    }
                    return $html;
                })
                ->renderExpanded(),

            AssociationField::new('reference', 'Référence')->renderAsEmbeddedForm(), //Supprime le <select>DropDown</select>
            AssociationField::new('categorie', 'Catégorie')->autocomplete(), //Charge totutes les valeurs via une requète Ajax
            AssociationField::new('distributeur', 'Distributeur(s)')
                ->formatValue(fn($value) => implode(', ', $value->map(fn($d) => (string) $d)->toArray())), //Permet l'affichage du tableau des distributeurs
            AssociationField::new('user', 'Vendeur')->onlyOnForms()->hideOnForm() //Cache le champ User => ce dernier est auto remplis
        ];
    }

    /**
     * Gestion des uploads d’images => cet element pourrait un service generique
     */
    private function handleImagesUpload(Produits $produit): void
    {
        //Pour chaque element du tableau d'image
        foreach ($produit->getImages() as $image) {

            // Si un fichier est uploadé
            if ($image->getFile()) {
                $filename = uniqid() . '.' . $image->getFile()->guessExtension(); //ID unique + objet file + extension
                //Deplacer l'image => php move_uploaded_file()
                $image->getFile()->move(
                    $this->getParameter('images_directory'), //Clé confiqurer dans services.yaml
                    $filename
                );
                $image->setImagePath($filename); //Utilisé le mutateur de l'entité Images
            }

            // Si aucune image n’est uploadée et image_path vide → supprimer pour éviter NULL
            if (!$image->getFile() && !$image->getImagePath()) {
                $produit->removeImage($image);
            } else {
                // Associer correctement l'image au produit
                $image->setProduit($produit);
            }
        }
    }
    //Appelé au niveau du Event  Kerne Symfonyl avant INSERT INTO et UPDATE =>
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        //Si ce n'est pas une insatnce de l'entité Produits => STOP
        if (!$entityInstance instanceof Produits) {
            return;
        }
        //Supprimer les balises HTML du textarea
        $entityInstance->setProduitDescription(strip_tags($entityInstance->getProduitDescription()));
        //Utilisateur connecté a l'aide du getter
        $user = $this->security->getUser();
        $entityInstance->setUser($user);

        $this->handleImagesUpload($entityInstance);
        parent::persistEntity($em, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if (!$entityInstance instanceof Produits) {
            return;
        }
        //Supprimer les balises HTML du textarea
        $entityInstance->setProduitDescription(strip_tags($entityInstance->getProduitDescription()));

        $this->handleImagesUpload($entityInstance);
        parent::updateEntity($em, $entityInstance);
    }

    //Afficher un espace admin privé par utilisateur
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $query_builder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        //Utilisateur connecté
        $current_user = $this->getUser();
        //Filtrer les produits par utilisateur connecté
        if ($this->isGranted('ROLE_USER') && $current_user) {
            $query_builder->andWhere('entity.user = :user')
                ->setParameter('user', $current_user);
        }
        return $query_builder;
    }
}
