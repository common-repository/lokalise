<?php

class Lokalise_Locales_Site implements Lokalise_Locales
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultLocale()
    {
        return get_locale();
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales()
    {
        // get Wordpress site language
        $siteLocale = get_locale();
        if ($siteLocale === 'en_US') {
            // en_US is default locale and is hard-coded in Wordpress
            return array(
                new Lokalise_Model_Locale('English (United States)', $siteLocale)
            );
        }

        // retrieve locale name from available translation list used in dashboard dropdown
        if (!function_exists('wp_get_available_translations')) {
            require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
        }
        if (!function_exists('wp_get_available_translations')) {
            throw new Exception('Could not list site languages');
        }
        $translations = wp_get_available_translations();

        if (!isset($translations[$siteLocale])) {
            throw new Exception(sprintf('Site locale "%s" can not be described', $siteLocale));
        }
        $translation = $translations[$siteLocale];

        return array(
            new Lokalise_Model_Locale($translation['english_name'], $siteLocale)
        );
    }
}
