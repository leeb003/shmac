<?php
/**
 * WP Mortgage Calculator Uninstall
 *
 * Uninstalling WP Mortgage Calculator deletes options set for the plugin.
 *
 * @author      ScriptHat
 * @category    Core
 * @package     shmac/Uninstaller
 * @version     1.1.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$settings_option = 'shmac_settings';
$email_option    = 'shmac_email';

// For Single site
if ( !is_multisite() ) {
	delete_option($settings_option);
	delete_option($email_option);
} else { // Multisite
	global $wpdb;
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	$original_blog_id = get_current_blog_id();
	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		delete_site_option($settings_option);
		delete_site_option($email_option);
	}
	switch_to_blog( $original_blog_id );
}

