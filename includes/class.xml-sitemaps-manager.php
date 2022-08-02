<?php
/**
 * XML_Sitemaps_Manager class
 *
 * @package XML Sitemap Manager
 * @since 0.1
 */

class XML_Sitemaps_Manager
{
	/**
	 * class WP_Sitemaps_Manager constructor, runs on init
	 *
	 * @since 0.1
	 */
	function __construct()
	{
		// Abort here if we're in the admin.
		if ( is_admin() ) {
			return;
		}

		/**
		 * Usage info for debugging.
		 */
		add_action( 'shutdown', array( $this, 'usage' ) );

		/**
		 * Maybe disable sitemaps.
		 */
		add_action( 'wp_sitemaps_enabled', array( $this, 'sitemaps_enabled' ) );

		/**
		 * Maximum URLs per sitemap.
		 */
		add_filter( 'wp_sitemaps_max_urls', array( $this, 'max_urls' ), 10, 2 );

		/**
		 * Exclude providers or sitemaps.
		 */
		// Sitemap providers.
		add_filter( 'wp_sitemaps_add_provider', array( $this, 'exclude_providers' ), 10, 2 );
		// Individual post types. TODO Fix
		add_filter( 'wp_sitemaps_post_types',   array( $this, 'exclude_post_types' )       );
		// Individual taxonomies. TODO Fix
		add_filter( 'wp_sitemaps_taxonomies',   array( $this, 'exclude_taxonomies' )       );

		/**
		 * Modify query arguments.
		 */
		// Posts.
		add_filter(	'wp_sitemaps_posts_query_args',	     array( $this, 'posts_query_args' )      );
		// Terms.
		add_filter(	'wp_sitemaps_taxonomies_query_args', array( $this, 'taxonomies_query_args' ) );
		// Users.
		add_filter(	'wp_sitemaps_users_query_args',      array( $this, 'users_query_args' )      );


		/**
		 * Add lastmod.
		 */
		// To the index.
		add_filter( 'wp_sitemaps_index_entry',               array( $this, 'lastmod_index_entry' ),        10, 4 );
		// To post entries.
		add_filter( 'wp_sitemaps_posts_entry',               array( $this, 'lastmod_posts_entry' ),        10, 3 );
		add_filter( 'wp_sitemaps_posts_show_on_front_entry', array( $this, 'lastmod_posts_show_on_front_entry' ) );
		// To term entries.
		add_filter( 'wp_sitemaps_taxonomies_entry',          array( $this, 'lastmod_taxonomies_entry' ),   10, 4 );
		add_action( 'transition_post_status',                array( $this, 'update_term_modified_meta' ),  10, 3 );
		// To user entries.
		add_filter( 'wp_sitemaps_users_entry',               array( $this, 'lastmod_users_entry' ),        10, 2 );
		add_action( 'transition_post_status',                array( $this, 'update_user_modified_meta' ),  10, 3 );
		// TODO change post/term/user order to 'modified'


		/**
		 * Add sitemaps.
		 */
		// TODO
		// Maybe add dedicated Media sitemap if image tags are (still) not possible OR completely replace the renderer?

		// TODO
		// add custom post type root pages?
		// Maybe with wp_sitemaps_posts_pre_url_list (replacing the whole posts provider url_list);
			/* $post_type_archive_url = get_post_type_archive_link( $post_type );
			if ( $post_type_archive_url ) {
				$url_list[] = array( 'loc' => $post_type_archive_url );
			}*/

	}

	/**
	 * Maybe disable the XML Sitemap.
	 *
	 * @since 0.1
	 */
	public function sitemaps_enabled()
	{
		return get_option( 'xmlsm_sitemaps_enabled', true );
	}

	/**
	 * Filter maximum urls per sitemap. Hooked into wp_sitemaps_max_urls filter.
	 *
	 * @since 0.1
	 *
	 * @param  int    $max_urls
	 * @param  string $object_type
	 *
	 * @return int    $max_urls
	 */
	function max_urls( $max_urls, $object_type = 'post' )
	{
		$max = get_option( 'xmlsm_max_urls' );

		// Optionally split mas_urls per object type 'post', 'term' or 'user'
		if ( is_array( $max ) && ! empty( $max[$object_type] ) ) {
			$max = $max[$object_type];
		}

		return is_numeric( $max ) && $max > 0 ? $max : $max_urls;
	}

	/**
	 * Filter sitemap providers. Hooked into wp_sitemaps_add_provider filter.
	 *
	 * @since 0.1
	 *
	 * @param  obj       $provider
	 * @param  string    $name
	 *
	 * @return false|obj $provider or false if disabled
	 */
	public function exclude_providers( $provider, $name )
	{
		if ( is_admin() ) {
			return $provider;
		}

		$enabled = (array) get_option( 'xmlsm_sitemap_providers', array() );

		return in_array( $name, $enabled ) ? $provider : false;
	}

	/**
	 * Add lastmod to index entries.
	 * Hooked into wp_sitemaps_index_entry filter.
	 *
	 * @since 0.1
	 *
	 * @param array  $entry
	 * @param string $type
	 * @param string $subtype
	 * @param int    $page
	 *
	 * @return array $entry
	 */
	public function lastmod_index_entry( $entry, $type, $subtype, $page )
	{
		if ( ! get_option( 'xmlsm_lastmod' ) ) {
			return $entry;
		}

		// Add lastmod.
		switch( $type ) {
			case 'post':
				if ( '1' == $page ) {
					$entry['lastmod'] = get_date_from_gmt( get_lastpostmodified( 'GMT', $subtype ), DATE_W3C );
				}
				break;

			case 'term':
				if ( '1' == $page ) {
					$obj = get_taxonomy( $subtype );

					$lastmodified = array();
					foreach ( (array) $obj->object_type as $object_type ) {
						$lastmodified[] = get_lastpostdate( 'GMT', $object_type );
					}
					sort( $lastmodified );
					$lastmodified = array_filter( $lastmodified );
					$lastmod = end( $lastmodified );

					$entry['lastmod'] = get_date_from_gmt( $lastmod, DATE_W3C );
				}
				break;

			case 'user':
				if ( '1' == $page ) {
					$entry['lastmod'] = get_date_from_gmt( get_lastpostdate( 'GMT', 'post' ), DATE_W3C ); // Absolute last post date.
				}
				// TODO make this xmlsm_user_archive_post_type filter compatible.
				break;

			default:
				// Do nothing.
		}

		return $entry;
	}

	/**
	 * Filter post types. Hooked into wp_sitemaps_post_types filter.
	 *
	 * @since 0.1
	 *
	 * @param  array $post_types
	 *
	 * @return array $post_types
	 */
	public function exclude_post_types( $post_types )
	{
		$disabled_subtypes = (array) get_option( 'xmlsm_disabled_subtypes', array() );
		$exclude = array_key_exists( 'posts', $disabled_subtypes ) ? (array) $disabled_subtypes['posts'] : array();
		foreach ( $post_types as $post_type => $object ) {
			if ( in_array( $post_type, $exclude ) ) {
				unset( $post_types[$post_type] );
			}
		}

		return $post_types;
	}

	/**
	 * Filter post query arguments. Hooked into wp_sitemaps_posts_query_args filter.
	 *
	 * @since 0.1
	 *
	 * @param array $args
	 *
	 * @return array $args
	 */
	public function posts_query_args( $args )
	{
		/**
		 * Order by modified date if Lastmod is activated.
		 * This is needed to accomodate at least one correct lastmod in the Index.
		 */
		if ( get_option( 'xmlsm_lastmod' ) ) {
			$args['orderby'] = 'modified';
		}

		/**
		 * Allow exclusion of individual posts via meta data.
		 */
		/*if ( get_option( 'xmlsm_exclude_posts' ) ) {
			// Exclude posts based on meta data.
			$args['meta_query'] = array(
				array(
					'key' => '_xml_sitemap_exclude',
					'compare' => 'NOT EXISTS'
				)
			);
			// Update meta cache in one query instead of many. Maybe not needed? TODO test.
			$args['update_post_meta_cache'] = true;
		}*/

		return $args;
	}

	/**
	 * Add priority and lastmod to posts entries.
	 * Hooked into wp_sitemaps_posts_entry filter.
	 *
	 * @since 0.1
	 *
	 * @param array  $entry
	 * @param obj    $post_object
	 * @param string $post_type
	 *
	 * @return array $entry
	 */
	public function lastmod_posts_entry( $entry, $post_object, $post_type )
	{
		if ( ! get_option( 'xmlsm_lastmod' ) ) {
			return $entry;
		}

		// Get lastmod.
		if ( 'page' === $post_type && 'page' === get_option( 'show_on_front' ) && ( $post_object->ID == get_option( 'page_on_front' ) || $post_object->ID == get_option( 'page_for_posts' ) ) ) {
			// If blog or home page then look for last post date.
			$lastmod = get_lastpostdate( 'gmt', 'post' );
		} else {
			// Regular post type.
			$lastmod = $post_object->post_modified_gmt;

			// Make sure lastmod is not older than publication date (happens on scheduled posts).
			if ( isset( $post_object->post_date_gmt ) && strtotime( $post_object->post_date_gmt ) > strtotime( $lastmod ) ) {
				$lastmod = $post_object->post_date_gmt;
			}
		}

		// Add lastmod.
		$entry['lastmod'] = ! empty( $lastmod ) ? get_date_from_gmt( $lastmod, DATE_W3C ) : false;

		return $entry;
	}

	/**
	 * Add priority and lastmod to posts show on front entry.
	 * Hooked into wp_sitemaps_posts_show_on_front_entry filter.
	 *
	 * @since 0.1
	 *
	 * @param array $entry
	 *
	 * @return array $entry
	 */
	public function lastmod_posts_show_on_front_entry( $entry )
	{
		if ( ! get_option( 'xmlsm_lastmod' ) ) {
			return $entry;
		}

		// Set front blog page lastmod to last published post.
		$entry['lastmod'] = get_lastpostdate( 'gmt', 'post' );

		return $entry;
	}

	/**
	 * Maybe exclude taxonomies.
	 * Hooked into wp_sitemaps_taxonomies filter.
	 *
	 * @since 0.1
	 *
	 * @param array $taxonomies
	 *
	 * @return array $taxonomies
	 */
	public function exclude_taxonomies( $taxonomies )
	{
		$disabled_subtypes = (array) get_option( 'xmlsm_disabled_subtypes', array() );
		$exclude = array_key_exists( 'taxonomies', $disabled_subtypes ) ? (array) $disabled_subtypes['taxonomies'] : array();
		foreach ( $taxonomies as $tax_type => $object ) {
			if ( in_array( $tax_type, $exclude ) ) {
				unset( $taxonomies[$tax_type] );
			}
		}

		return $taxonomies;
	}

	/**
	 * Filter taxonomies query arguments. Hooked into wp_sitemaps_taxonomies_query_args filter.
	 *
	 * @since 0.1
	 *
	 * @param array $args
	 *
	 * @return array $args
	 */
	public function taxonomies_query_args( $args )
	{
		/**
		 * Order by modified date if Lastmod is activated.
		 * This is needed to accomodate at least one correct lastmod in the Index.
		 */
		if ( get_option( 'xmlsm_lastmod' ) ) {
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key' => 'term_modified_gmt',
					'compare' => 'NOT EXISTS'
				),
				array(
					'key' => 'term_modified_gmt'
				)
			);
			$args['meta_type'] = 'DATETIME';
			$args['orderby']   = 'meta_value_datetime term_order';
			$args['order']     = 'DESC';
		}

		return $args;
	}

	/**
	 * Add lastmod to taxonomy entries.
	 * Hooked into wp_sitemaps_taxonomies_entry filter.
	 *
	 * @since 0.1
	 *
	 * @param array    $entry
	 * @param int|obj  $term         Either the term ID or the WP_Term object depending on query arguments (WP 5.9)
	 * @param string   $taxonomy
	 * @param obj|null $term_object  The WP_Term object, available starting WP 6.0 otherwise null
	 *
	 * @return array $entry
	 */
	public function lastmod_taxonomies_entry( $entry, $term, $taxonomy, $term_object = null )
	{
		if ( ! get_option( 'xmlsm_lastmod' ) || ! function_exists( 'get_metadata_raw' ) ) {
			return $entry;
		}

		// Make sure we have a WP_Term object.
		if ( null === $term_object ) {
			$term_object = get_term( $term );
		}

		/**
		 * Get lastmod from term_modified meta data.
		 * Use get_metadata_raw because it will return null if the key does not exist.
		 */
		$lastmod = get_metadata_raw( 'term', $term_object->term_id, 'term_modified_gmt', true );
		if ( null === $lastmod ) {
			/**
			 * Fetch and cache lastmod as term_modified meta data.
			 */
			$lastmod = $this->_term_lastmod( $term_object->slug, $taxonomy );
			add_term_meta( $term_object->term_id, 'term_modified_gmt', $lastmod );
		}

		// Add lastmod.
		$entry['lastmod'] = ! empty( $lastmod ) ? mysql2date( DATE_W3C, $lastmod, false ) : false;

		return $entry;
	}

	/**
	 * Get term lastmod.
	 *
	 * @since 0.1
	 *
	 * @param string $slug     The slug of the term to be queried.
	 * @param string $taxonomy The term taxonomy.
	 *
	 * @return string Last publish date for user or empty string.
	 */
	protected function _term_lastmod( $slug, $taxonomy )
	{
		// Get the latest post in this taxonomy item, to use its post_date as lastmod.
		$posts = get_posts (
			array(
				'post_type' => 'any',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'update_cache' => false,
				'lang' => '',
				'tax_query' => array(
					array(
						'taxonomy' => $taxonomy,
						'field' => 'slug',
						'terms' => $slug
					)
				)
			)
		);

		return ! empty( $posts ) ? get_post_field( 'post_date_gmt', $posts[0] ) : '';
	}

	/**
	 * Update term modified meta, hooked to transition post status
	 *
	 * @since 0.1
	 *
	 * @param string  $new_status
	 * @param string  $old_status
	 * @param WP_Post $post
	 */
	public function update_term_modified_meta( $new_status, $old_status, $post )
	{
		// Bail when no status transition or not moving in or out of 'publish' status.
		if ( $old_status == $new_status || ( 'publish' != $new_status && 'publish' != $old_status )	) {
			return;
		}

		// TODO: maybe only for activated taxonomies

		$term_ids = array();
		$taxonomies = get_object_taxonomies( $post );

		foreach ( $taxonomies as $slug ) {
			$terms = wp_get_post_terms( $post->ID, $slug, array( 'fields' => 'ids' ));
			if ( ! is_wp_error( $terms ) ) {
				$term_ids = array_merge( $term_ids, $terms );
			}
		}

		$time = date('Y-m-d H:i:s');

		foreach ( $term_ids as $id ) {
			update_term_meta( $id, 'term_modified_gmt', $time );
		}
	}

	/**
	 * Filter users query arguments. Hooked into wp_sitemaps_users_query_args filter.
	 *
	 * @since 0.1
	 *
	 * @param array $args
	 *
	 * @return array $args
	 */
	public function users_query_args( $args )
	{
		/**
		 * Order by modified date if Lastmod is activated.
		 * This is needed to accomodate at least one correct lastmod in the Index.
		 */
		if ( get_option( 'xmlsm_lastmod' ) ) {
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key' => 'user_modified_gmt',
					'compare' => 'NOT EXISTS'
				),
				array(
					'key' => 'user_modified_gmt'
				)
			);

			$args['meta_type'] = 'DATETIME';
			$args['orderby']   = 'meta_value_datetime';
			$args['order']     = 'DESC';
		}

		return $args;
	}

	/**
	 * Add lastmod to author entries.
	 * Hooked into wp_sitemaps_users_entry filter.
	 *
	 * @since 0.1
	 *
	 * @param array   $entry
	 * @param WP_User $user_object
	 *
	 * @return array
	 */
	public function lastmod_users_entry( $entry, $user_object )
	{
		if ( ! get_option( 'xmlsm_lastmod' ) || ! function_exists( 'get_metadata_raw' ) ) {
			return $entry;
		}

		/**
		 * Get lastmod from user_modified meta data.
		 * Use get_metadata_raw because it will return null if the key does not exist.
		 */
		$lastmod = get_metadata_raw( 'user', $user_object->ID, 'user_modified_gmt', true );
		if ( null === $lastmod ) {
			/**
			 * Fetch and cache lastmod as user_modified meta data.
			 */
			$lastmod = $this->_user_lastmod( $user_object->ID );
			add_user_meta( $user_object->ID, 'user_modified_gmt', $lastmod );
		}

		// Add lastmod.
		$entry['lastmod'] = ! empty( $lastmod ) ? mysql2date( DATE_W3C, $lastmod, false ) : false;

		return $entry;
	}

	/**
	 * Get user lastmod.
	 *
	 * @since 0.1
	 *
	 * @param int $user_id The user ID to be queried.
	 *
	 * @return string Last publish date for user or empty string.
	 */
	protected function _user_lastmod( $user_id )
	{
		/**
		 * Filters the post types present in the author archive. Must return a string or an array of multiple post types.
		 * Allows to add or change post type when theme author archive page shows custom post types.
		 *
		 * @since 0.1
		 *
		 * @param string $post_type Post type slug. Default 'post'.
		 * @return string|array
		 */
		$post_type = apply_filters( 'xmlsm_user_archive_post_type', 'post' );

		$posts = get_posts(
			array(
				'author' => $user_id,
				'post_type' => $post_type,
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'update_cache' => false,
				'lang' => ''
			)
		);

		return ! empty( $posts ) ? get_post_field( 'post_date_gmt', $posts[0] ) : '';
	}

	/**
	 * Update user modified meta, hooked to transition post status
	 *
	 * @since 0.1
	 *
	 * @param string  $new_status
	 * @param string  $old_status
	 * @param WP_Post $post
	 */
	public function update_user_modified_meta( $new_status, $old_status, $post )
	{
		// Bail when no status transition or not moving in or out of 'publish' status.
		if ( $old_status == $new_status || ( 'publish' != $new_status && 'publish' != $old_status )	) {
			return;
		}

		$time = date('Y-m-d H:i:s');
		$user_id = get_post_field( 'post_author', $post );

		update_user_meta( $user_id, 'user_modified_gmt', $time );
	}

	/**
	 * Usage info for debugging printed at the end of the sitemap.
	 *
	 * @since 0.1
	 */
	public function usage() {
		global $wp, $wpdb, $EZSQL_ERROR;

		if ( empty( $wp->query_vars['sitemap'] ) || ! ( defined('WP_DEBUG') && WP_DEBUG ) ) {
			return;
		}

		// Get memory usage.
		$mem = function_exists('memory_get_peak_usage') ? round( memory_get_peak_usage()/1024/1024, 2 ) . 'M' : false;

		// Get query errors.
		$errors = '';
		if ( is_array($EZSQL_ERROR) && count($EZSQL_ERROR) ) {
			$i = 1;
			foreach ( $EZSQL_ERROR AS $e ) {
				$errors .= PHP_EOL . $i . ': ' . implode( PHP_EOL, $e ) . PHP_EOL;
				$i += 1;
			}
		}
		// Get saved queries.
		$saved = '';
		if ( defined('SAVEQUERIES') && SAVEQUERIES ) {
			$saved = $wpdb->queries;
		}

		// Get system load.
		$load = function_exists('sys_getloadavg') ? sys_getloadavg() : false;

		// Print debug info.
		include __DIR__ . '/views/_usage.php';
	}

}
