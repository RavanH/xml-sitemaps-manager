<?php
/**
 * XML Sitemaps Manager Lastmod Module.
 *
 * @package XML Sitemaps Manager
 *
 * @since 0.1
 */

namespace XMLSitemapsManager\Modules;

/**
 * Add lastmod to the sitemap.
 *
 * @since 0.1
 */
class Lastmod {
	/**
	 * Load lastmod module actions and filters.
	 */
	public static function load() {
		// Add lastmod to the index.
		\add_filter( 'wp_sitemaps_index_entry', array( __CLASS__, 'index_entry' ), 10, 4 );
		\add_filter( 'wp_sitemaps_posts_query_args', array( __CLASS__, 'posts_query_args' ) );

		// To post entries.
		\add_filter( 'wp_sitemaps_posts_entry', array( __CLASS__, 'posts_entry' ), 10, 3 );
		\add_filter( 'wp_sitemaps_posts_show_on_front_entry', array( __CLASS__, 'posts_show_on_front_entry' ) );

		// To term entries.
		\add_filter( 'wp_sitemaps_taxonomies_entry', array( __CLASS__, 'taxonomies_entry' ), 10, 4 );
		\add_action( 'transition_post_status', array( __CLASS__, 'update_term_modified_meta' ), 10, 3 );
		\add_filter( 'wp_sitemaps_taxonomies_query_args', array( __CLASS__, 'taxonomies_query_args' ) );

		// To user entries.
		\add_filter( 'wp_sitemaps_users_entry', array( __CLASS__, 'users_entry' ), 10, 2 );
		\add_action( 'transition_post_status', array( __CLASS__, 'update_user_modified_meta' ), 10, 3 );
		\add_filter( 'wp_sitemaps_users_query_args', array( __CLASS__, 'users_query_args' ) );
	}

	/**
	 * Add lastmod to index entries.
	 * Hooked into wp_sitemaps_index_entry filter.
	 *
	 * @since 0.1
	 *
	 * @param array  $entry   Sitemap entry.
	 * @param string $type    Entry type.
	 * @param string $subtype Subtype.
	 * @param int    $page    Sitemap page number.
	 *
	 * @return array $entry
	 */
	public static function index_entry( $entry, $type, $subtype, $page ) {
		// Skip if this is not the first sitemap. TODO make this possible for subsequent sitemaps.
		if ( $page > 1 || isset( $entry['lastmod'] ) ) {
			return $entry;
		}

		$subtype = \apply_filters( 'xmlsm_index_entry_subtype', $subtype );
		$lastmod = '';

		// Add lastmod.
		switch ( $type ) {

			case 'post':
				// Restores the more descriptive, specific name for use within this block.
				$post_type = $subtype;

				/**
				 * Pre-filter for Lastmod date. Can be used to bypass the default get_lastpostdate() for lastmod date retrieval.
				 * A falsy value other than NULL will cause the lastmod to be skipped. Otherwise make sure to return a GMT date.
				 *
				 * @since 0.6
				 *
				 * @param null
				 * @param array  $entry     Index entry array.
				 * @param string $post_type Post type slug. Default 'post'.
				 *
				 * @return string|bool|null $lastmod GMT date, false or null.
				 */
				$lastmod = \apply_filters( 'xmlsm_lastmod_index_entry', null, $entry, $post_type );

				// Get absolute last post date for object.
				if ( null === $lastmod ) {
					$lastmod = \get_lastpostmodified( 'gmt', $post_type );

					/*
					 * Calculate for one exception: the homepage as blog page.
					 */
					if ( 'page' === $post_type && 'posts' === \get_option( 'show_on_front' ) ) {
						// Get last published post.
						$home_post_type = \apply_filters( 'xmlsm_home_post_type', 'post' );
						$home_lastmod   = \get_lastpostdate( 'gmt', $home_post_type );
						if ( $home_lastmod && $home_lastmod > $lastmod ) {
							$lastmod = $home_lastmod;
						}
					}
				}
				break;

			case 'term':
				$obj = \get_taxonomy( $subtype );

				if ( $obj ) {
					$lastmodified = array();
					foreach ( (array) $obj->object_type as $object_type ) {
						/**
						 * Pre-filter for Lastmod date. Can be used to bypass the default get_lastpostdate() for lastmod date retrieval.
						 * A falsy value other than NULL will cause the lastmod to be skipped. Otherwise make sure to return a GMT date.
						 *
						 * @since 0.6
						 *
						 * @param null
						 * @param array  $entry     Index entry array.
						 * @param string $post_type Post type slug. Default 'post'.
						 *
						 * @return string|bool|null $lastmod GMT date, false or null.
						 */
						$lastmod = \apply_filters( 'xmlsm_lastmod_index_entry', null, $entry, $object_type );

						// Get absolute last post date for object.
						if ( null === $lastmod ) {
							$lastmod = \get_lastpostdate( 'gmt', $object_type );
						}

						$lastmodified[] = $lastmod;
					}

					sort( $lastmodified );
					$lastmodified = \array_filter( $lastmodified );
					$lastmod      = \end( $lastmodified );
				}

				break;

			case 'user':
				/**
				 * Filters the post types present in the author archive. Must return a string or an array of multiple post types.
				 * Allows to add or change post type when theme author archive page shows custom post types.
				 *
				 * @since 0.1
				 *
				 * @param string $post_type Post type slug. Default 'post'.
				 *
				 * @return string|array
				 */
				$post_type = \apply_filters( 'xmlsm_user_archive_post_type', 'post' );

				/**
				 * Pre-filter for Lastmod date. Can be used to bypass the default get_lastpostdate() for lastmod date retrieval.
				 * A falsy value other than NULL will cause the lastmod to be skipped. Otherwise make sure to return a GMT date.
				 *
				 * @since 0.6
				 *
				 * @param null
				 * @param array  $entry     Index entry array.
				 * @param string $post_type Post type slug. Default 'post'.
				 *
				 * @return string|bool|null $lastmod GMT date, false or null.
				 */
				$lastmod = \apply_filters( 'xmlsm_lastmod_index_entry', null, $entry, $post_type );

				// Get absolute last post date.
				if ( null === $lastmod ) {
					$lastmod = \get_lastpostdate( 'gmt', $post_type );
				}
				break;

			default:
				// Do nothing.
		}

		if ( $lastmod ) {
			$entry['lastmod'] = \wp_date( DATE_W3C, \strtotime( $lastmod ) );
		}

		return $entry;
	}

	/**
	 * Filter post query arguments. Hooked into wp_sitemaps_posts_query_args filter.
	 *
	 * @since 0.1
	 *
	 * @param array $args Arguments.
	 *
	 * @return array $args
	 */
	public static function posts_query_args( $args ) {
		/**
		 * Order by modified date.
		 * This is needed to accomodate at least one correct lastmod in the Index.
		 */
		$args['orderby'] = 'modified';
		$args['order']   = 'DESC';

		return $args;
	}

	/**
	 * Add lastmod to posts entries.
	 * Hooked into wp_sitemaps_posts_entry filter.
	 *
	 * @since 0.1
	 *
	 * @param array  $entry       Sitemap entry.
	 * @param obj    $post_object Post object.
	 * @param string $post_type   Post type.
	 *
	 * @return array $entry
	 */
	public static function posts_entry( $entry, $post_object, $post_type ) {
		if ( ! isset( $entry['lastmod'] ) ) {
			$entry['lastmod'] = \wp_date( DATE_W3C, \strtotime( $post_object->post_modified_gmt ) );
		}

		/*
		 * Recalculate lastmod for exeptional cases:
		 * - for blogpage;
		 * - for homepage;
		 * - when modified date is older than post date.
		 */
		if ( 'page' === $post_type ) {
			if ( 'page' === get_option( 'show_on_front' ) && (int) \get_option( 'page_on_front' ) === $post_object->ID ) {
				$home_post_type   = \apply_filters( 'xmlsm_front_page_post_type', 'post' );
				$entry['lastmod'] = wp_date( DATE_W3C, \strtotime( \get_lastpostdate( 'gmt', $home_post_type ) ) );
			} elseif ( (int) \get_option( 'page_for_posts' ) === $post_object->ID ) {
				$blog_post_type   = \apply_filters( 'xmlsm_blog_page_post_type', 'post' );
				$entry['lastmod'] = \wp_date( DATE_W3C, \strtotime( \get_lastpostdate( 'gmt', $blog_post_type ) ) );
			}
		} elseif ( isset( $post_object->post_date_gmt ) && $post_object->post_date_gmt > $post_object->post_modified_gmt ) {
			$entry['lastmod'] = \wp_date( DATE_W3C, \strtotime( $post_object->post_date_gmt ) );
		}

		return $entry;
	}

	/**
	 * Add lastmod to posts show on front entry.
	 * Hooked into wp_sitemaps_posts_show_on_front_entry filter.
	 *
	 * Overrides lastmod in WP 6.5+ with last post date instead of last modified date.
	 *
	 * @since 0.1
	 *
	 * @param array $entry Sitemap entry.
	 *
	 * @return array $entry
	 */
	public static function posts_show_on_front_entry( $entry ) {
		// Get last published post.
		$post_type = \apply_filters( 'xmlsm_blog_page_post_type', 'post' );
		$lastmod   = \get_lastpostdate( 'gmt', $post_type );

		// Add lastmod.
		if ( $lastmod ) {
			$entry['lastmod'] = \wp_date( DATE_W3C, \strtotime( $lastmod ) );
		}

		return $entry;
	}

	/**
	 * Filter taxonomies query arguments. Hooked into wp_sitemaps_taxonomies_query_args filter.
	 *
	 * @since 0.1
	 *
	 * @param array $args Arguments.
	 *
	 * @return array $args
	 */
	public static function taxonomies_query_args( $args ) {
		/**
		 * Order by modified date.
		 * This is needed to accomodate at least one correct lastmod in the Index.
		 */
		$args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key'     => 'term_modified_gmt',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key' => 'term_modified_gmt',
			),
		);
		$args['meta_type']  = 'DATETIME';
		$args['orderby']    = 'meta_value_datetime term_order';
		$args['order']      = 'DESC';

		return $args;
	}

	/**
	 * Add lastmod to taxonomy entries.
	 * Hooked into wp_sitemaps_taxonomies_entry filter.
	 *
	 * @since 0.1
	 *
	 * @param array    $entry       Sitemap entry.
	 * @param int|obj  $term_id     Either the term ID or the WP_Term object depending on query arguments (WP 5.9).
	 * @param string   $taxonomy    Entry taxonomy.
	 * @param obj|null $term_object The WP_Term object, available starting WP 6.0 otherwise null.
	 *
	 * @return array $entry
	 */
	public static function taxonomies_entry( $entry, $term_id, $taxonomy, $term_object = null ) {
		// Make sure we have a WP_Term object.
		if ( null === $term_object ) {
			$term_object = \get_term( $term_id );
		}

		/**
		 * Filters the lastmod metadata key.
		 *
		 * @since 0.6
		 *
		 * @param string $meta_key.
		 * @return string
		 */
		$meta_key = \apply_filters( 'xmlsm_lastmod_term_meta_key', 'term_modified_gmt' );

		/**
		 * Get lastmod from term_modified meta data.
		 * Use get_metadata_raw because it will return null if the key does not exist.
		 */
		$lastmod = \get_metadata_raw( 'term', $term_object->term_id, $meta_key, true );
		if ( null === $lastmod ) {
			/**
			 * Fetch and cache lastmod as term_modified meta data.
			 */
			$lastmod = self::_term_lastmod( $term_object->slug, $taxonomy );
			\add_term_meta( $term_object->term_id, $meta_key, $lastmod );
		}

		// Add lastmod.
		if ( $lastmod ) {
			$entry['lastmod'] = \mysql2date( DATE_W3C, $lastmod, false );
		}

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
	private static function _term_lastmod( $slug, $taxonomy ) {
		$args = array(
			'post_type'              => 'any',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'update_cache'           => false,
			'tax_query'              => array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $slug,
				),
			),
		);

		/**
		 * Filters the get_posts arguments array for retrieving the last post in taxonomy term archive.
		 * Allows to add or change arguments before get_posts() is executed.
		 *
		 * @since 0.6
		 *
		 * @param array $args.
		 * @return array
		 */
		$args = \apply_filters( 'xmlsm_lastmod_term_args', $args );

		// Get the latest post in this taxonomy item, to use its post_date as lastmod.
		$posts = \get_posts( $args );

		return ! empty( $posts ) ? \get_post_field( 'post_date_gmt', $posts[0] ) : '';
	}

	/**
	 * Update term modified meta. Hooked to transition post status.
	 *
	 * @since 0.1
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public static function update_term_modified_meta( $new_status, $old_status, $post ) {
		// Bail when no status transition or not moving in or out of 'publish' status.
		if ( $old_status === $new_status || ( 'publish' !== $new_status && 'publish' !== $old_status ) ) {
			return;
		}

		// TODO: maybe only for activated taxonomies.

		$term_ids   = array();
		$taxonomies = \get_object_taxonomies( $post );

		foreach ( $taxonomies as $slug ) {
			$terms = \wp_get_post_terms( $post->ID, $slug, array( 'fields' => 'ids' ) );
			if ( ! \is_wp_error( $terms ) ) {
				$term_ids = \array_merge( $term_ids, $terms );
			}
		}

		$time = \gmdate( 'Y-m-d H:i:s' );

		/**
		 * Filters the lastmod metadata key.
		 *
		 * @since 0.6
		 *
		 * @param string $meta_key.
		 * @return string
		 */
		$meta_key = \apply_filters( 'xmlsm_lastmod_term_meta_key', 'term_modified_gmt', $post );

		foreach ( $term_ids as $id ) {
			\update_term_meta( $id, $meta_key, $time );
		}
	}

	/**
	 * Filter users query arguments. Hooked into wp_sitemaps_users_query_args filter.
	 *
	 * @since 0.1
	 *
	 * @param array $args Arguments.
	 *
	 * @return array $args
	 */
	public static function users_query_args( $args ) {
		/**
		 * Order by modified date.
		 * This is needed to accomodate at least one correct lastmod in the Index.
		 */
		$args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key'     => 'user_modified_gmt',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key' => 'user_modified_gmt',
			),
		);

		$args['meta_type'] = 'DATETIME';
		$args['orderby']   = 'meta_value_datetime';
		$args['order']     = 'DESC';

		return $args;
	}

	/**
	 * Add lastmod to author entries.
	 * Hooked into wp_sitemaps_users_entry filter.
	 *
	 * @since 0.1
	 *
	 * @param array   $entry       Sitemap entry.
	 * @param WP_User $user_object User object.
	 *
	 * @return array
	 */
	public static function users_entry( $entry, $user_object ) {
		/**
		 * Filters the lastmod metadata key. Allows to change it depending on language for example.
		 *
		 * @since 0.6
		 *
		 * @param string $meta_key.
		 * @return string
		 */
		$meta_key = \apply_filters( 'xmlsm_lastmod_user_meta_key', 'user_modified_gmt' );

		/**
		 * Get lastmod from user_modified meta data.
		 * Use get_metadata_raw because it will return null if the key does not exist.
		 */
		$lastmod = \get_metadata_raw( 'user', $user_object->ID, $meta_key, true );
		if ( null === $lastmod ) {
			/**
			 * Fetch and cache lastmod as user_modified meta data.
			 */
			$lastmod = self::_user_lastmod( $user_object->ID );
			\add_user_meta( $user_object->ID, $meta_key, $lastmod );
		}

		// Add lastmod.
		if ( $lastmod ) {
			$entry['lastmod'] = \mysql2date( DATE_W3C, $lastmod, false );
		}

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
	private static function _user_lastmod( $user_id ) {
		/**
		 * Filters the post types present in the author archive. Must return a string or an array of multiple post types.
		 * Allows to add or change post type when theme author archive page shows custom post types.
		 *
		 * @since 0.1
		 *
		 * @param string $post_type Post type slug. Default 'post'.
		 * @return string|array
		 */
		$post_type = \apply_filters( 'xmlsm_user_archive_post_type', 'post' );

		$args = array(
			'author'                 => $user_id,
			'post_type'              => $post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'update_cache'           => false,
		);

		/**
		 * Filters the get_posts arguments array for retrieving the last post in user archive.
		 * Allows to add or change arguments before get_posts() is executed.
		 *
		 * @since 0.6
		 *
		 * @param array $args.
		 * @return array
		 */
		$args = apply_filters( 'xmlsm_lastmod_user_args', $args );

		$posts = \get_posts( $args );

		return ! empty( $posts ) ? \get_post_field( 'post_date_gmt', $posts[0] ) : '';
	}

	/**
	 * Update user modified meta. Hooked to transition post status.
	 *
	 * @since 0.1
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public static function update_user_modified_meta( $new_status, $old_status, $post ) {
		// Bail when no status transition or not moving in or out of 'publish' status.
		if ( $old_status === $new_status || ( 'publish' !== $new_status && 'publish' !== $old_status ) ) {
			return;
		}

		$time    = \gmdate( 'Y-m-d H:i:s' );
		$user_id = \get_post_field( 'post_author', $post );

		/**
		 * Filters the lastmod metadata key. Allows to change it depending on language for example.
		 *
		 * @since 0.6
		 *
		 * @param string $meta_key.
		 * @return string
		 */
		$meta_key = \apply_filters( 'xmlsm_lastmod_user_meta_key', 'user_modified_gmt', $post );

		\update_user_meta( $user_id, $meta_key, $time );
	}
}
