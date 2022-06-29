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
		'wpsm_sitemaps_enabled'   => true,
		'wpsm_sitemaps_fixes'     => true,
		'wpsm_sitemaps_max_urls'  => 2000,
		'wpsm_sitemaps_lastmod'   => false,
		'wpsm_sitemap_providers'  => array( 'posts', 'taxonomies', 'users' ),
		'wpsm_disabled_subtypes'  => '',
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

		update_option( 'wpsm_version', WPSM_VERSION );
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
		//if ( version_compare( '0.1', $db_version, '>' ) ) {
		//}

		// Fill in missing options.
		foreach ( $this->options as $option => $default ) {
			add_option( $option, $default );
		}

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log( 'WP Sitemaps Manager upgraded from '.$db_version.' to '.WPSM_VERSION );
		}
	}

}
