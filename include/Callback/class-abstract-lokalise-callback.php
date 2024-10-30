<?php

abstract class Abstract_Lokalise_Callback extends Lokalise_Registrable implements Lokalise_Callback
{
    /**
     * @param WP_REST_Request $request
     *
     * @return bool
     */
    public function isMatchingRequest($request) {
        $route = $request->get_route();

        foreach (Post_Types_Provider::getSupportedRoutes() as $supportedRoute) {
            if (strpos($route, $supportedRoute) === 0) {
                return true;
            }
        }

        return false;
    }
}
