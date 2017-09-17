<?php
/*
 * Plugin Name: WP Amortization Calculator
 * Plugin URI: http://www.sh-themes.com
 * Description: Mortgage & Amortization Calculator WordPress Plugin
 * Version: 1.1.11
 * Author: SH-Themes
 * Author URI: http://www.sh-themes.com
 */
if(defined('SHMAC_PLUGIN_VERSION')) {
    die('ERROR: It looks like you already have one instance of WP Amortization Calculator installed. WordPress cannot activate and handle two instanced at the same time, you need to remove the old one first.');
}


// Constants
    define('SHMAC_ROOT_FILE', __FILE__);
    define('SHMAC_ROOT_PATH', dirname(__FILE__));
    define('SHMAC_ROOT_URL', plugins_url('', __FILE__));
    define('SHMAC_PLUGIN_VERSION', '1.1.11');
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
	require SHMAC_ROOT_PATH . '/includes/plugin-updates/plugin-update-checker.php';
	$update_checker = new PluginUpdateChecker_2_1(
	'http://updates.sh-themes.com/server/?action=get_metadata&slug=shmac', //Metadata URL.
		__FILE__, //Full path to the main plugin file.
		'shmac' //Plugin slug. Usually it's the same as the name of the directory.
	);
	$update_checker->addQueryArgFilter('shmac_updates_additional_queries');
	function shmac_updates_additional_queries($queryArgs) {
    	$first_tab = get_option('shmac_settings');
    	$license_key = isset($first_tab['license_key']) ? $first_tab['license_key'] : '';
		$queryArgs['license_key'] = $license_key;
		$queryArgs['market'] = SHMAC_MARKET;
		return $queryArgs;
	}

	// Translations
	add_action('init', 'shmac_load_textdomain');
	function shmac_load_textdomain() {
		load_plugin_textdomain('shmac', false, basename( dirname( __FILE__ ) ) . '/lang');
	}

	// Visual Composer add in
    if(function_exists('vc_set_as_theme')) {
        require_once SHMAC_ROOT_PATH . '/includes/shmac-vc-extend.php';
    }
