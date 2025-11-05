<?php

declare(strict_types=1);

namespace App\Controller\Security;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class LogoutController extends AbstractController
{
    public const string ROUTE_NAME = 'app_logout';

    #[Route(path: '/logout', name: self::ROUTE_NAME)]
    public function __invoke(): void
    {
        throw new LogicException(
            'This method can be blank - it will be intercepted by the logout key on your firewall.',
        );
    }
}
