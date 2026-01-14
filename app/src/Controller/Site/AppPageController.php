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
        $response = $this->render('app.html.twig');
        
        // Cache the page for 1 hour in the browser
        $response->setSharedMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        
        return $response;
    }
}
