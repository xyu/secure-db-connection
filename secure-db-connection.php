<?php
/**
 * Plugin name: Secure DB Connection
 * Plugin URI: http://wordpress.org/plugins/secure-db-connection/
 * Description: Sets SSL keys and certs for encrypted database connections
 * Author: Xiao Yu
 * Author URI: http://xyu.io/
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_url( __FILE__ ) . 'lib/admin.php';
require_once plugin_dir_url( __FILE__ ) . 'lib/dropin.php';

class WP_SecureDBConnection {

	public static function load() {
		add_action( 'admin_enqueue_scripts', array( self::_get_sdbc_admin(), 'admin_enqueue_scripts' ) );
		add_filter( 'dashboard_glance_items', array( self::_get_sdbc_admin(), 'dashboard_glance_items' ) );
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
			self::$_sdbc_dropin = new WP_SecureDBConnection_DropIn(
				$wpdb
			);
		}
		return self::$_sdbc_dropin;
	}
}

add_action( 'plugins_loaded', 'WP_SecureDBConnection::load' );
