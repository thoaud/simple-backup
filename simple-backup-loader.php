<?php
/*
Plugin Name: Simple Backup
Plugin URI: http://MyWebsiteAdvisor.com/tools/wordpress-plugins/simple-backup/
Description: Simple Backup System, You can create and download backups for your WordPress Website
Version: 2.6.6
Author: MyWebsiteAdvisor
Author URI: http://MyWebsiteAdvisor.com
*/

register_activation_hook(__FILE__, 'simple_backup_activate');


function simple_backup_activate() {

	// display error message to users
	if ($_GET['action'] == 'error_scrape') {                                                                                                   
	    die("Sorry, Simple Backup Plugin requires PHP 5.2 or higher. Please deactivate Simple Backup Plugin.");                                 
	}
	
	if ( version_compare( phpversion(), '5.2', '<' ) ) {
		trigger_error("", E_USER_ERROR);
	}
	
}


// require simple backup Plugin if PHP 5.2 installed
if ( version_compare( phpversion(), '5.2', '>=') ) {

	define('SB_LOADER', __FILE__);
	
	require_once(dirname(__FILE__) . '/simple-backup-settings-page.php');
	
	require_once(dirname(__FILE__) . '/simple-backup-manager.php');
	require_once(dirname(__FILE__) . '/simple-backup-ftp-tools.php');

	require_once(dirname(__FILE__) . '/simple-backup-ftp-list-table.php');	
	require_once(dirname(__FILE__) . '/simple-backup-list-table.php');
	
	require_once(dirname(__FILE__) . '/simple-backup-plugin.php');
	
	
	$simple_backup = new Simple_Backup_Plugin();

}

?>