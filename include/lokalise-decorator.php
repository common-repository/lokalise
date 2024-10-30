<?php

interface Lokalise_Decorator
{
    /**
     * @param WP_REST_Response $response
     * @return WP_REST_Response
     */
    public function decorateResponse($response);

    /**
     * @param WP_REST_Request $request
     * @param WP_REST_Response $response
     * @return WP_REST_Response
     */
    public function decorateRequest($request, $response);
}
