<?php
namespace WP_PLUGIN\core;

class Debug {

	/**
	 * Global Request Key Name
	 * @var string
	 */
	public static $global_request = 'test';

	/**
	 * Debug constructor.
	 */
	public function __construct() {

		/*
		 * Email Send Debug
		 */
		add_action( 'phpmailer_init', array( $this, 'mailtrap' ) );

		/*
		 * Admin Area Test Method
		 */
		add_action( 'admin_init', array( $this, 'plugin_admin_test' ) );

		/*
		 * Wordpress Front Area Method
		 */
		add_action( 'wp', array( $this, 'plugin_front_test' ) );

		/*
		 * Wordpress Ajax Method
		 */
		add_action( 'wp_ajax_' . self::$global_request, array( $this, 'ajax' ) );
		add_action( 'wp_ajax_nopriv_' . self::$global_request, array( $this, 'ajax' ) );

		/*
		 * Wordpress Rest Api test
		 */
		add_action( 'rest_api_init', array( $this, 'init_rest_api' ) );
	}

	/**
	 * Pre Show Variable Debug
	 * @param $variable
	 * @param bool $exit
	 */
	public static function dump( $variable, $exit = true ) {
		echo '<div style="width: 95%; padding:5px 30px 30px; background: #f6f6f6; border-radius: 15px;">';
		echo '<pre style="font: 15px Trebuchet MS; line-height: 30px;">';
		if ( is_array( $variable ) ) {
			print_r( $variable );
		} else {
			var_dump( $variable );
		}
		echo '</pre>';
		echo '</div>';
		if ( $exit ) {
			exit;
		}
	}

	/**
	 * PHP Mail Test
	 * @param $phpmailer
	 */
	public function mailtrap( $phpmailer ) {
		$phpmailer->isSMTP();
		$phpmailer->Host     = 'smtp.mailtrap.io';
		$phpmailer->SMTPAuth = true;
		$phpmailer->Port     = 2525;
		$phpmailer->Username = '0b7c8032bb38c2';
		$phpmailer->Password = '9bc4ec76c04858';
	}

	/**
	 * Admin Area Test Code
	 */
	public function plugin_admin_test() {
		if ( isset( $_REQUEST[ self::$global_request ] ) ) {
			exit;
		}
	}

	/**
	 * Front Area Test
	 */
	public function plugin_front_test() {
		if ( isset( $_REQUEST[ self::$global_request ] ) ) {

			exit;
		}
	}

	/**
	 * Ajax Test Method
	 */
	public function ajax() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_send_json( array( 'status' => '200' ) );

		}
		die();
	}

	/**
	 * Wordpress Rest Api Test Debug
	 */
	public function init_rest_api() {
		register_rest_route( 'method', '/' . self::$global_request . '/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'rest_api_request' ),
			'permission_callback' => '__return_true'
		) );
	}

	/**
	 * Rest Api Request
	 *
	 * @param  $request
	 * @see https://v2.wp-api.org/extending/adding/
	 */
	public function rest_api_request( \WP_REST_Request $request ) {
			// get Custom Param
			//$param = $request->get_param( 'some_param' );

			//Error Response
			//return new \WP_Error( 'error', 'Your license is not valid!', array( 'status' => 404 ) );

			//Success Response
			//new \WP_REST_Response( $result, 200 );
	}
}
