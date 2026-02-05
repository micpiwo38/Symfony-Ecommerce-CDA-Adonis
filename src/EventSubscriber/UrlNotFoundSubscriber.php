<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent; // C'est l'événement qui nous intéresse
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UrlNotFoundSubscriber implements EventSubscriberInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator){
        $this->urlGenerator = $urlGenerator;
    }
    public function onKernelException(ExceptionEvent $event):void{
    /*
    // getThrowable() fonctionne maintenant car l'événement est de type ExceptionEvent
    $exception = $event->getThrowable();

    // 1. Est-ce une 404 "Route non trouvée" ?
    if(!$exception instanceof NotFoundHttpException){
        return;
    }

    // 2. C'est une 404 => on redirige vers la page de login (par exemple)
    // Assurez-vous que 'app_login' est le nom d'une route valide
    $url = $this->urlGenerator->generate("app_produits_index");

    // 3. Nouvelle réponse de redirection (code 301 pour Permanent)
    $response = new RedirectResponse($url, Response::HTTP_MOVED_PERMANENTLY);

    // 4. Executer la nouvelle réponse à la place de l'erreur 404
    $event->setResponse($response);
    */
}


public static function getSubscribedEvents(): array
{
    return [
        // CORRECTION : Écoutez l'événement d'exception du Kernel
        ExceptionEvent::class => 'onKernelException',
    ];
}
}
