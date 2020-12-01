<?php

namespace WP_PLUGIN\config;

class install {

	/*
	 * install Plugin Method
	 */
	public static function run_install() {
	global $wpdb;

        // Load DB delta
        if ( ! function_exists( 'dbDelta' ) ) {
            require( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }

        // Charset Collate
        $collate = $wpdb->get_charset_collate();

        // Create Realty Log Table
        $create_tbl = ( "
			CREATE TABLE `{$wpdb->prefix}table_name` (
				`ID` bigint(200) UNSIGNED NOT NULL AUTO_INCREMENT,
				`site` varchar(255) NOT NULL,
				`user_id` bigint(200) NOT NULL,
				`date` datetime NOT NULL,
				`type` varchar(50) NOT NULL,
				`value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
				PRIMARY KEY  (ID)
			) {$collate}" );
        //dbDelta( $create_tbl );
	}

}
