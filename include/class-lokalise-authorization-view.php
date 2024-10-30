<?php

class Lokalise_Authorization_View extends Lokalise_Registrable
{
    /**
     * @var Lokalise_Authorization
     */
    private $authorization;

    /**
     * @param Lokalise_Authorization $authorization
     */
    public function __construct($authorization)
    {
        $this->authorization = $authorization;
    }

    public function register()
    {
        // nothing to do here
    }

    private function handleUserAction($choice, Lokalise_Model_Authorization_Request $model)
    {
        if (!$choice) {
            return;
        }

        switch ($choice) {
            case Lokalise_Model_Authorization_Request::AUTH_ACCEPT:
                // generate token request code
                $requestCode = $this->authorization->createRequestCode();

                wp_redirect($model->getAcceptUrl($requestCode));
                die();
            case Lokalise_Model_Authorization_Request::AUTH_REJECT:
                wp_redirect($model->getRejectUrl());
                die();
            case Lokalise_Model_Authorization_Request::AUTH_RETURN:
                wp_redirect($model->getReturnUrl());
                die();
            default:
                $this->redirectToAuthPage($model->redirectUri);
                break;
        }
    }


    private function redirectToLogin()
    {
        if (!is_user_logged_in()) {
            // URI that we are requesting - user is redirected to it after sing-in
            wp_redirect(wp_login_url($_SERVER['REQUEST_URI']));
            exit();
        }
    }

    private function redirectToAuthPage($redirectUri)
    {
        $authUrl = home_url(Lokalise_Routes::ROUTE_AUTH);
        wp_redirect($authUrl . '?redirect_uri=' . $redirectUri);
        die();
    }


    public function authorisationCallback($redirectUri, $choice)
    {
        $this->redirectToLogin();
        $errors = [];

        if (!current_user_can('edit_posts')) {
            $errors['missing_permission_posts'] = __(
                "Current user does not have permission to edit posts!<br />" .
                "Switch user or change user role."
            );
        }

        if (!current_user_can('edit_pages')) {
            $errors['missing_permission_pages'] = __(
                "Current user does not have permission to edit pages!<br />" .
                "Switch user or change user role."
            );
        }

        if ($this->authorization->getSecret() === null) {
            $errors['lokalise_secret_missing'] = sprintf(__(
                "Authorization secret is not set-up.<br />" .
                "Generate secret in <i>Settings &gt; Lokalise.</i><br />" .
                "Or add <code>%s</code> to your <code>wp-config.php</code>"
            ), "define('LOKALISE_SECRET', '&lt;secret&gt;');", 'lokalise');
        }

        if ($this->authorization->getSecret() === null) {
            $errors['lokalise_secret_missing'] = sprintf(__(
                "Authorization secret is not set-up.<br />" .
                "Generate secret in <i>Settings &gt; Lokalise.</i><br />" .
                "Or add <code>%s</code> to your <code>wp-config.php</code>"
            ), "define('LOKALISE_SECRET', '&lt;secret&gt;');", 'lokalise');
        }

        if (!$this->authorization->isRedirectUriValid($redirectUri)) {
            $errors['redirect_uri_not_valid'] = __("Redirect URI is not valid.", 'lokalise');
        }

        $model = new Lokalise_Model_Authorization_Request($redirectUri);
        $model->setValid(empty($errors));

        $this->handleUserAction($choice, $model);

        return [
            'model' => $model,
        ];
    }

}
