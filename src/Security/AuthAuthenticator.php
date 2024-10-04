<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordCredentialsBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthAuthenticator extends AbstractAuthenticator
{
    private $router;
    private $userProvider;

    public function __construct(RouterInterface $router, UserProviderInterface $userProvider)
    {
        $this->router = $router;
        $this->userProvider = $userProvider;
    }

    public function supports(Request $request): ?bool
    {
        // Check if the current request is for the login route
        return $request->attributes->get('_route') === 'app_login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        // Get credentials from the login form
        $username = $request->request->get('username', '');
        $password = $request->request->get('password', '');

        // You can also use CSRF tokens for security if needed
        $csrfToken = $request->request->get('_csrf_token');

        // Return a Passport with user credentials and badges
        return new Passport(
            new UserBadge($username, function ($userIdentifier) {
                return $this->userProvider->loadUserByIdentifier($userIdentifier);
            }),
            new PasswordCredentialsBadge($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?RedirectResponse
    {
        if ($this->router->generate('dish_list')) {
            return new RedirectResponse($this->router->generate('dish_list'));
        }

        // Redirect to the dishes page after successful login
        return new RedirectResponse($this->router->generate('app_home')); // Or any other fallback
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?RedirectResponse
    {
        // Redirect back to the login page on failure with an error message
        return new RedirectResponse($this->router->generate('app_login'));
    }
}
