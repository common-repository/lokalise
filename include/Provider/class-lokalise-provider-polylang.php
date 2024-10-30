<?php

class Lokalise_Provider_Polylang extends Lokalise_Registrable implements Lokalise_Provider
{

    public function register()
    {
        Lokalise_PluginProvider::registerProvider($this->getSlug(), $this);
    }

    public function isEnabled()
    {
        return is_plugin_active($this->getPluginFile());
    }

    public function getLocale()
    {
        return new Lokalise_Locales_Polylang();
    }

    public function getDecorator()
    {
        return new Lokalise_Decorator_Polylang();
    }

    public function getName()
    {
        return 'Polylang';
    }

    public function getSlug()
    {
        return 'polylang';
    }

    public function getPriority()
    {
        return 9;
    }

    private function getPluginFile()
    {
        return 'polylang/polylang.php';
    }

    /**
     * @inheritDoc
     */
    public function getTranslatablePosts()
    {
        if (!function_exists('PLL')) {
            // Polylang API is not available
            return [];
        }

        /**
         * @var PLL_REST_Request $pll
         */
        $pll = Pll();

        return $pll->model->get_translated_post_types();
    }
}
