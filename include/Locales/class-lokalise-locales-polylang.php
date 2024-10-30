<?php

class Lokalise_Locales_Polylang implements Lokalise_Locales
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultLocale()
    {
        return pll_default_language('locale');
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales()
    {
        if (!function_exists('PLL')) {
            // Polylang API is not available
            return [];
        }

        $pll = Pll();
        $defaultLang = pll_default_language('slug');

        $result = [];
        /**
         * @var PLL_Language $lang
         */
        foreach ($pll->model->get_languages_list() as $lang) {
            $locale = new Lokalise_Model_Locale($lang->name, $lang->get_locale());
            if ($lang->slug === $defaultLang) {
                array_unshift($result, $locale);
            } else {
                $result[] = $locale;
            }
        }

        return $result;
    }
}
