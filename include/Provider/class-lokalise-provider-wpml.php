<?php

class Lokalise_Provider_Wpml extends Lokalise_Registrable implements Lokalise_Provider
{
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

    public function isEnabled()
    {
        return is_plugin_active($this->getPluginFile());
    }

    public function getSlug()
    {
        return 'wpml';
    }

    public function getName()
    {
        return 'WPML Multilingual CMS';
    }

    public function getPriority()
    {
        return 10;
    }

    public function getLocale()
    {
        return new Lokalise_Locales_Wpml($this->wpdb);
    }

    public function getDecorator()
    {
        return new Lokalise_Decorator_Wpml($this->wpdb);
    }

    public function getPluginFile()
    {
        return 'sitepress-multilingual-cms/sitepress.php';
    }

    public function register()
    {
        Lokalise_PluginProvider::registerProvider($this->getSlug(), $this);
    }

    /**
     * @inheritDoc
     */
    public function getTranslatablePosts()
    {
        global $sitepress;
        global $sitepress_settings;

        if (!$sitepress || !$sitepress_settings) {
            // WPML is not enabled.

            return [];
        }

        $wpml_post_types = new WPML_Post_Types($sitepress);
        $customPosts = $wpml_post_types->get_translatable();
        $translatablePosts = [];

        foreach ($customPosts as $key => $customPost) {
            $translation_mode = WPML_CONTENT_TYPE_DONT_TRANSLATE;
            if (isset($sitepress_settings['custom_posts_sync_option'][$key])) {
                $translation_mode = (int)$sitepress_settings['custom_posts_sync_option'][$key];
            }

            if ($translation_mode === WPML_CONTENT_TYPE_TRANSLATE) {
                $translatablePosts[] = $key;
            }
        }

        return $translatablePosts;
    }
}
