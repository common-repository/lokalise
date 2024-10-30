<?php

class Lokalise_Model_Dashboard
{
    /**
     * @var array
     */
    private $settings = array();

    /**
     * Lokalise_Model_Dashboard constructor.
     *
     * @param array $settings
     */
    public function __construct($settings = array())
    {
        $this->settings = $settings;
    }

    public function getSecret($masked = false)
    {
        $secret = $this->get('secret');

        if (!$masked) {
            return $secret;
        }

        return $this->maskSecret($secret);
    }

    private function maskSecret($secret)
    {
        $length = strlen($secret);
        return str_repeat('*', $length);
    }

    /**
     * @param $name
     * @param null $default
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (!isset($this->settings[$name])) {
            return $default;
        }

        return $this->settings[$name];
    }
}
