<?php

class Lokalise_Field_Provisioner_Acf extends Lokalise_Registrable implements Lokalise_Field_Provisioner
{
    // Unique slug, which will be used in rest API as post type parameter and provisioner slug
    // Should not conflict with ACF to rest API plugin ("acf")
    const API_SLUG = 'lacf';
    const FIELD_ENABLE_FORMATTING = true;
    const SUPPORTED_FIELD_TYPES = [
        'text',
        'textarea',
        'wysiwyg',
    ];

    public function register()
    {
        Lokalise_Field_ProvisionerProvider::registerFieldProvisioner($this->getSlug(), $this);
    }

    public function isEnabled()
    {
        return is_plugin_active('advanced-custom-fields/acf.php') || is_plugin_active('advanced-custom-fields-pro/acf.php');
    }

    public function getName()
    {
        return 'Advanced Custom Fields';
    }

    public function getSlug()
    {
        return self::API_SLUG;
    }

    public function registerHooks()
    {
        $supportedPostTypes = Post_Types_Provider::getSupportedPostTypes();

        foreach (array_keys($supportedPostTypes) as $postType) {
            $this->registerPostTypeFields($postType);
        }
    }

    public function onRestGetCallback($object)
    {
        // @see: https://www.advancedcustomfields.com/resources/get_field_objects/
        if (!function_exists('get_field_objects')) {
            return [];
        }

        $fieldObjects = get_field_objects($object['id'], self::FIELD_ENABLE_FORMATTING, true);
        if (empty($fieldObjects)) {
            return [];
        }

        $filteredFieldObjects = array_filter($fieldObjects, function ($fieldObject) {
           return in_array($fieldObject['type'], self::SUPPORTED_FIELD_TYPES);
        });

        $fields = [];
        foreach ($filteredFieldObjects as $fieldObject) {
            $fields[$fieldObject['name']] = $fieldObject['value'] ?: '';
        }

        return $fields;
    }

    public function onRestPostCallback($values, $object)
    {
        // @see: https://www.advancedcustomfields.com/resources/update_field/
        // @see: https://www.advancedcustomfields.com/resources/get_field_object/
        if (!function_exists('update_field') || !function_exists('get_field_object')) {
            return false;
        }

        $supportedFieldObjects = $this->getSupportedFieldObjects($object->post_type);

        foreach ($values as $field => $fieldValue) {
            $fieldObject = $supportedFieldObjects[$field] ?: null;

            if (empty($fieldObject) || !in_array($fieldObject['type'], self::SUPPORTED_FIELD_TYPES)) {
                continue;
            }

            update_field($field, $fieldValue, $object->ID);
        }

        return true;
    }

    public function getSupportedFieldObjects($postType)
    {
        if (!function_exists('acf_get_field_groups') || !function_exists('acf_get_fields')) {
            return [];
        }

        $fieldObjects = [];
        $groups = acf_get_field_groups(['post_type' => $postType]);
        foreach ($groups as $group) {
            $groupFieldObjects = acf_get_fields($group['key']);
            foreach ($groupFieldObjects as $fieldObject) {
                if (isset($fieldObjects[$fieldObject['name']])) {
                    continue;
                }
                $fieldObjects[$fieldObject['name']] = $fieldObject;
            }
        }

        return $fieldObjects;
    }

    private function registerPostTypeFields($postType)
    {
        register_rest_field($postType, $this->getSlug(), [
            'get_callback' => [$this, 'onRestGetCallback'],
            'update_callback' => [$this, 'onRestPostCallback'],
            'schema' => [
                'description' => 'Expose advanced custom fields.',
                'type' => 'object',
            ],
        ]);
    }
}
