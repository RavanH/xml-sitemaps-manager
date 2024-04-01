<?php
/**
 * XML Sitemaps Manager Admin class
 *
 * @package XML Sitemap Manager
 */

namespace XMLSitemapsManager;

/**
 * Sitemap admin.
 *
 * @since 0.1
 */
class Admin {

	/**
	 * Register settings.
	 *
	 * @since 0.1
	 */
	public static function register_settings() {
		// Field.
		\add_settings_field(
			'xml_sitemaps',
			\__( 'XML Sitemap' ),
			array( __CLASS__, 'sitemaps_settings_field' ),
			'reading'
		);

		// Help tab.
		\add_action(
			'load-options-reading.php',
			array( __CLASS__, 'sitemaps_help' )
		);

		// Don't register settings when blog not public.
		if ( 1 !== (int) \get_option( 'blog_public' ) ) {
			return;
		}

		if ( \get_option( 'xmlsm_sitemap_providers', array( 'posts', 'taxonomies', 'users' ) ) ) {
			// Settings.
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
		}

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
	 * @param array $save Checkbox options array.
	 *
	 * @return array Array containing only checked option names.
	 */
	public static function sanitize_checkbox_array_deep( $save ) {
		$sanitized_array = array();

		foreach ( (array) $save as $option => $value ) {
			if ( empty( $value ) ) {
				continue;
			}
			if ( is_array( $value ) ) {
				$sanitized_array[ $option ] = self::sanitize_checkbox_array_deep( $value );
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
	 * @param array $save Intval options array.
	 *
	 * @return array Array containing option name as key and integer as value.
	 */
	public static function sanitize_intval_array_deep( $save ) {
		$sanitized_array = array();

		foreach ( (array) $save as $option => $value ) {
			if ( is_array( $value ) ) {
				$sanitized_array[ $option ] = self::sanitize_intval_array_deep( $value );
			} else {
				$value = (int) $value;
				if ( ! empty( $value ) ) {
					$sanitized_array[ $option ] = $value;
				}
			}
		}

		return $sanitized_array;
	}

	/**
	 * Settings fields on Settings > Reading.
	 *
	 * @since 0.1
	 */
	public static function sitemaps_settings_field() {

		$sitemaps_fixes      = (bool) \get_option( 'xmlsm_sitemaps_fixes', true );
		$active_providers    = \get_option( 'xmlsm_sitemap_providers', array( 'posts', 'taxonomies', 'users' ) );
		$lastmod             = \get_option( 'xmlsm_lastmod', false );
		$max_urls            = \get_option( 'xmlsm_max_urls', false );
		$disabled_subtypes   = (array) \get_option( 'xmlsm_disabled_subtypes', array() );
		$provider_nice_names = array(
			'posts'      => __( 'Post types', 'xml-sitemaps-manager' ),
			'taxonomies' => __( 'Taxonomies', 'xml-sitemaps-manager' ),
			'users'      => __( 'Users' ),
		);

		// The actual fields for data entry.
		include __DIR__ . '/views/admin-fields.php';
	}

	/**
	 * Help tab Tools actions.
	 *
	 * @since 0.6
	 */
	public static function tools_actions() {
		if ( ! isset( $_POST['_xmlsm_help_nonce'] ) ) {
			return;
		}

		if ( ! \wp_verify_nonce( \sanitize_key( $_POST['_xmlsm_help_nonce'] ), XMLSM_BASENAME . '-help' ) ) {
			\add_settings_error(
				'not_allowed_notice',
				'not_allowed_notice',
				\__( 'Something went wrong.' ),
				'warning'
			);

			return;
		}

		/**
		 * Remove metadata.
		 */
		if ( isset( $_POST['xmlsm-clear-lastmod-meta'] ) ) {
			self::clear_lastmod_meta();

			\add_settings_error(
				'clear_meta_notice',
				'clear_meta_notice',
				\__( 'XML Sitemap lastmod meta caches have been cleared.', 'xml-sitemaps-manager' ),
				'updated'
			);
		}
	}

	/**
	 * Clear lastmod metadata.
	 *
	 * @since 0.7
	 */
	public static function clear_lastmod_meta() {
		// Terms meta.
		\delete_metadata( 'term', 0, 'term_modified_gmt', '', true );

		// User meta.
		\delete_metadata( 'user', 0, 'user_modified_gmt', '', true );

		\do_action( 'xmlsm_clear_lastmod_meta' );
	}

	/**
	 * Help tab sections on Settings > Reading.
	 *
	 * @since 0.1
	 */
	public static function sitemaps_help() {
		if ( 1 !== (int) \get_option( 'blog_public' ) ) {
			return;
		}

		\ob_start();
		include __DIR__ . '/views/admin-help-tab.php';
		$content = \ob_get_clean();

		\get_current_screen()->add_help_tab(
			array(
				'id'       => 'sitemap-settings',
				'title'    => \__( 'XML Sitemap' ),
				'content'  => $content,
				'priority' => 11,
			)
		);
	}

	/**
	 * Plugin action links.
	 *
	 * @since 0.1
	 *
	 * @param array $links Action links array.
	 *
	 * @return array $links
	 */
	public static function add_action_link( $links ) {
		$settings_link = '<a href="' . \admin_url( 'options-reading.php' ) . '#xml_sitemaps">' . \esc_html__( 'Settings', 'xml-sitemaps-manager' ) . '</a>';
		\array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Plugin meta links.
	 *
	 * @since 0.1
	 *
	 * @param array  $links Meta links array.
	 * @param string $file  Plugin file name.
	 *
	 * @return array $links
	 */
	public static function plugin_meta_links( $links, $file ) {
		if ( XMLSM_BASENAME === $file ) {
			$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/xml-sitemaps-manager/">' . \esc_html__( 'Support', 'xml-sitemaps-manager' ) . '</a>';
			$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/xml-sitemaps-manager/reviews/?filter=5#new-post">' . \esc_html__( 'Rate ★★★★★', 'xml-sitemaps-manager' ) . '</a>';
		}
		return $links;
	}

	/**
	 * Plugindeactivation.
	 *
	 * @since 0.7
	 *
	 * @param bool $network_deactivating Wheter the plugin is network deactivated or not.
	 */
	public static function deactivate( $network_deactivating = false ) {
		if ( $network_deactivating && ! \wp_is_large_network() ) {
			$_ids = get_sites(
				array(
					'fields' => 'ids',
					'number' => -1,
				)
			); // TEST number = -1..1000.

			foreach ( $_ids as $_id ) {
				\switch_to_blog( $_id );

				self::clear_lastmod_meta();

				\restore_current_blog();
			}
		} else {
			self::clear_lastmod_meta();
		}
	}
}
