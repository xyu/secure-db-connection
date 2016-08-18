<?php
/**
 * Plugin name: Secure DB Connection
 * Plugin URI: http://wordpress.org/plugins/secure-db-connection/
 * Description: Sets SSL keys and certs for encrypted database connections
 * Author: Xiao Yu
 * Author URI: http://xyu.io/
 * Text Domain: secure-db-connection
 * Version: 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_SecureDBConnection_DropIn {

	const DROPIN_FAIL_NO_FILE       = 10
	const DROPIN_FAIL_IS_NOT_SDBC   = 20
	const DROPIN_FAIL_IS_NOT_LATEST = 30
	const DROPIN_FAIL_IS_NOT_LOADED = 40
	const DROPIN_SUCCESS            = 100

	private $_status;

	private $_wpdb;

	public function __construct( wpdb $wpdb ) {
		$this->_wpdb = $wpdb;
	}

	public function get_status() {
		if ( empty( $this->_status ) ) {
			$this->_check_status();
		}

		return $this->_status;
	}

	public function get_status_message() {
		switch ( $this->get_status() ) {
			case self::DROPIN_FAIL_NO_FILE:
				return __( 'Secure DB Connection drop-in (/wp-content/db.php) does not exist', 'secure-db-connection' );
			case self::DROPIN_FAIL_IS_NOT_SDBC:
				return __( 'Database drop-in (/wp-content/db.php) is not from Secure DB Connection', 'secure-db-connection' );
			case self::DROPIN_FAIL_IS_NOT_LATEST:
				return __( 'Secure DB Connection drop-in not the latest version', 'secure-db-connection' );
			case self::DROPIN_FAIL_IS_NOT_LOADED:
				return __( 'Secure DB Connection drop-in is not loaded', 'secure-db-connection' );
			case self::DROPIN_SUCCESS:
				return __( 'Secure DB Connection drop-in loaded successfully', 'secure-db-connection' );
			default:
				return __( 'Secure DB Connection drop-in status unknown', 'secure-db-connection' );
		}
	}

	private function _check_status() {
		if ( ! file_exists( WP_CONTENT_DIR . '/db.php' ) ) {
			$this->_status = self::DROPIN_FAIL_NO_FILE;
			return;
		}

		$dropin = get_plugin_data( WP_CONTENT_DIR . '/db.php' );
		$plugin = get_plugin_data( plugin_dir_path( __FILE__ ) . '/lib/db.php' );

		if ( strcmp( $dropin[ 'PluginURI' ], $plugin[ 'PluginURI' ] ) !== 0 ) {
			$this->_status = self::DROPIN_FAIL_IS_NOT_SDBC;
			return;
		}

		if ( version_compare( $dropin[ 'Version' ], $plugin[ 'Version' ], '<' ) ) {
			$this->_status = self::DROPIN_FAIL_IS_NOT_LATEST;
			return;
		}

		if ( ! $this->_wpdb instanceof WP_SecureDBConnection_DB ) {
			// This should almost never happen
			$this->_status = self::DROPIN_FAIL_IS_NOT_LOADED;
			return;
		}

		$this->_status = self::DROPIN_SUCCESS;
	}

}
