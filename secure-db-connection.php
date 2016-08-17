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

require_once plugin_dir_url( __FILE__ ) . 'lib/admin.php';

new WP_SecureDBConnection_Admin();
