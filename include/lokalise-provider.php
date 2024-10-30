<?php

interface Lokalise_Provider
{
    /**
     * Determine whether provider is enabled
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Return locale listing provider
     *
     * @return Lokalise_Locales
     */
    public function getLocale();

    /**
     * @return Lokalise_Decorator|null
     */
    public function getDecorator();

    /**
     * Return localization provider name
     *
     * @return string
     */
    public function getName();

    /**
     * Return short, lower-case, non-space separated provider identifier
     *
     * @return string
     */
    public function getSlug();

    /**
     * Get provider priority.
     * Higher value makes provider more likely to be selected as effective provider.
     *
     * @return int
     */
    public function getPriority();

    /**
     * @return string[]
     */
    public function getTranslatablePosts();
}
