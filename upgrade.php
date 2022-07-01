<?php
/*
 * WP Sitemaps Manager upgrade routines
 *
 * @since 0.1
 */
class WP_Sitemaps_Manager_Upgrade {

	/**
	 * Default settings.
	 *
	 * @since 0.1
	 *
	 * @var array Array of options and their default setting.
	 */
	private $options = array(
		'xmlsm_sitemaps_enabled'   => true,
		'xmlsm_sitemaps_fixes'     => true,
		'xmlsm_max_urls'           => '',
		'xmlsm_lastmod'            => false,
		'xmlsm_sitemap_providers'  => array( 'posts', 'taxonomies', 'users' ),
		'xmlsm_disabled_subtypes'  => '',
	);

	/**
	 * Constructor: manages upgrade.
	 *
	 * @since 0.1
	 */
	function __construct( $db_version = null )
	{
		if ( $db_version )
			$this->upgrade( $db_version );
		else
			$this->install();

		update_option( 'xmlsm_version', WPSM_VERSION );
	}

	/**
	 * Set up default plugin data.
	 *
	 * @since 0.1
	 */
	private function install()
	{
		// Make sure to start with fresh defaults.
		foreach ( $this->options as $option => $default ) {
			delete_option( $option );
			add_option( $option, $default );
		}

		// Kilroy was here.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WP Sitemaps Manager version '.WPSM_VERSION.' installed.' );
		}
	}

	/**
	 * upgrade plugin data.
	 *
	 * @since 0.1
	 */
	private function upgrade( $db_version )
	{
		if ( version_compare( '0.2', $db_version, '>=' ) ) {
			// Max urls option.
			$max_urls = get_option( 'xmlsm_sitemaps_max_urls', '' );
			if ( is_array( $max_urls ) && ! empty( $max_urls['post'] ) ) {
				$max_urls = $max_urls['post'];
			} else {
				$max_urls = '';
			}
			add_option( 'xmlsm_max_urls', $max_urls );
			delete_option( 'xmlsm_sitemaps_max_urls' );

			// Lastmod option.
			$lastmod = get_option( 'xmlsm_sitemaps_lastmod' );
			if ( is_array( $lastmod ) && ! empty( $lastmod ) ) {
				$lastmod = true;
			}
			add_option( 'xmlsm_lastmod', $lastmod );
			delete_option( 'xmlsm_sitemaps_lastmod' );
		}

		// Fill in missing options.
		foreach ( $this->options as $option => $default ) {
			add_option( $option, $default );
		}

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log( 'WP Sitemaps Manager upgraded from '.$db_version.' to '.WPSM_VERSION );
		}
	}

}
