<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/hello', name: 'hello_world')]
    public function index(Connection $connection): Response
    {
        // Appeler la fonction hello_world() directement depuis le contrôleur
        $result = $connection->fetchOne('SELECT hello_world()');

        return new Response($result); // Afficher le résultat
    }
}
