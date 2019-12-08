<?php
namespace WP_PLUGIN\core;

/**
 * Wordpress Template System For Use in Plugins
 */
class Template {

	/**
	 * Create instance
	 * @var null
	 */
	protected static $_instance = NULL;

	/**
	 * Singleton class instance.
	 */
	public static function get() {
		if ( NULL === self::$_instance )
			self::$_instance = new self;
		return self::$_instance;
	}

	/*
	 * Set Template Show List
	 */
	function shortlink_locate_template( $template_name, $template_path = '', $default_path = '' ) {

		// Set variable to search in betterstudio folder of theme.
		if ( ! $template_path ) :
			$template_path = 'wp-reviews/';
		endif;

		// Set default plugin templates path.
		if ( ! $default_path ) :
			$default_path = \WP_PLUGIN::$plugin_path . 'template/'; // Path to the template folder
		endif;

		// Search template file in theme folder.
		$template = locate_template( array(
			$template_path . $template_name,
			$template_name
		) );

		// Get plugins template file.
		if ( ! $template ) :
			$template = $default_path . $template_name;
		endif;

		return apply_filters( 'shortlink_locate_template', $template, $template_name, $template_path, $default_path );
	}


	/**
	 * Get Template File
	 *
	 * @param $template_name
	 * @param array $args
	 * @param string $tempate_path
	 * @param string $default_path
	 * @return string included file
	 */
	function shortlink_get_template( $template_name, $args = array(), $tempate_path = '', $default_path = '' ) {

		if ( is_array( $args ) && isset( $args ) ) :
			extract( $args );
		endif;

		$template_file = $this->shortlink_locate_template( $template_name, $tempate_path, $default_path );

		if ( ! file_exists( $template_file ) ) :
			_doing_it_wrong( __FUNCTION__, __("Template Shortlink Not Found", 'wp-reviews-insurance') , '1.0.0' );
			return;
		endif;

		include $template_file;
	}

}