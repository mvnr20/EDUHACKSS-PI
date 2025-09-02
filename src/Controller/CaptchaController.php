<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Repository\CategoriesRepository;


class CaptchaController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/get-captcha', name: 'get_captcha')]
    public function getCaptcha(): Response
    {
        $response = $this->client->request('GET', 'https://captchas-io1.p.rapidapi.com/?key=API_KEY&action=get&id=CAPTCHA_ID', [
            'headers' => [
                'X-RapidAPI-Host' => 'captchas-io1.p.rapidapi.com',
                'X-RapidAPI-Key' => '1035efd4fbmshc9f9a018da1f4c2p1b6c84jsn90d0f6e31e0b', // Replace with your actual API key
            ],
        ]);

        $captchaCode = $response->getContent();

        // Render a template with the CAPTCHA code or return it in JSON format
        return $this->render('captcha.html.twig', [
            'captcha_code' => $captchaCode,
        ]);
    }
}
