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

class WP_SecureDBConnection_Admin {

	private $_wpdb;
	private $_dropin;

	public function __construct( wpdb $wpdb, WP_SecureDBConnection_DropIn $dropin ) {
		$this->_wpdb = $wpdb;
		$this->_dropin = $dropin;
	}

	public function admin_enqueue_scripts( $hook_suffix ) {
		if ( "index.php" === $hook_suffix ) {
			$plugin = get_plugin_data( __FILE__ );
			wp_enqueue_style(
				'secure-db-connection',
				plugins_url( 'includes/admin-page.css', __DIR__ ),
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
					'<li class="%1$s"><span title="%2$s">%3$s</span></li>',
					'securedbconnection-nossl',
					esc_attr( sprintf(
						__( "Connection to MySQL is in plain text:\n%s", 'secure-db-connection' ),
						$this->_dropin->get_status_message()
					) ),
					esc_html__( 'MySQL Unencrypted', 'secure-db-connection' )
				);
			} else {
				printf(
					'<li class="%1$s"><span title="%2$s">%3$s</span></li>',
					'securedbconnection-ssl',
					esc_attr( sprintf(
						__( 'Connection to MySQL is SSL (%1$s) encrypted via %2$s', 'secure-db-connection' ),
						$status['ssl_version'],
						$status['ssl_cipher']
					) ),
					esc_html__( 'MySQL Secured', 'secure-db-connection' )
				);
			}
		}

		return $elements;
	}

	private function _get_conn_status() {
		$results = $this->_wpdb->get_results(
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
