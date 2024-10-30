<?php

class Lokalise_RestCallbacks extends Lokalise_Registrable
{
    /**
     * @var Lokalise_Callback[]
     */
    private static $requestCallbacks = [];

    /**
     * @var callable|null
     */
    private $requestHandler;

    /**
     * @param $response
     * @param $restServer
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response|null
     */
    public function beforeRequest($response, $restServer, $request)
    {
        $this->requestHandler = null;

        foreach (self::$requestCallbacks as $callback) {
            if ($callback->isMatchingRequest($request)) {
                $this->requestHandler = function ($response) use ($callback, $request) {
                    return $callback->handler($request, $response);
                };
                break;
            }
        }

        return $response;
    }

    /**
     * @param Lokalise_Callback $callback
     */
    public static function registerCallback($callback)
    {
        self::$requestCallbacks[] = $callback;
    }

    /**
     * @param WP_REST_Response $response
     * @return WP_REST_Response
     */
    public function afterRequest($response)
    {
        if ($this->requestHandler === null) {
            return $response;
        }

        return call_user_func($this->requestHandler, $response);
    }

    public function register()
    {
        add_filter('rest_request_before_callbacks', array($this, 'beforeRequest'), 10, 3);
        add_filter('rest_request_after_callbacks', array($this, 'afterRequest'));
    }
}
