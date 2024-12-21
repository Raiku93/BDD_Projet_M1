<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Connection;
use Symfony\Component\Routing\Attribute\Route;

class HelloWorldController extends AbstractController
{
    #[Route('/hello', name: 'app_hello_world')]
    public function index(Connection $co): Response
    {
     $r=$co -> fetchOne('SELECT say_hello()');  
        return new Response($r);
    }

}
