<?php

class Lokalise_Model_Authorization_Request
{
    const AUTH_ACCEPT = 'accept';
    const AUTH_REJECT = 'reject';
    const AUTH_RETURN = 'return';

    /**
     * @var string
     */
    public $redirectUri;
    /**
     * @var bool
     */
    private $valid;

    /**
     * @param string $redirectUri
     */
    public function __construct($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function getAcceptUrl($code)
    {
        return sprintf('%s?%s', $this->redirectUri, http_build_query([
            'code' => $code,
        ]));
    }

    public function getRejectUrl()
    {
        return sprintf('%s?%s', $this->redirectUri, http_build_query([
            'error' => 'reject',
            'error_message' => "User rejected authorization",
        ]));
    }

    public function getReturnUrl()
    {
        return sprintf('https://%s/wordpress-redirect', LOKALISE_APP);
    }

    public function getSignInUrl()
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        return wp_login_url($requestUri);
    }

    public function setValid($valid)
    {
        $this->valid = $valid;
    }

    public function isValid()
    {
        return $this->valid;
    }
}
