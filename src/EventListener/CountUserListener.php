<?php

namespace App\EventListener;

use Predis\Client;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event:ResponseEvent::class , method: 'onUserVisit')]
final class CountUserListener
{
    public function __construct(
        private Client $client
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

        if (!$session->isStarted()) {
            $session->start();
        }

        $ipUser = $request->getClientIp();
        $idSession = $session->getId();
        // On commence les ajouts avec redis
        // On incrémente le nombre total de visite
        $this->client->incr('visit:total');
        // Pour chaque utilisateur, on ajoute la dernière page visitée et l'heure à laquelle l'utilisateur a visité la page
        // C'est effectué uniquement si l'utilisateur a une session
        if (!empty($idSession)) {
            $this->client
                ->hmset('visit:'.$idSession, [
                    'lastPage' => $pathInfo,
                    'lastVisit' => (new \DateTime())->format('Y-m-d H:i')
                ]);
            $this->client->hincrby('visit:'.$idSession,'totalPage',1);
        }
        // On ajoute l'adresse IP dans le set
        $this->client->sAdd('visit:ids', $ipUser);
    }
} 