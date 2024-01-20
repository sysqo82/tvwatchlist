<?php

declare(strict_types=1);

namespace App\Controller\Site;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppPageController extends AbstractController
{
    #[Route('/', name: 'app_page')]
    public function run(): Response
    {
        return $this->render('app.html.twig');
    }
}
