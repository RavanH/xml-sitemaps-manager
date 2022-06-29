<?php
/**
 * WP_Sitemaps_Manager_Admin class
 *
 * @package WP Sitemap Manager
 * @since 0.1
 */

class WP_Sitemaps_Manager_Admin
{
	/**
	 * class WP_Sitemaps_Manager_Admin constructor
	 *
	 * @since 0.1
	 */
	function __construct()
	{
		/**
		 * Register settings.
		 */
		add_action( 'admin_init', array( $this, 'register_settings' ), 0 );

		/**
		 * Plugin action links.
		 */
		add_filter( 'plugin_action_links_' . WPSM_BASENAME, array( $this, 'add_action_link' )         );
		add_filter( 'plugin_row_meta',                      array( $this, 'plugin_meta_links' ), 10, 2);
	}

	/**
	 * Register settings.
	 *
	 * @since 0.1
	 */
	public function register_settings()
	{
		// Settings.
		register_setting(
			'reading',
			'wpsm_sitemaps_enabled',
			'boolval'
		);
		register_setting(
			'reading',
			'wpsm_sitemaps_fixes',
			'boolval'
		);
		register_setting(
			'reading',
			'wpsm_sitemaps_lastmod',
			array( $this, 'sanitize_checkbox_array_deep' )
		);
		register_setting(
			'reading',
			'wpsm_sitemaps_max_urls',
			array( $this, 'sanitize_intval_array_deep' )
		);
		register_setting(
			'reading',
			'wpsm_sitemap_providers',
			array( $this, 'sanitize_checkbox_array_deep' )
		);
		register_setting(
			'reading',
			'wpsm_disabled_subtypes',
			array( $this, 'sanitize_checkbox_array_deep' )
		);

		// Field.
		add_settings_field(
			'wpsm_sitemaps',
			translate( 'XML Sitemap' ),
			array( $this, 'sitemaps_settings_field' ),
			'reading'
		);

		// Help tab.
		add_action(
			'load-options-reading.php',
			array( $this, 'sitemaps_help' )
		);
	}

	/**
	 * Deep sanitize array of checkbox options.
	 *
	 * @since 0.1
	 *
	 * @param array Checkbox options array
	 *
	 * @return array Array containing only checked option names
	 */
	public function sanitize_checkbox_array_deep( $new )
	{
		$sanitized_array = array();

		foreach ( (array) $new as $option => $value ) {
			if ( empty( $value ) ) {
				continue;
			}
			if ( is_array( $value ) ) {
				$sanitized_array[$option] = $this->sanitize_checkbox_array_deep( $value );
			} else {
				$sanitized_array[] = $option;
			}
		}

		return ! empty( $sanitized_array ) ? $sanitized_array : '';
	}

	/**
	 * Sanitize array of intval options.
	 *
	 * @since 0.1
	 *
	 * @param array Intval options array
	 *
	 * @return array Array containing option name as key and integer as value
	 */
	public function sanitize_intval_array_deep( $new )
	{
		$sanitized_array = array();

		foreach ( (array) $new as $option => $value ) {
			if ( is_array( $value ) ) {
				$sanitized_array[$option] = $this->sanitize_intval_array_deep( $value );
			} else {
				$value = (int) $value;
				if ( ! empty( $value ) ) {
					$sanitized_array[$option] = $value;
				}
			}
		}

		return $sanitized_array;
	}

	/**
	 * Help tab sections on Settings > Reading.
	 *
	 * @since 0.1
	 */
	public function sitemaps_settings_field()
	{
		if ( '1' !== get_option('blog_public') ) {
			esc_html_e( 'The XML Sitemap is disabled because of your site&#8217;s visibility settings (above).', 'wp-sitemaps-manager' );
			return;
		}

		$wpsm_sitemaps_enabled  = (bool)  get_option( 'wpsm_sitemaps_enabled',  true );
		$wpsm_sitemaps_fixes    = (bool)  get_option( 'wpsm_sitemaps_fixes',    true );
		$wpsm_sitemap_providers = (array) get_option( 'wpsm_sitemap_providers', array( 'posts', 'taxonomies', 'users' ) );
		$wpsm_sitemaps_lastmod  = (array) get_option( 'wpsm_sitemaps_lastmod',  array() );
		$wpsm_sitemaps_max_urls = (array) get_option( 'wpsm_sitemaps_max_urls', array() );
		$wpsm_disabled_subtypes = (array) get_option( 'wpsm_disabled_subtypes', array() );
		$provider_names = array(
			'posts'      => translate( 'Post types' ),
			'taxonomies' => translate( 'Taxonomies' ),
			'users'      => translate( 'Users' ),
		);
		$provider_object_types = array(
			'posts'      => 'post',
			'taxonomies' => 'term',
			'users'      => 'user',
		);

		// The actual fields for data entry
		include WPSM_DIR . '/includes/views/admin-field-reading.php';
	}

	/**
	 * Help tab sections on Settings > Reading.
	 *
	 * @since 0.1
	 */
	public function sitemaps_help()
	{
		if ( '1' !== get_option('blog_public') ) {
			return;
		}

		ob_start();
		include WPSM_DIR . '/includes/views/admin-help-tab-reading.php';
		include WPSM_DIR . '/includes/views/admin-help-tab-support.php';
		$content = ob_get_clean();

		get_current_screen()->add_help_tab(
			array(
				'id'      => 'sitemap-settings',
				'title'   => translate( 'XML Sitemap' ),
				'content' => $content,
				'priority' => 11
			)
		);
	}

	/**
	 * Plugin action links.
	 *
	 * @since 0.1
	 */
	public function add_action_link( $links ) {
		$settings_link = '<a href="' . admin_url( 'options-reading.php' ) . '#wpsm_sitemaps">' . esc_html( translate( 'Settings' ) ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Plugin meta links.
	 *
	 * @since 0.1
	 */
	public function plugin_meta_links( $links, $file ) {
		if ( $file == WPSM_BASENAME ) {
			$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/wp-sitemaps-manager/">' .  esc_html__( 'Support', 'wp-sitemaps-manager' ) . '</a>';
			$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/wp-sitemaps-manager/reviews/?filter=5#new-post">' .  esc_html__( 'Rate ★★★★★', 'wp-sitemaps-manager' ) . '</a>';
		}
		return $links;
	}

}
