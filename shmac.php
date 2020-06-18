<?php
/*
 * Plugin Name: WP Amortization Calculator
 * Plugin URI: https://www.scripthat.com
 * Description: Mortgage & Amortization Calculator WordPress Plugin
 * Version: 1.5.5
 * Author: ScriptHat
 * Author URI: https://www.scripthat.com
 */
if(defined('SHMAC_PLUGIN_VERSION')) {
    die('ERROR: It looks like you already have one instance of WP Amortization Calculator installed. WordPress cannot activate and handle two instanced at the same time, you need to remove the old one first.');
}


// Constants
    define('SHMAC_ROOT_FILE', __FILE__);
    define('SHMAC_ROOT_PATH', dirname(__FILE__));
    define('SHMAC_ROOT_URL', plugins_url('', __FILE__));
    define('SHMAC_PLUGIN_VERSION', '1.5.5');
    define('SHMAC_PLUGIN_SLUG', basename(dirname(__FILE__)));
    define('SHMAC_PLUGIN_BASE', plugin_basename(__FILE__));
    define('SHMAC_MARKET', 'envato');

    // Load the frontend page content
    require_once SHMAC_ROOT_PATH. '/includes/class-shmac.php';
    $shmac_class = new shmac;

    // the admin menu
    if ( is_admin() ) {
        require_once( SHMAC_ROOT_PATH . '/includes/class-shmac-api-tabs.php' );
    }

    // Automatic Updates
	// Using envato market plugin with tgmpa now
	require_once( SHMAC_ROOT_PATH . '/includes/tgmpa/class-tgm-plugin-activation.php');
	require_once( SHMAC_ROOT_PATH . '/includes/shmac-tgmpa.php');
	add_action('tgmpa_register', 'shmac_register_required_plugins');

    // Translations
    add_action('init', 'shmac_load_textdomain');
    function shmac_load_textdomain() {
        load_plugin_textdomain('shmac', false, basename( dirname( __FILE__ ) ) . '/lang');
    }

    // Visual Composer add in
    if(function_exists('vc_set_as_theme')) {
        require_once SHMAC_ROOT_PATH . '/includes/shmac-vc-extend.php';
    }
	// Elementor
	require_once SHMAC_ROOT_PATH . '/includes/shmac-elementor-load.php';
