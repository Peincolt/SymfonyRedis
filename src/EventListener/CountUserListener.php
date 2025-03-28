<?php

namespace App\EventListener;

use Predis\ClientInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ResponseEvent::class, method: 'onUserVisit')]
final class CountUserListener
{
    public function __construct(
        private ClientInterface $client
    ) {
    }

    public function onUserVisit(ResponseEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $request = $event->getRequest();
        $pathInfo = $request->getPathInfo();
        $session = $request->getSession();

        if (preg_match('/^\/(api|stats|css|images|js|_(profiler|wdt))/', $pathInfo)) {
            return;
        }

        $ipUser = $request->getClientIp();
        $val = $session->set('test', 45);
        $idSession = $session->getId();
        // On commence les ajouts avec redis
        // On incrémente le nombre total de visite
        try {
            $this->client->incr('visit:total');
            // Pour chaque utilisateur, on ajoute la dernière page visitée et l'heure à laquelle l'utilisateur a visité la page
            // C'est effectué uniquement si l'utilisateur a une session
            if (!empty($idSession)) {
                $this->client
                    ->hmset('visit:'.$idSession, [
                        'lastPage' => $pathInfo,
                        'lastVisit' => (new \DateTime())->format('Y-m-d H:i')
                    ]);
                $this->client->hincrby('visit:'.$idSession, 'totalPage', 1);
                $this->client->expire('visit:'.$idSession, 86400, 'NX');
            }
            // On ajoute l'adresse IP dans le set
            $this->client->sAdd('visit:ids', $ipUser);
        } catch (\Exception $e) {
            //Ici, on pourrait mettre en place un système d'emailing d'alerte et de monitoring
        }
    }
}
