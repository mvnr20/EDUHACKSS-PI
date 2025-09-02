<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class DataBaseController extends AbstractController
{
    #[Route("/test-database-connection", name:"test_database_connection")]
    public function testDatabaseConnection(EntityManagerInterface $entityManager): Response
    {
        try {
            $connection = $entityManager->getConnection();
            $connection->connect();

            if ($connection->isConnected()) {
                return new Response('Database connection successful');
            }
        } catch (\Exception $e) {
            return new Response('Database connection failed: ' . $e->getMessage());
        }

        return new Response('Database connection failed');
    }
}