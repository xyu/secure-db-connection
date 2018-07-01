=== Secure DB Connection ===
Contributors: HypertextRanch
Tags: db, mysql, secure, encrypted, ssl
Requires at least: 3.9
Tested up to: 4.8.2
Stable tag: 1.1.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Sets SSL keys and certs for encrypted MySQL database connections.

== Description ==

Depending on the MySQL server setup the SSL certs used may not be in the trusted store, if that's the case `mysqli_ssl_set()` needs to be called to set custom keys and certs before connect. This Plugin adds a custom DB class that allows these settings to be configured via custom constants.

This plugin will also add a custom item on the "At a Glance" section of the Dashboard to show if the `$wpdb` connection is secure or not.

Also find me on [GitHub](https://github.com/xyu/secure-db-connection).

= Configuration Parameters =

To adjust the configuration, define any of the following applicable constants in your `wp-config.php` file.

  * `MYSQL_SSL_KEY` [default: not set]

    The path name to the key file. (RSA Key)

  * `MYSQL_SSL_CERT` [default: not set]

    The path name to the certificate file.

  * `MYSQL_SSL_CA` [default: not set]

    The path name to the certificate authority file.

  * `MYSQL_SSL_CA_PATH` [default: not set]

    The pathname to a directory that contains trusted SSL CA certificates in PEM format.

  * `MYSQL_SSL_CIPHER` [default: not set]

    A list of allowable ciphers to use for SSL encryption

= Turning on SSL =

Once SSL keys and certs have been configured you via the defines above define an WP core constant to pass a use SSL flag to the mysqli client also in your `wp-config.php` file.

    define( 'MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_SSL );

If you are using the MySQL Native Driver and MySQL 5.6 or later `mysqli_real_connect()` will verify the server SSL certificate before connecting. If the SSL cert installed on the MySQL server your are connecting to is not valid PHP will refuse to connect. A flag was added to disable server certificate validation. If your server has an invalid certificate turn on SSL and turn off validation like so:

    define( 'MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT );

== Installation ==

For detailed installation instructions, please read the [standard installation procedure for WordPress plugins](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

1. Install and activate plugin, if the filesystem is writable the Secure DB Connection dropin will be installed for you automatically. If not proceed to step 2
2. Symlink or copy the `db.php` file from the `/wp-content/plugins/secure-db-connection/lib/` directory to the `/wp-content/` directory.
3. Set the relevant defines in your `wp-config.php` file.

== Screenshots ==

1. An at a glance item is added showing the status of the MySQL connection when this plugin is activated. If the connection is encrypted the SSL version and cipher used will also be shown.

== Changelog ==

= 1.1.4 =

  * Update version numbers in file headers.

= 1.1.3 =

  * Better PHP backwards compatibility.

= 1.1.2 =

  * Only set MySQL SSL opts if secure connections are requested.

= 1.1.1 =

  * Retag release to fix version inconsistency

= 1.1.0 =

  * Fix status message for when DB connection is not SSL enabled
  * Automatically install and remove db.php dropin on activate / deactivate
  * Check and report status of dropin
  * Add i18n support

= 1.0 =

  * Initial release
