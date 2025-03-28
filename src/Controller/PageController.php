<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PageController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('page/home.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    #[Route('/page1', name: 'page_1')]
    public function indexTwo(): Response
    {
        return $this->render('page/1.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    #[Route('/page2', name: 'page_2')]
    public function indexThree(): Response
    {
        return $this->render('page/2.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
}
