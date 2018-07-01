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

class WP_SecureDBConnection_DropIn {

	const DROPIN_FAIL_NO_FILE       = 10;
	const DROPIN_FAIL_IS_NOT_SDBC   = 20;
	const DROPIN_FAIL_IS_NOT_LATEST = 30;
	const DROPIN_FAIL_IS_NOT_LOADED = 40;
	const DROPIN_SUCCESS            = 90;

	private $_status;
	private $_path_dropin;
	private $_path_plugin;

	private $_wpdb;
	private $_wpfs;

	private function _set_property_defaults() {
		$this->_path_dropin = WP_CONTENT_DIR . '/db.php';
		$this->_path_plugin = __DIR__ . '/db.php';
	}

	public function __construct( wpdb $wpdb, &$wp_filesystem ) {
		$this->_set_property_defaults();
		$this->_wpdb = $wpdb;
		$this->_wpfs = &$wp_filesystem;
	}

	public function install() {
		switch ( $this->get_status() ) {
			case self::DROPIN_FAIL_NO_FILE:       // Can install new dropin
			case self::DROPIN_FAIL_IS_NOT_LATEST: // Can upgrade existing
				//TODO: Move Drop-in into place
				if ( $this->_initialize_fs() ) {
					$this->_wpfs->copy(
						$this->_path_plugin,
						$this->_path_dropin,
						true
					);
				}
				return;
			case self::DROPIN_SUCCESS:            // Already installed
				return true;
			case self::DROPIN_FAIL_IS_NOT_SDBC:   // Don't overwrite another dropin
			case self::DROPIN_FAIL_IS_NOT_LOADED: // Dropin not loading abort!
			default:
				return false;
		}
	}

	public function uninstall() {
		switch ( $this->get_status() ) {
			case self::DROPIN_SUCCESS:            // Can remove installed
			case self::DROPIN_FAIL_IS_NOT_LATEST: // Can remove older existing
			case self::DROPIN_FAIL_IS_NOT_LOADED: // Dropin not loading but remove anyway
				//TODO: Remove Drop-in
				if ( $this->_initialize_fs() ) {
					$this->_wpfs->delete(
						$this->_path_dropin
					);
				}
				return;
			case self::DROPIN_FAIL_NO_FILE:       // No dropin
				return true;
			case self::DROPIN_FAIL_IS_NOT_SDBC:   // Don't remove another dropin
			default:
				return false;
		}
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
		if ( ! file_exists( $this->_path_dropin ) ) {
			$this->_status = self::DROPIN_FAIL_NO_FILE;
			return;
		}

		$dropin = get_plugin_data( $this->_path_dropin );
		$plugin = get_plugin_data( $this->_path_plugin );

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

	private function _initialize_fs() {
		if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
			return false;
		}

		if ( $this->_wpfs instanceof WP_Filesystem ) {
			return true;
		}

		if ( 'direct' !== get_filesystem_method() ) {
			return false;
		}

		// Because we have a 'direct' method we should not need to ask via
		// HTML form for creds from the user but use output buffering just
		// in case.
		ob_start();
		$wpfs_ok = WP_Filesystem( request_filesystem_credentials( '' ) );
		ob_end_clean();

		return ( bool ) $wpfs_ok;
	}

}
