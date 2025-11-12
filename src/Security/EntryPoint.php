<?php

declare(strict_types=1);

namespace App\Security;

use App\Controller\Security\LinkLoginController;
use App\Controller\Security\LoginController;
use Override;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final class EntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {
    }

    #[Override]
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $path = $request->getPathInfo();

        if (str_starts_with($path, '/admin')) {
            return new RedirectResponse($this->router->generate(LoginController::ROUTE_NAME));
        }

        return new RedirectResponse($this->router->generate(LinkLoginController::ROUTE_NAME));
    }
}
