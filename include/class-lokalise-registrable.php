<?php

/**
 * Class Lokalise_Loadable serves as base for all plugin components that register Wordpress hooks
 */
abstract class Lokalise_Registrable
{
    /**
     * Function is called after all components are collected
     *
     * @return void
     */
    abstract public function register();
}
