<?php

class Lokalise_Callback_Update_Posts extends Abstract_Lokalise_Callback
{

    /**
     * @var Lokalise_PluginProvider
     */
    private $pluginProvider;

    /**
     * @param Lokalise_PluginProvider $pluginProvider
     */
    public function __construct($pluginProvider)
    {
        $this->pluginProvider = $pluginProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function handler($request, $response)
    {
        if (!$response instanceof WP_REST_Response) {
            return $response;
        }

        $provider = $this->pluginProvider->getEffectiveProvider();

        $decorator = $provider->getDecorator();
        if ($decorator !== null) {
            $response = $decorator->decorateRequest($request, $response);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        Lokalise_RestCallbacks::registerCallback($this);
    }

    /**
     * {@inheritdoc}
     */
    public function isMatchingRequest($request)
    {
        if ($request->get_method() !== self::HTTP_POST) {
            return false;
        }

        return parent::isMatchingRequest($request);
    }
}
