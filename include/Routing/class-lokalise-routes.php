<?php

class Lokalise_Routes extends Lokalise_Registrable
{
    const ROUTE_AUTH = 'lokalise/authorisation';
    /**
     * @var Lokalise_Router
     */
    private $router;
    /**
     * @var Lokalise_Authorization_View
     */
    private $authorizationView;

    public function __construct(Lokalise_Router $router, Lokalise_Authorization_View $authorizationView)
    {
        $this->router = $router;
        $this->authorizationView = $authorizationView;
    }

    public function register()
    {
        $this->router->addRoute(
            '^' . self::ROUTE_AUTH . '/?',
            [$this->authorizationView, 'authorisationCallback'],
            LOKALISE_DIR . 'template/authorization.php',
            ['redirect_uri' => 1, 'choice' => 2]
        );
    }
}
