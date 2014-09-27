<?php
/**
 * WPMovieLibrary Deprecated Meta Class.
 * 
 * This class handles deprecated WPMovieLibrary Movie Metadata. Prior to WPMOLY
 * version 1.3 movie metadata were stored in a unique post meta value which
 * blocked a lot of features and improvement. Current class handles the migration
 * from obsolete to new data format.
 * 
 * @package   WPMovieLibrary
 * @author    Charlie MERLAND <charlie@caercam.org>
 * @license   GPL-3.0
 * @link      http://www.caercam.org/
 * @copyright 2014 CaerCam.org
 */

if ( ! class_exists( 'WPMOLY_Deprecated_Meta' ) ) :

	class WPMOLY_Deprecated_Meta extends WPMOLY_Module {

		/**
		 * Constructor
		 *
		 * @since    1.3
		 */
		public function __construct() {

			$this->register_hook_callbacks();
		}

		/**
		 * Register callbacks for actions and filters
		 * 
		 * @since    1.3
		 */
		public function register_hook_callbacks() {

			add_action( 'admin_notices', array( $this, 'deprecated_meta_notice' ) );

			// Load admin style sheet and JavaScript.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

			add_action( 'wp_ajax_wpmoly_update_movie', array( $this, 'update_movie_callback' ) );
		}

		/**
		 * Register and enqueue admin-specific style sheet.
		 *
		 * @since    1.3
		 *
		 * @return   null    Return early if no settings page is registered.
		 */
		public function enqueue_admin_styles( $hook ) {

			if ( 'dashboard_page_wpmoly-update-movies' == $hook )
				wp_enqueue_style( 'roboto-font', '//fonts.googleapis.com/css?family=Roboto:100', array(), WPMOLY_VERSION );
		}

		/**
		 * Register and enqueue admin-specific JavaScript.
		 * 
		 * @since    1.3
		 * 
		 * @return   null    Return early if no settings page is registered.
		 */
		public function enqueue_admin_scripts( $hook ) {

			if ( 'dashboard_page_wpmoly-update-movies' == $hook ) {
				wp_enqueue_script( WPMOLY_SLUG . '-jquery-ajax-queue', WPMOLY_URL . '/assets/js/jquery.ajaxQueue.js', array( 'jquery' ), WPMOLY_VERSION, true );
				wp_enqueue_script( WPMOLY_SLUG . '-updates', WPMOLY_URL . '/assets/js/wpmoly.updates.js', array( WPMOLY_SLUG, 'jquery' ), WPMOLY_VERSION, true );

				wp_localize_script(
					WPMOLY_SLUG . '-updates', 'wpmoly_legacy',
					array(
						'lang' => array(
							'updated'        => __( 'updated successfully', 'wpmovielibrary' ),
							'not_updated'    => __( 'not updated', 'wpmovielibrary' ),
							'updating'       => __( 'updating movies...', 'wpmovielibrary' ),
							'selected'       => _n( 'selected', 'selected', 0, 'wpmovielibrary' ),
							'x_selected'     => _n( 'selected', 'selected', 2, 'wpmovielibrary' ),
							'movie_updated'  => _n( 'movie updated', 'movies updated', 0, 'wpmovielibrary' ),
							'movies_updated' => _n( 'movie updated', 'movies updated', 2, 'wpmovielibrary' ),
						)
					)
				);
			}
		}

		/**
		 * Display an admin notice
		 *
		 * @since    1.3
		 */
		public function deprecated_meta_notice() {

			echo self::render_admin_template( 'admin-notice.php', array( 'notice' => 'deprecated-meta' ) );
		}

		/**
		 * AJAX callback for movie update.
		 *
		 * @since    1.3
		 */
		public function update_movie_callback() {

			wpmoly_check_ajax_referer( 'update-movie' );

			$movie_id = ( isset( $_POST['movie_id'] ) && '' != $_POST['movie_id'] ? intval( $_POST['movie_id'] ) : null );

			if ( is_null( $movie_id ) )
				wp_die( 0 );

			$response = self::update_movie( $movie_id );

			wpmoly_ajax_response( $response, array(), wpmoly_create_nonce( 'update-movie' ) );
		}

		/**
		 * Dashboard movies update page
		 *
		 * @since    1.3
		 */
		public static function update_movies_page() {

			$deprecated = self::get_movies();
			$updated    = self::get_updated_movies();

			echo self::render_admin_template( 'update-movies.php', array( 'deprecated' => $deprecated, 'updated' => $updated ) );
		}

		/**
		 * Get a list of deprected Movie IDs.
		 * 
		 * Movie having an non-empty '_wpmoly_movie_data' custom field
		 * are considered deprecated and needing updating.
		 * 
		 * @since    1.3
		 * 
		 * @return   int|bool    False is no deprecated could be find, number of deprecated movie else.
		 */
		public static function get_deprecated_movies() {

			global $wpdb;

			$movies = $wpdb->get_results( "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key='_wpmoly_movie_data' AND meta_value!=''" );
			$movies = ( ! $wpdb->num_rows ? false : $movies );

			return $movies;
		}

		/**
		 * Get a list of updated Movie IDs.
		 * 
		 * @since    1.3
		 * 
		 * @return   array    
		 */
		public static function get_updated_movies() {

			global $wpdb;

			$movies = $wpdb->get_results( "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key='_wpmoly_movie_data' AND meta_value!=''" );

			foreach ( $movies as $i => $movie )
				$movies[ $i ] = $movie->post_id;

			$args = array(
				'posts_per_page' => -1,
				'orderby'        => 'post_title',
				'order'          => 'ASC',
				'post_type'      => 'movie',
				'post_status'    => 'publish',
				'post__not_in'   => $movies
			);

			$movies = get_posts( $args );

			return $movies;
		}

		/**
		 * Get a list of deprected Movie IDs.
		 * 
		 * Movie having an non-empty '_wpmoly_movie_data' custom field
		 * are considered deprecated and needing updating.
		 * 
		 * @since    1.3
		 * 
		 * @return   int|bool    False is no deprecated could be find, number of deprecated movie else.
		 */
		private static function get_movies() {

			$movies = self::get_deprecated_movies();
			if ( false === $movies )
				return false;

			foreach ( $movies as $i => $movie )
				$movies[ $i ] = $movie->post_id;

			$args = array(
				'posts_per_page' => -1,
				'orderby'        => 'post_title',
				'order'          => 'ASC',
				'post_type'      => 'movie',
				'post_status'    => 'publish',
				'post__in'       => $movies
			);

			$movies = get_posts( $args );

			return $movies;
		}

		/**
		 * Update Movie metas.
		 * 
		 * @since    1.3
		 *
		 * @param    int    $movie_id
		 * 
		 * @return   int|object    Movie ID if meta were update successfully, WP_Error else
		 */
		public static function update_movie( $movie_id ) {

			if ( ! current_user_can( 'edit_post' ) )
				return new WP_Error( 'permission_denied', __( 'Error: you are not allowed to edit this movie.', 'wpmovielibrary' ) );

			if ( ! get_post( $movie_id ) || 'movie' != get_post_type( $movie_id ) )
				return new WP_Error( 'invalid_post', __( 'Error: submitted post is not a movie.', 'wpmovielibrary' ) );

			$update = self::update_meta( $movie_id );

			return $update;
		}

		/**
		 * Update metas.
		 * 
		 * @since    1.3
		 *
		 * @param    int    $movie_id
		 * 
		 * @return   int|bool    False if update failed, true else
		 */
		private static function update_meta( $movie_id ) {

			$meta = get_post_meta( $movie_id, '_wpmoly_movie_data', $single = true );
			if ( '' == $meta )
				return false;

			$update = WPMOLY_Edit_Movies::save_movie_meta( $movie_id, $meta, $clean = false );

			if ( ! is_wp_error( $update ) && $update == $movie_id )
				delete_post_meta( $movie_id, '_wpmoly_movie_data', $meta );

			return $update;
		}

		/**
		 * Update old 'wpml' slug to new 'wpmoly' in database.
		 * 
		 * @since    2.0
		 */
		private static function update_slug() {

			global $wpdb;

			$where = array();
			$slugs = array( 'wpml_backdrop', 'wpml_movie', 'wpml_poster' );
			foreach ( $slugs as $slug )
				$where[] = "meta_key LIKE '%{$slug}%'";

			$where = implode( ' OR ', $where );
			$movies = $wpdb->get_results( "SELECT meta_id FROM {$wpdb->postmeta} WHERE {$where}" );

			if ( ! $wpdb->num_rows )
				return false;

			foreach( $movies as $i => $movie )
				$movies[ $i ] = $movie->meta_id;

			$movies = implode( ',', $movies );
			if ( '' == $movies )
				return false;

			$update = $wpdb->query( "UPDATE {$wpdb->postmeta} SET meta_key=REPLACE(meta_key,'wpml_','wpmoly_') WHERE meta_id IN ({$movies})" );

		}

		/**
		 * Prepares sites to use the plugin during single or network-wide activation
		 *
		 * @since    1.3
		 *
		 * @param    bool    $network_wide
		 */
		public function activate( $network_wide ) {

			if ( ! wpmoly_has_deprecated_meta() )
				return false;

			$deprecated = self::get_deprecated_movies();
			if ( false !== $deprecated ) {

				delete_option( 'wpmoly_has_deprecated_meta' );
				add_option( 'wpmoly_has_deprecated_meta', count( $deprecated ), null, 'no' );
			}

			self::update_slug();
		}

		/**
		 * Rolls back activation procedures when de-activating the plugin
		 *
		 * @since    1.3
		 */
		public function deactivate() {}

		/**
		 * Set the uninstallation instructions
		 *
		 * @since    1.3
		 */
		public static function uninstall() {}

		/**
		 * Initializes variables
		 *
		 * @since    1.3
		 */
		public function init() {}

	}

endif;