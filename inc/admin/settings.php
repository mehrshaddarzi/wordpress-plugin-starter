<?php

namespace WP_PLUGIN\admin;
use WP_PLUGIN\core\SettingAPI;

/**
 * Class Settings
 * @see https://github.com/tareq1988/wordpress-settings-api-class
 */
class Settings {

	/**
	 * Plugin Option name
	 */
	public $setting;

	/**
	 * The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Main Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Admin_Setting_Api constructor.
	 */
	public function __construct() {
		/**
		 * Set Admin Setting
		 */
		add_action( 'admin_init', array( $this, 'init_option' ) );
	}

	/**
	 * Display the plugin settings options page
	 */
	public function setting_page() {

		echo '<div class="wrap">';
		settings_errors();

		$this->setting->show_navigation();
		$this->setting->show_forms();

		echo '</div>';
	}

	/**
	 * Registers settings section and fields
	 */
	public function init_option() {
		$sections = array(
			array(
				'id'    => 'wp_plugin_email_opt',
				'desc'  => __( 'Basic email settings', 'wp-reviews-insurance' ),
				'title' => __( 'Email', 'wp-reviews-insurance' )
			),
			array(
				'id'    => 'WP_PLUGIN_opt',
				'title' => __( 'General', 'wp-reviews-insurance' )
			),
			array(
				'id'    => 'wp_plugin_help',
				'title' => __( 'Help', 'wp-reviews-insurance' ),
				'save'  => false
			),
		);

		$fields = array(
			'wp_plugin_email_opt'     => array(
				array(
					'name'    => 'from_email',
					'label'   => __( 'From Email', 'wp-reviews-insurance' ),
					'type'    => 'text',
					'default' => get_option( 'admin_email' )
				),
				array(
					'name'    => 'from_name',
					'label'   => __( 'From Name', 'wp-reviews-insurance' ),
					'type'    => 'text',
					'default' => get_option( 'blogname' )
				),
				array(
					'name'         => 'email_logo',
					'label'        => __( 'Email Logo', 'wp-reviews-insurance' ),
					'type'         => 'file',
					'button_label' => 'choose logo image'
				),
				array(
					'name'    => 'email_body',
					'label'   => __( 'Email Body', 'wp-reviews-insurance' ),
					'type'    => 'wysiwyg',
					'default' => '<p>Hi, [fullname] </p> For Accept Your Reviews Please Click Bottom Link : <p> [link]</p>',
					'desc'    => 'Use This Shortcode :<br /> [fullname] : User Name <br /> [link] : Accept email link'
				),
				array(
					'name'    => 'email_footer',
					'label'   => __( 'Email Footer Text', 'wp-reviews-insurance' ),
					'type'    => 'wysiwyg',
					'default' => 'All rights reserved',
				)
			),
			'WP_PLUGIN_opt' => array(
				array(
					'name'    => 'is_auth_ip',
					'label'   => __( 'IP Validation', 'wp-reviews-insurance' ),
					'type'    => 'select',
					'desc'    => 'Each user can only have one vote',
					'options' => array(
						'0' => 'No',
						'1' => 'yes'
					)
				),
				array(
					'name'    => 'email_auth',
					'label'   => __( 'Confirmation email', 'wp-reviews-insurance' ),
					'type'    => 'select',
					'desc'    => 'The user must click confirmation email',
					'options' => array(
						'0' => 'No',
						'1' => 'yes'
					)
				),
				array(
					'name'    => 'star_color',
					'label'   => __( 'Star Rating color', 'wp-reviews-insurance' ),
					'type'    => 'color',
					'default' => '#f2b01e'
				),
				array(
					'name'    => 'thanks_text',
					'label'   => __( 'Thanks you Text', 'wp-reviews-insurance' ),
					'type'    => 'wysiwyg',
					'default' => 'Thanks you for this vote.'
				),
				array(
					'name'    => 'error_ip',
					'label'   => __( 'Duplicate ip error', 'wp-reviews-insurance' ),
					'type'    => 'textarea',
					'default' => 'Each user can only have one vote'
				),
				array(
					'name'    => 'email_subject',
					'label'   => __( 'Email subject for Confirm', 'wp-reviews-insurance' ),
					'type'    => 'text',
					'default' => 'confirm your reviews',
					'desc'    => 'Use This Shortcode :</br> [fullname] : User Name<br /> [sitename] : Site Name',
				),
				array(
					'name'    => 'email_thanks_text',
					'label'   => __( 'Thanks Confirm Text', 'wp-reviews-insurance' ),
					'type'    => 'text',
					'default' => 'Thank You For Your Reviews.',
				),
			),
			'wp_plugin_help'           => array(
				array(
					'name'  => 'html_help_shortcode',
					'label' => 'ShortCode List',
					'desc'  => 'You Can using bottom shortcode in wordpress : <br /><br />
 <table border="0" class="widefat">
  <tr>
 <td> [reviews-form]</td>
 <td>For Show Review Form</td>
</tr>
 <tr>
 <td>[reviews-insurance]</td>
 <td>List Of insurance With Rating Averag e.g : [reviews-insurance order="DESC"]</td>
</tr>
<tr>
 <td>[reviews-list]</td>
 <td>List Of Review For Custom insurance . e.g : [reviews-list insurance_id=10 order="ASC" number="50"]</td>
</tr>
</table>
',
					'type'  => 'html'
				),
				array(
					'name'  => 'html_help_custom template',
					'label' => 'Custom Template',
					'desc'  => 'for Custom Template according to your theme style : <br /> <br />
 <table border="0" class="widefat">
  <tr>
  <td>Copy `template` folder to root dir theme and rename folder to `wp-reviews`. then change your html code. :)</td>
  </tr>
  </table>
',
					'type'  => 'html'
				),
			)
		);

		$this->setting = new SettingAPI();

		//set sections and fields
		$this->setting->set_sections( $sections );
		$this->setting->set_fields( $fields );

		//initialize them
		$this->setting->admin_init();
	}

}