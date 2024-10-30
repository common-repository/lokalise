<?php

class Lokalise_Model_ProviderNotice
{
    /**
     * @var string[]
     */
    public $providers = array();

    /**
     * @param string[] $providers
     */
    public function __construct($providers)
    {
        $this->providers = $providers;
    }

    /**
     * @return string[]
     */
    public function getProviders()
    {
        return $this->providers;
    }
}
