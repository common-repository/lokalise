<?php

class Post_Types_Provider extends Lokalise_Registrable
{
    const ROUTE_PREFIX = '/wp/v2';
    const PRIORITY = 50;
    const UNSUPPORTED_TYPES = ['attachment'];

    private static $supportedRoutes = [];
    private static $supportedPostTypes = [];

    private static $initialised = false;
    /**
     * @var Lokalise_PluginProvider
     */
    private $pluginProvider;

    /**
     * @param Lokalise_PluginProvider $pluginProvider
     */
    public function __construct($pluginProvider)
    {
        $this->pluginProvider = $pluginProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        add_action('init', [$this, 'collectPostTypesData'], self::PRIORITY);
    }

    public static function getSupportedRoutes()
    {
        if (!self::$initialised) {
            throw new RuntimeException('Post types are not initialised');
        }

        return self::$supportedRoutes;
    }

    public static function getSupportedPostTypes()
    {
        if (!self::$initialised) {
            throw new RuntimeException('Post types are not initialised');
        }

        return self::$supportedPostTypes;
    }

    /**
     * @return string[]
     */
    private function getTranslatablePosts()
    {

        $provider = $this->pluginProvider->getEffectiveProvider();

        return $provider->getTranslatablePosts();
    }


    public function collectPostTypesData()
    {
        self::$initialised = true;
        $currentUser =  wp_get_current_user();

        if (empty($currentUser->ID)) {
            return;
        }

        $translatablePosts = $this->getTranslatablePosts();

        if (!$translatablePosts) {
            return;
        }

        $postTypes = get_post_types([
            'show_in_rest' => true,
            'public' => true,
        ], 'objects');


        foreach ($postTypes as $postType) {
            if (in_array($postType->name, self::UNSUPPORTED_TYPES, true)) {
                continue;
            }

            if (empty($postType->cap->edit_posts) || !current_user_can($postType->cap->edit_posts)) {
                continue;
            }

            if (!in_array($postType->name, $translatablePosts, true)) {
                continue;
            }

            $route = self::ROUTE_PREFIX . '/' . ($postType->rest_base ?: $postType->name);

            self::$supportedPostTypes[$postType->name] = $route;
            self::$supportedRoutes[] = $route;
        }

        self::$initialised = true;
    }
}
