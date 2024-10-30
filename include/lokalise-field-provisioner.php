<?php

interface Lokalise_Field_Provisioner
{
    /**
     * Determine whether field provisioner is enabled
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Return field provisioner name
     *
     * @return string
     */
    public function getName();

    /**
     * Return short, lower-case, non-space separated field provisioner identifier
     *
     * @return string
     */
    public function getSlug();

    /**
     * Register field provisioner hooks
     *
     * @return void
     */
    public function registerHooks();
}
