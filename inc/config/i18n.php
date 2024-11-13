<?php

namespace WP_PLUGIN\config;
class i18n {

	/**
	 * Get name of text domain in this plugin
	 * @var string
	 */
	public $text_domain;

	/**
	 * i18n constructor.
	 * @param $text_domain
	 */
	public function __construct( $text_domain ) {

		/*
		 * Check Use i18n in Plugin
		 */
		if ( \WP_PLUGIN::$use_i18n === false ) {
			return;
		}

		/*
		 * Set Text Domain name
		 */
		$this->text_domain = $text_domain;

		/*
		 * Load Plugin Text Domain
		 */
		add_action( 'after_setup_theme', array( $this, 'i18n' ) );
	}

	/**
	 * Loads translation file.
	 *
	 * Accessible to other classes to load different language files (admin and
	 * front-end for example).
	 *
	 * @wp-hook init
	 * @return  void
	 */
	public function i18n() {
		load_plugin_textdomain( $this->text_domain, false, wp_normalize_path( \WP_PLUGIN::$plugin_path . '/languages' ) );
	}

}
