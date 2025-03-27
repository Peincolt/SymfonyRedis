<?php

namespace App\Controller;

use Predis\ClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class StatsController extends AbstractController
{

    public function __construct(
        private ClientInterface $client
    ) {

    }

    #[Route('/stats', name: 'app_stats')]
    public function index(SessionInterface $session): Response
    {
        $idSession = $session->getId();
        $totalVisits = $this->client->get('visit:total');
        $userStats = $this->client->hmget('visit:'.$idSession,[
            'totalPage',
            'lastVisit',
            'lastPage'
        ]);
        return $this->render('stats/index.html.twig', [
            'user_stats' => $userStats,
            'total_visit' => $totalVisits
        ]);
    }
}
