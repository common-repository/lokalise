<?php

interface Lokalise_Locales
{
    /**
     * @return string|null
     * @throws Exception
     */
    public function getDefaultLocale();

    /**
     * @return Lokalise_Model_Locale[]
     * @throws Exception
     */
    public function getLocales();
}
