<?php
/**
 * Plugin Name: Wp Plugin
 * Description: A Plugin For starter Wordpress
 * Plugin URI:  https://site.com
 * Version:     1.0.0
 * Author:      Mehrshad Darzi
 * Author URI:  https://site.com
 * License:     MIT
 * Text Domain: wp-plugin
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WP_PLUGIN {
	/**
	 * @var string
	 */
	public static $ENVIRONMENT = 'development';

	/**
	 * Use Template Engine
	 * if you want use template Engine Please add dir name
	 *
	 * @var string / dir name
	 * @status Core
	 */
	public static $Template_Engine = 'wp-plugin';

	/**
	 * Minimum PHP version required
	 *
	 * @var string
	 */
	private $min_php = '5.4.0';

	/**
	 * Use plugin's translated strings
	 *
	 * @var string
	 * @default true
	 */
	public static $use_i18n = true;

	/**
	 * List Of Class
	 * @var array
	 */
	public static $providers = array(
		'admin\Settings',
		'admin\Admin',
		'Front',
		'Ajax',
		'core\\Utility'
	);

	/**
	 * URL to this plugin's directory.
	 *
	 * @type string
	 * @status Core
	 */
	public static $plugin_url;

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 * @status Core
	 */
	public static $plugin_path;

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 * @status Core
	 */
	public static $plugin_version;

	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @status Core
	 */
	protected static $_instance = null;

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @return  object of this class
	 * @since   2012.09.13
	 */
	public static function instance() {
		null === self::$_instance and self::$_instance = new self;
		return self::$_instance;
	}

	/**
	 * WP_PLUGIN constructor.
	 */
	public function __construct() {

		/*
		 * Check Require Php Version
		 */
		if ( version_compare( PHP_VERSION, $this->min_php, '<=' ) ) {
			add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
			return;
		}

		/*
		 * Define Variable
		 */
		$this->define_constants();

		/*
		 * include files
		 */
		$this->includes();

		/*
		 * init Wordpress hook
		 */
		$this->init_hooks();

		/*
		 * Plugin Loaded Action
		 */
		do_action( 'wp_plugin_loaded' );
	}

	/**
	 * Define Constant
	 */
	public function define_constants() {

		/*
		 * Get Plugin Data
		 */
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( __FILE__ );

		/*
		 * Set Plugin Version
		 */
		self::$plugin_version = $plugin_data['Version'];

		/*
		 * Set Plugin Url
		 */
		self::$plugin_url = plugins_url( '', __FILE__ );

		/*
		 * Set Plugin Path
		 */
		self::$plugin_path = plugin_dir_path( __FILE__ );
	}

	/**
	 * include Plugin Require File
	 */
	public function includes() {

		/*
		 * autoload plugin files
		 */
		include_once dirname( __FILE__ ) . '/inc/config/i18n.php';
		include_once dirname( __FILE__ ) . '/inc/config/install.php';
		include_once dirname( __FILE__ ) . '/inc/config/uninstall.php';
		include_once dirname( __FILE__ ) . '/inc/helper.php';
		include_once dirname( __FILE__ ) . '/inc/ajax.php';
		include_once dirname( __FILE__ ) . '/inc/front.php';
		include_once dirname( __FILE__ ) . '/inc/admin/admin.php';
		include_once dirname( __FILE__ ) . '/inc/admin/settings.php';
		include_once dirname( __FILE__ ) . '/inc/core/debug.php';
		include_once dirname( __FILE__ ) . '/inc/core/settingapi.php';
		include_once dirname( __FILE__ ) . '/inc/core/template.php';
		include_once dirname( __FILE__ ) . '/inc/core/utility.php';
		include_once dirname( __FILE__ ) . '/inc/core/wp_mail.php';

		/*
		 * Load List Of classes
		 */
		foreach ( self::$providers as $class ) {
			$class_object = '\WP_PLUGIN\\' . $class;
			new $class_object;
		}

	}

	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook init Hook
	 * @return  void
	 */
	public function init_hooks() {

		/*
		 * Activation Plugin Hook
		 */
		register_activation_hook( __FILE__, array( '\WP_PLUGIN\config\install', 'run_install' ) );

		/*
		 * Uninstall Plugin Hook
		 */
		register_deactivation_hook( __FILE__, array( '\WP_PLUGIN\config\uninstall', 'run_uninstall' ) );

		/*
		 * Load i18n
		 */
		if ( self::$use_i18n === true ) {
			new \WP_PLUGIN\config\i18n( 'wp-plugin' );
		}

		//Check $ENVIRONMENT Mode
		if ( self::$ENVIRONMENT == "development" ) {
			new \WP_PLUGIN\core\debug();
		}

	}

	/**
	 * Show notice about PHP version
	 *
	 * @return void
	 */
	function php_version_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$error = __( 'Your installed PHP Version is: ', 'wp-plugin' ) . PHP_VERSION . '. ';
		$error .= __( 'The <strong>WP Plugin</strong> plugin requires PHP version <strong>', 'wp-plugin' ) . $this->min_php . __( '</strong> or greater.', 'wp-plugin' );
		?>
        <div class="error">
            <p><?php printf( $error ); ?></p>
        </div>
		<?php
	}

}

/**
 * Main instance of WP_Plugin.
 *
 * @since  1.1.0
 */
function wp_plugin() {
	return WP_PLUGIN::instance();
}

// Global for backwards compatibility.
$GLOBALS['wp-plugin'] = wp_plugin();
