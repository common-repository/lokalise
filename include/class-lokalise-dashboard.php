<?php

class Lokalise_Dashboard extends Lokalise_Registrable
{
    /**
     * @var Lokalise_Authorization
     */
    private $authorization;
    /**
     * @var Lokalise_Installer
     */
    private $installer;

    /**
     * @param Lokalise_Authorization $authorization
     * @param Lokalise_Installer $installer
     */
    public function __construct($authorization, $installer)
    {
        $this->authorization = $authorization;
        $this->installer = $installer;
    }

    public function menuOptions()
    {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        $model = new Lokalise_Model_Dashboard(array(
            'secret' => $this->authorization->getSecret(),
            'generate_url' => $this->optionsUrl(array('generate_secret' => 1)),
        ));

        include(LOKALISE_DIR . 'template/dashboard.php');
    }

    public function dashboardBefore()
    {
        global $pagenow;

        $page = null;
        if (!empty($_GET['page'])) {
            $page = $_GET['page'];
        }

        if ($pagenow === 'options-general.php' && $page === 'lokalise' && !empty($_GET['generate_secret'])) {
            $this->installer->generateSecret();
            wp_redirect($this->optionsUrl());
            exit;
        }
    }

    private function optionsUrl($params = array())
    {
        $defaultParams = array('page' => 'lokalise');
        $params = array_merge($defaultParams, $params);

        return admin_url('/options-general.php?' . http_build_query($params));
    }

    public function dashboardMenu()
    {
        add_options_page('Lokalise', 'Lokalise', 'manage_options', 'lokalise', array($this, 'menuOptions'));
    }

    public function dashboardScripts()
    {
        wp_register_style('lokalise-dashboard-page', LOKALISE_URL . 'public/dashboard.css');
        wp_enqueue_style('lokalise-dashboard-page');
    }

    public function register()
    {
        add_action('admin_menu', array($this, 'dashboardMenu'));
        add_action('admin_enqueue_scripts', array($this, 'dashboardScripts'));
        add_action('admin_init', array($this, 'dashboardBefore'));
    }
}
