<?php

interface Lokalise_Callback
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';

    /**
     * @param WP_REST_Request $request
     * @param WP_REST_Response|WP_Error $response
     *
     * @return WP_REST_Response|WP_Error
     */
    public function handler($request, $response);

    /**
     * @param WP_REST_Request $request
     *
     * @return bool
     */
    public function isMatchingRequest($request);
}
