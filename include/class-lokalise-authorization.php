<?php

class Lokalise_Authorization extends Lokalise_Registrable
{
    const SECRET_OPTION = 'lokalise_secret';
    const SECRET_LENGTH = 64;

    const AUTH_HEADER_PATTERN = '~^Bearer (?<token>(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?)$~';

    public static $isAuthorized = false;

    /**
     * @var wpdb
     */
    private $wpdb;

    /**
     * @param wpdb $wpdb
     */
    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
    }

    /**
     * @return string|null
     */
    public function getSecret()
    {
        $secret = get_option(self::SECRET_OPTION);

        if (empty($secret) || strlen($secret) !== self::SECRET_LENGTH) {
            $secret = null;
        }

        return $secret;
    }

    /**
     * @param string $redirectUri
     * @return bool
     */
    public function isRedirectUriValid($redirectUri)
    {
        return 1 === preg_match(
            '~^https://' . preg_quote(LOKALISE_APP) . '/wordpress-redirect(\?.*|$)~',
            $redirectUri
        );
    }

    public function createRequestCode()
    {
        $userData = wp_get_current_user();

        $requestCode = hash('sha512', uniqid('lokalise_' . $userData->ID, true));

        $this->wpdb->insert($this->wpdb->prefix . 'lokalise_auth_tokens', [
            'user_id' => $userData->ID,
            'request_code' => $requestCode,
            'valid_before' => date('Y-m-d H:i:s', time() + LOKALISE_AUTH_TIMEOUT),
        ]);

        return $requestCode;
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function createToken($request)
    {
        $code = $request->get_param('code');
        $secret = $request->get_param('secret');
        $redirectUri = $request->get_param('redirect_uri');

        if (empty($code) || empty($secret) || empty($redirectUri)) {
            return new WP_Error('missing_params', "Missing request parameters", ['status' => 400]);
        }

        if (!$this->isRedirectUriValid($redirectUri)) {
            return new WP_Error('invalid_redirect_uri', "Invalid redirect URI", ['status' => 400]);
        }

        if ($this->getSecret() === null || $secret !== $this->getSecret()) {
            return new WP_Error('invalid_secret', "Invalid Lokalise secret", ['status' => 400]);
        }

        $authToken = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT id, user_id
            FROM {$this->wpdb->prefix}lokalise_auth_tokens
            WHERE request_code = %s AND valid_before IS NOT NULL AND valid_before > %s",
            $code,
            date('Y-m-d H:i:s')
        ), ARRAY_A);

        if ($authToken === null) {
            return new WP_Error('invalid_code', "Invalid request code", ['status' => 400]);
        }

        // take only 94 characters starting from generated hash
        // this will ensure that resulting base64 string is exactly 64 characters long
        $tokenRaw = substr(hash('sha512', uniqid('lokalise_' . $authToken['user_id'], true)), 0, 94);
        $tokenBin = hex2bin($tokenRaw);
        $token = base64_encode($tokenBin);

        // generate weak hash just for verification
        // this will be used upon request to check if token is issued with current LOKALISE_SECRET
        $verify = hash('md5', $tokenBin . $this->getSecret());

        $this->wpdb->update($this->wpdb->prefix . 'lokalise_auth_tokens', [
            'token' => $token,
            'request_code' => null, // remove request code
            'valid_before' => null, // valid_before is used for request_code only, token has indefinite lifespan
            'verify' => $verify, // verify is meant to invalidate all issued tokens
        ], [
            'id' => $authToken['id'],
        ], ['%s', '%s', '%s'], ['%d']);

        $data = [
            'token' => $token,
        ];

        return rest_ensure_response($data);
    }

    public function authorizationRestInit()
    {
        register_rest_route(LOKALISE_REST_NS, 'auth/tokens', [
            'methods' => 'POST',
            'callback' => [$this, 'createToken'],
            'args' => [
                'code' => [
                    'type' => 'string',
                    'description' => __("Authorization token request code", 'lokalise'),
                    'required' => true,
                ],
                'secret' => [
                    'type' => 'string',
                    'description' => __("Authorization secret found at Settings > Lokalise", 'lokalise'),
                    'required' => true,
                ],
                'redirect_uri' => [
                    'type' => 'string',
                    'description' => __("Redirect URI for remote service validation", 'lokalise'),
                    'required' => true,
                ],
            ],
            'permission_callback' => '__return_true',
        ]);
    }

    public function adminNotice()
    {
        if ($this->getSecret() === null) {
            include(LOKALISE_DIR . 'template/admin-notice-secret.php');
        }
    }

    public function register()
    {
        add_action('rest_api_init', [$this, 'authorizationRestInit']);
        add_filter('determine_current_user', [$this, 'authorizeRequest']);
        add_action('admin_notices', [$this, 'adminNotice']);
    }

    public function getRequestHeaders()
    {
        $baseHeaders = [];
        if (function_exists('apache_request_headers')) {
            $baseHeaders = apache_request_headers();
        }

        return array_merge($baseHeaders, $this->getAllHeaders());
    }

    private function getAllHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }

    public function authorizeRequest($userId)
    {
        if ($userId && $userId > 0) {
            return (int)$userId;
        }

        if ($this->getSecret() === null) {
            return null;
        }

        $requestHeaders = $this->getRequestHeaders();
        if (empty($requestHeaders['Authorization'])) {
            return null;
        }

        $match = [];
        // match base64 value in header
        if (!preg_match(self::AUTH_HEADER_PATTERN, $requestHeaders['Authorization'], $match)) {
            return null;
        }

        $tokenRaw = base64_decode($match['token']);
        if ($tokenRaw === false) {
            return null;
        }

        $verify = hash('md5', $tokenRaw . $this->getSecret());
        $userId = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT user_id
            FROM {$this->wpdb->prefix}lokalise_auth_tokens
            WHERE token = %s AND verify = %s",
            $match['token'],
            $verify
        ));

        if ($userId === null) {
            return null;
        }

        self::$isAuthorized = true;

        return (int)$userId;
    }

    public function generateSecret($length)
    {
        $secret = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        $max = strlen($codeAlphabet);

        for ($i = 0; $i < $length; $i++) {
            $secret .= $codeAlphabet[$this->cryptoRandSecure(0, $max - 1)];
        }

        return $secret;
    }

    private function cryptoRandSecure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) {
            return $min;
        } // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int)($log / 8) + 1; // length in bytes
        $bits = (int)$log + 1; // length in bits
        $filter = (int)(1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);

        return $min + $rnd;
    }
}
