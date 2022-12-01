<?php
/**
 * XML Sitemaps Manager Admin class
 *
 * @package XML Sitemap Manager
 * @since 0.1
 */

namespace XMLSitemapsManager;

class Admin
{
	/**
	 * class XML_Sitemaps_Manager_Admin constructor.
	 *
	 * @since 0.1
	 */
	function __construct() { }

	/**
	 * Register settings.
	 *
	 * @since 0.1
	 */
	public static function register_settings()
	{
		// Field.
		\add_settings_field(
			'xml_sitemaps',
			\translate( 'XML Sitemap' ),
			array( __CLASS__, 'sitemaps_settings_field' ),
			'reading'
		);

		// Help tab.
		\add_action(
			'load-options-reading.php',
			array( __CLASS__, 'sitemaps_help' )
		);

		// Don't register settings when blog not public.
		if ( '1' !== \get_option( 'blog_public' ) ) {
			return;
		}

		// Settings.
		\register_setting(
			'reading',
			'xmlsm_sitemaps_enabled',
			'boolval'
		);

		\register_setting(
			'reading',
			'xmlsm_sitemaps_fixes',
			'boolval'
		);
		\register_setting(
			'reading',
			'xmlsm_lastmod',
			'boolval'
		);
		\register_setting(
			'reading',
			'xmlsm_max_urls',
			'intval'
		);
		\register_setting(
			'reading',
			'xmlsm_sitemap_providers',
			array( __CLASS__, 'sanitize_checkbox_array_deep' )
		);
		\register_setting(
			'reading',
			'xmlsm_disabled_subtypes',
			array( __CLASS__, 'sanitize_checkbox_array_deep' )
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
	public static function sanitize_checkbox_array_deep( $new )
	{
		$sanitized_array = array();

		foreach ( (array) $new as $option => $value ) {
			if ( empty( $value ) ) {
				continue;
			}
			if ( is_array( $value ) ) {
				$sanitized_array[$option] = self::sanitize_checkbox_array_deep( $value );
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
	public static function sanitize_intval_array_deep( $new )
	{
		$sanitized_array = array();

		foreach ( (array) $new as $option => $value ) {
			if ( is_array( $value ) ) {
				$sanitized_array[$option] = self::sanitize_intval_array_deep( $value );
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
	public static function sitemaps_settings_field()
	{
		if ( '1' !== get_option( 'blog_public' ) ) {
			\esc_html_e( 'The XML Sitemap is disabled because of your site&#8217;s visibility settings (above).', 'xml-sitemaps-manager' );
			return;
		}

		$sitemaps_enabled  = (bool)  \get_option( 'xmlsm_sitemaps_enabled',  true    );
		$sitemaps_fixes    = (bool)  \get_option( 'xmlsm_sitemaps_fixes',    true    );
		$sitemap_providers = (array) \get_option( 'xmlsm_sitemap_providers', array( 'posts', 'taxonomies', 'users' ) );
		$lastmod           =         \get_option( 'xmlsm_lastmod',           false   );
		$max_urls          =         \get_option( 'xmlsm_max_urls',          false   );
		$disabled_subtypes = (array) \get_option( 'xmlsm_disabled_subtypes', array() );
		$provider_nice_names = array(
			'posts'      => __( 'Post types', 'xml-sitemaps-manager' ),
			'taxonomies' => __( 'Taxonomies', 'xml-sitemaps-manager' ),
			'users'      => __( 'Users', 'xml-sitemaps-manager' ),
		);

		// The actual fields for data entry
		include __DIR__ . '/views/admin-field.php';
	}

	/**
	 * Help tab sections on Settings > Reading.
	 *
	 * @since 0.1
	 */
	public static function sitemaps_help()
	{
		if ( '1' !== \get_option( 'blog_public' ) ) {
			return;
		}

		\ob_start();
		include __DIR__ . '/views/admin-help-tab.php';
		$content = \ob_get_clean();

		\get_current_screen()->add_help_tab(
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
	public static function add_action_link( $links ) {
		$settings_link = '<a href="' . \admin_url( 'options-reading.php' ) . '#xml_sitemaps">' . \esc_html( \translate( 'Settings' ) ) . '</a>';
		\array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Plugin meta links.
	 *
	 * @since 0.1
	 */
	public static function plugin_meta_links( $links, $file ) {
		if ( $file == WPSM_BASENAME ) {
			$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/xml-sitemaps-manager/">' .  \esc_html__( 'Support', 'xml-sitemaps-manager' ) . '</a>';
			$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/xml-sitemaps-manager/reviews/?filter=5#new-post">' .  \esc_html__( 'Rate ★★★★★', 'xml-sitemaps-manager' ) . '</a>';
		}
		return $links;
	}

}
