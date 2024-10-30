<?php
/**
 * Plugin Name: Lokalise Companion Plugin
 * Plugin URI: https://docs.lokalise.com/en/articles/4131716-wordpress
 * Description: Adds functionality to exchange post and page content with Lokalise
 * Author: Lokalise
 * Author URI: https://lokalise.com
 * Version: 1.6.9
 * Plugin Slug: lokalise
 * Text Domain: lokalise
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    die;
}

if (!defined('LOKALISE_APP')) {
    define('LOKALISE_APP', 'app.lokalise.com');
}

define('LOKALISE_FILE', __FILE__);
define('LOKALISE_DIR', plugin_dir_path(LOKALISE_FILE));
define('LOKALISE_URL', plugin_dir_url(LOKALISE_FILE));
define('LOKALISE_INC', LOKALISE_DIR . 'include/');

define('LOKALISE_REST_NS', 'lokalise/v1');

define('LOKALISE_AUTH_GUARD_COOKIE_NAME', 'lokalise__auth');
define('LOKALISE_AUTH_TIMEOUT', 15 * 60);

require_once(LOKALISE_DIR . 'vendor/autoload.php');

// load plugin in function scope
function lokalise_plugin() {
    /** @var wpdb $wpdb */
    global $wpdb;
    $loader = new Lokalise_Loader($wpdb);
    $loader->load();
}
lokalise_plugin();

register_uninstall_hook(LOKALISE_FILE, 'Lokalise_Installer::uninstall');
