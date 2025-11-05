<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DefaultController extends AbstractController
{
    public const string ROUTE_NAME = 'app_default_index';

    #[Route(path: '/', name: self::ROUTE_NAME)]
    public function __invoke(): Response
    {
        return $this->render('default/index.html.twig');
    }
}
