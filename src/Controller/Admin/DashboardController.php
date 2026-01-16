<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Entity\Distributeurs;
use App\Entity\Produits;
use App\Entity\References;
use App\Entity\User;
use App\Entity\Images;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        return $this->redirectToRoute('admin_user_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(ProduitsCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration')
            ->renderContentMaximized()
            ->setDefaultColorScheme('dark')
            ->setLocales(['fr']);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau d\'adminstration', 'fa fa-home');

        //Cacher les accès si on est pas ROLE_ADMIN
        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::linkToCrud('Catégories', 'fas fa-list', Categories::class);
            yield MenuItem::linkToCrud('Références', 'fas fa-list', References::class);
            yield MenuItem::linkToCrud('Distributeurs', 'fas fa-list', Distributeurs::class);
            yield MenuItem::linkToCrud('Images', 'fas fa-list', Images::class);
            yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-list', User::class);
        }else{
            yield MenuItem::linkToCrud('Produits', 'fas fa-list', Produits::class);
        }
    }
}
