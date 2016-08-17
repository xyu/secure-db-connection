<?php
/*
Plugin name: Secure DB Connection
Plugin URI: http://wordpress.org/plugins/secure-db-connection/
Description: Sets SSL keys and certs for encrypted database connections
Author: Xiao Yu
Author URI: http://xyu.io/
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_SecureDBConnection_Admin {

	public function __construct() {
		$this->init();
	}

	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'dashboard_glance_items', array( $this, 'dashboard_glance_items' ) );
	}

	public function admin_enqueue_scripts( $hook_suffix ) {
		if ( "index.php" === $hook_suffix ) {
			$plugin = get_plugin_data( __FILE__ );
			wp_enqueue_style(
				'secure-db-connection',
				plugin_dir_url( __FILE__ ) . 'includes/admin-page.css',
				null,
				$plugin[ 'Version' ]
			);
		}
	}

	/**
	 * Add to Dashboard At a Glance echo instead of return to hack in
	 * custom styles.
	 */
	public function dashboard_glance_items( $elements ) {
		if ( current_user_can( 'administrator' ) ) {
			$status = $this->_get_conn_status();

			if ( empty( $status['ssl_cipher'] ) ) {
				printf(
					'<li class="securedbconnection-nossl"><span title="%s">%s</span></li>',
					"Connection to MySQL is in plain text",
					'MySQL Unencrypted'
				);
			} else {
				printf(
					'<li class="securedbconnection-ssl"><span title="%s">%s</span></li>',
					"Connection to MySQL is SSL ({$status['ssl_version']}) encrypted via {$status['ssl_cipher']}",
					"MySQL Secured"
				);
			}
		}

		return $elements;
	}

	private function _get_conn_status() {
		global $wpdb;

		$results = $wpdb->get_results(
			"SHOW SESSION STATUS WHERE variable_name IN ( 'Ssl_cipher', 'Ssl_version' )"
		);

		$return = array();
		foreach ( $results as $row ) {
			$key = strtolower( $row->Variable_name );
			$return[ $key ] = $row->Value;
		}
		return $return;
	}

}
