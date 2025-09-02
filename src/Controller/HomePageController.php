<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
    #[Route('/back', name: 'backoffice')]
    public function backindex(): Response
    {
        return $this->render('base.html.twig');
    }

    #[Route('/front', name: 'frontoffice')]
    public function frontindex(): Response
    {
        return $this->render('frontbase.html.twig');
    }

    #[Route('/home', name: 'home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
