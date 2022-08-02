<?php
/**
 * XML Sitemaps Manager Admin class
 *
 * @package XML Sitemap Manager
 * @since 0.1
 */

class XML_Sitemaps_Manager_Admin
{
	/**
	 * class XML_Sitemaps_Manager_Admin constructor
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
		// Field.
		add_settings_field(
			'xml_sitemaps',
			translate( 'XML Sitemap' ),
			array( $this, 'sitemaps_settings_field' ),
			'reading'
		);

		// Help tab.
		add_action(
			'load-options-reading.php',
			array( $this, 'sitemaps_help' )
		);

		// Don't register settings when blog not public.
		if ( '1' !== get_option( 'blog_public' ) ) {
			return;
		}

		// Settings.
		register_setting(
			'reading',
			'xmlsm_sitemaps_enabled',
			'boolval'
		);

		register_setting(
			'reading',
			'xmlsm_sitemaps_fixes',
			'boolval'
		);
		register_setting(
			'reading',
			'xmlsm_lastmod',
			'boolval'
		);
		register_setting(
			'reading',
			'xmlsm_max_urls',
			'intval'
		);
		register_setting(
			'reading',
			'xmlsm_sitemap_providers',
			array( $this, 'sanitize_checkbox_array_deep' )
		);
		register_setting(
			'reading',
			'xmlsm_disabled_subtypes',
			array( $this, 'sanitize_checkbox_array_deep' )
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
		if ( '1' !== get_option( 'blog_public' ) ) {
			esc_html_e( 'The XML Sitemap is disabled because of your site&#8217;s visibility settings (above).', 'xml-sitemaps-manager' );
			return;
		}

		$sitemaps_enabled  = (bool)  get_option( 'xmlsm_sitemaps_enabled',  true    );
		$sitemaps_fixes    = (bool)  get_option( 'xmlsm_sitemaps_fixes',    true    );
		$sitemap_providers = (array) get_option( 'xmlsm_sitemap_providers', array( 'posts', 'taxonomies', 'users' ) );
		$lastmod           =         get_option( 'xmlsm_lastmod',           false   );
		$max_urls          =         get_option( 'xmlsm_max_urls',          false   );
		$disabled_subtypes = (array) get_option( 'xmlsm_disabled_subtypes', array() );
		$provider_nice_names = array(
			'posts'      => translate( 'Post types' ),
			'taxonomies' => translate( 'Taxonomies' ),
			'users'      => translate( 'Users' ),
		);

		// The actual fields for data entry
		include WPSM_DIR . '/includes/views/admin-field.php';
	}

	/**
	 * Help tab sections on Settings > Reading.
	 *
	 * @since 0.1
	 */
	public function sitemaps_help()
	{
		if ( '1' !== get_option( 'blog_public' ) ) {
			return;
		}

		ob_start();
		include WPSM_DIR . '/includes/views/admin-help-tab.php';
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
		$settings_link = '<a href="' . admin_url( 'options-reading.php' ) . '#xml_sitemaps">' . esc_html( translate( 'Settings' ) ) . '</a>';
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
			$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/xml-sitemaps-manager/">' .  esc_html__( 'Support', 'xml-sitemaps-manager' ) . '</a>';
			$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/xml-sitemaps-manager/reviews/?filter=5#new-post">' .  esc_html__( 'Rate ★★★★★', 'xml-sitemaps-manager' ) . '</a>';
		}
		return $links;
	}

}
