<?php

class Lokalise_Installer extends Lokalise_Registrable
{
    const VERSION_OPTION = 'lokalise_version';

    /**
     * @var wpdb
     */
    private $wpdb;
    /**
     * @var Lokalise_Loader
     */
    private $loader;
    /**
     * @var Lokalise_Authorization
     */
    private $authorization;

    /**
     * @param wpdb $wpdb
     * @param Lokalise_Loader $loader
     * @param Lokalise_Authorization $authorization
     */
    public function __construct($wpdb, $loader, $authorization)
    {
        $this->wpdb = $wpdb;
        $this->loader = $loader;
        $this->authorization = $authorization;
    }

    private function activateForSingleBlog()
    {
        // dbDelta is fiddly function and does not work as advertised in some cases
        // suppress all error output
        ob_start();
        $authTokenTableSql = $this->getAuthTokenTableSql();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($authTokenTableSql);

        $pluginData = $this->loader->getPluginData();
        $pluginVersion = $pluginData['Version'];

        $installedVersion = get_option(self::VERSION_OPTION);
        if ($installedVersion === false) {
            add_option(self::VERSION_OPTION, $pluginVersion);
        } else {
            update_option(self::VERSION_OPTION, $pluginVersion);
        }

        if ($this->authorization->getSecret() === null) {
            $this->generateSecret();
        }

        $installerOutput = ob_get_clean();
        if ($installerOutput !== '') {
            Lokalise_Logger::writeLog($installerOutput);
        }
    }

    public function activate($networkWide)
    {
        if ($networkWide && is_multisite()) {
            foreach (get_sites(['fields' => 'ids']) as $blogId) {
                switch_to_blog($blogId);
                $this->activateForSingleBlog();
                restore_current_blog();
            }
        } else {
            $this->activateForSingleBlog();
        }
    }

    private function updatePluginVersion($pluginVersion)
    {
        $installedVersion = get_option(self::VERSION_OPTION);
        if ($installedVersion !== $pluginVersion) {
            // implement update routines for each version step
            update_option(self::VERSION_OPTION, $pluginVersion);
        }
    }

    public function update()
    {
        $pluginData = $this->loader->getPluginData();
        $pluginVersion = $pluginData['Version'];

        if (is_multisite()) {
            foreach (get_sites(['fields' => 'ids']) as $blogId) {
                switch_to_blog($blogId);
                $this->updatePluginVersion($pluginVersion);
                restore_current_blog();
            }
        } else {
            $this->updatePluginVersion($pluginVersion);
        }
    }

    public static function uninstall()
    {
        global $wpdb;

        if (is_multisite()) {
            foreach (get_sites(['fields' => 'ids']) as $blogId) {
                $dbPrefix = $wpdb->get_blog_prefix($blogId);
                $wpdb->query("DROP TABLE IF EXISTS {$dbPrefix}lokalise_auth_tokens");
                delete_blog_option($blogId, self::VERSION_OPTION);
                delete_blog_option($blogId, Lokalise_Authorization::SECRET_OPTION);

            }
        } else {
            $dbPrefix = $wpdb->prefix;
            $wpdb->query("DROP TABLE IF EXISTS {$dbPrefix}lokalise_auth_tokens");
            delete_option(self::VERSION_OPTION);
            delete_option(Lokalise_Authorization::SECRET_OPTION);
        }
    }

    public function deactivate()
    {
        // after being deactivated plugin will no longer be loaded and no code will be executed
    }

    public function generateSecret()
    {
        $secret = $this->authorization->generateSecret(Lokalise_Authorization::SECRET_LENGTH);
        if ($this->authorization->getSecret() === null) {
            add_option(Lokalise_Authorization::SECRET_OPTION, $secret);
        } else {
            update_option(Lokalise_Authorization::SECRET_OPTION, $secret);
        }
    }

    /**
     * @link https://codex.wordpress.org/Creating_Tables_with_Plugins#Creating_or_Updating_the_Table
     *
     * @return string
     */
    private function getAuthTokenTableSql()
    {
        /** @wpversion >= 3.5 */
        $charsetCollate = $this->wpdb->get_charset_collate();
        /** @wpversion >= 2.1 */
        $tablePrefix = $this->wpdb->prefix;

        return <<<SQL
CREATE TABLE {$tablePrefix}lokalise_auth_tokens
(
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint(20) unsigned NOT NULL,
  token varchar(64) NULL,
  verify varchar(32) NULL,
  request_code varchar(128) NULL,
  valid_before datetime NULL,
  PRIMARY KEY  (id)
) {$charsetCollate};
SQL;
    }

    public function register()
    {
        add_action('plugins_loaded', [$this, 'update']);
        register_activation_hook(LOKALISE_FILE, [$this, 'activate']);
        register_deactivation_hook(LOKALISE_FILE, [$this, 'deactivate']);
    }
}
