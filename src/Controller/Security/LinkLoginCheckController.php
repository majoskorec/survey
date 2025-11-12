<?php

declare(strict_types=1);

namespace App\Controller\Security;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class LinkLoginCheckController extends AbstractController
{
    public const string ROUTE_NAME = 'app_link_login_check';

    #[Route(path: '/link_login_check', name: self::ROUTE_NAME)]
    public function __invoke(): never
    {
        throw new LogicException('This code should never be reached');
    }
}
