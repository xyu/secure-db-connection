<?php
/**
 * Plugin name: Secure DB Connection
 * Plugin URI: http://wordpress.org/plugins/secure-db-connection/
 * Description: Sets SSL keys and certs for encrypted database connections
 * Author: Xiao Yu
 * Author URI: http://xyu.io/
 * Text Domain: secure-db-connection
 * Version: 1.1.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'lib/admin.php';
require_once 'lib/dropin.php';

class WP_SecureDBConnection {

	public static function load() {
		load_plugin_textdomain( 'secure-db-connection', false, 'secure-db-connection/languages' );
		add_action( 'admin_enqueue_scripts', array( self::_get_sdbc_admin(), 'admin_enqueue_scripts' ) );
		add_filter( 'dashboard_glance_items', array( self::_get_sdbc_admin(), 'dashboard_glance_items' ) );
	}

	public static function activation() {
		self::_get_sdbc_dropin()->install();
	}

	public static function deactivation() {
		self::_get_sdbc_dropin()->uninstall();
	}

	public static function uninstall() {
		self::_get_sdbc_dropin()->uninstall();
	}

	private static $_sdbc_admin;
	private static function _get_sdbc_admin() {
		if ( ! self::$_sdbc_admin instanceof WP_SecureDBConnection_Admin ) {
			global $wpdb;
			self::$_sdbc_admin = new WP_SecureDBConnection_Admin(
				$wpdb,
				self::_get_sdbc_dropin()
			);
		}
		return self::$_sdbc_admin;
	}

	private static $_sdbc_dropin;
	private static function _get_sdbc_dropin() {
		if ( ! self::$_sdbc_dropin instanceof WP_SecureDBConnection_DropIn ) {
			global $wpdb;
			global $wp_filesystem;
			self::$_sdbc_dropin = new WP_SecureDBConnection_DropIn(
				$wpdb,
				$wp_filesystem
			);
		}
		return self::$_sdbc_dropin;
	}
}

add_action( 'plugins_loaded', 'WP_SecureDBConnection::load' );

register_activation_hook(   __FILE__, 'WP_SecureDBConnection::activation'   );
register_deactivation_hook( __FILE__, 'WP_SecureDBConnection::deactivation' );
register_uninstall_hook(    __FILE__, 'WP_SecureDBConnection::uninstall'    );
