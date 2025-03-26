<?php

namespace App\Controller;

use Predis\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class StatsController extends AbstractController
{
    public function __construct(
        private Client $client
    ) {

    }

    #[Route('/stats', name: 'app_stats')]
    public function index(RequestStack $request): Response
    {
        $ipAddress = $request->getCurrentRequest()->getClientIp();
        $totalVisits = $this->client->get('visit:total');
        $userStats = $this->client->hmget('visit:'.$ipAddress,[
            'totalPage',
            'lastVisit',
            'lastPage'
        ]);
        return $this->render('stats/index.html.twig', [
            'controller_name' => 'StatsController',
            'user_stats' => $userStats
        ]);
    }
}
