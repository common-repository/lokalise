<?php

class Lokalise_Provider_Site implements Lokalise_Provider
{
    public function isEnabled()
    {
        return true;
    }

    public function getName()
    {
        return 'Site (default)';
    }

    public function getSlug()
    {
        return 'site';
    }

    public function getPriority()
    {
        return -1;
    }

    public function getLocale()
    {
        return new Lokalise_Locales_Site();
    }

    public function getDecorator()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getTranslatablePosts()
    {
        return [];
    }
}
