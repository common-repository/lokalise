<?php

class Lokalise_I18n extends Lokalise_Registrable
{
    public function loadTextDomain()
    {
        $textDomain = 'lokalise';
        $domainPath = '/languages';
        load_plugin_textdomain($textDomain, false, LOKALISE_DIR . $domainPath);
    }

    public function register()
    {
        add_action('plugins_loaded', array($this, 'loadTextDomain'));
    }
}
