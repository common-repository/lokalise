<?php

class Lokalise_Field_ProvisionerProvider extends Lokalise_Registrable
{
    /**
     * @var Lokalise_Field_Provisioner[]
     */
    private static $fieldProvisioners;

    /**
     * @return Lokalise_Field_Provisioner[]
     */
    public function getFieldProvisioners()
    {
        if (empty(self::$fieldProvisioners)) {
            return [];
        }

        return array_filter(self::$fieldProvisioners, function ($fieldProvisioner) {
            return $fieldProvisioner->isEnabled();
        });
    }

    /**
     * @param string $name
     * @param Lokalise_Field_Provisioner $fieldProvisioner
     */
    public static function registerFieldProvisioner($name, $fieldProvisioner)
    {
        self::$fieldProvisioners[$name] = $fieldProvisioner;
    }

    public function register()
    {
        // Silence is golden
    }
}
