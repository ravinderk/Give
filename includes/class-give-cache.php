<?php
/**
 * Class for managing cache
 * Note: only use for internal purpose.
 *
 * @package     Give
 * @subpackage  Classes/Give_Cache
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Cache {
	/**
	 * Instance.
	 *
	 * @since  1.8.7
	 * @access protected
	 * @var Give_Cache
	 */
	static protected $instance;

	/**
	 * Flag to check if caching enabled or not.
	 *
	 * @since  2.0
	 * @access protected
	 * @var
	 */
	protected $is_cache;

	/**
	 * Singleton pattern.
	 *
	 * @since  1.8.7
	 * @access private
	 * Give_Cache constructor.
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  1.8.7
	 * @access public
	 * @return static
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Give_Cache ) ) {
			self::$instance = new Give_Cache();

			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup hooks.
	 *
	 * @since  1.8.7
	 * @access public
	 */
	public function setup() {
		add_action( 'give_init', array( __CLASS__, '__setup_constants' ), 0 );
		add_action( 'give_save_settings_give_settings', array( __CLASS__, 'flush_cache' ) );

		add_action( 'wp', array( __CLASS__,  'prevent_caching' ) );
		add_action( 'admin_notices', array( $this, '__notices' ) );
	}

	/**
	 * Setup constants
	 * Note: only for internal use
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 */
	public function __setup_constants(){
		// Currently enable cache only for backend.
		self::$instance->is_cache = (
			defined( 'GIVE_CACHE' )
				? GIVE_CACHE
				: give_is_setting_enabled( give_get_option( 'cache', 'enabled' ) )
		) && is_admin();
	}

	/**
	 * Prevent caching on certain pages
	 *
	 * @since  2.0.5
	 * @access public
	 * @credit WooCommerce
	 */
	public static function prevent_caching() {
		if ( ! is_blog_installed() ) {
			return;
		}

		$page_ids = array_filter( array(
			give_get_option( 'success_page' ),
			give_get_option( 'failure_page' ),
			give_get_option( 'history_page' ),
		) );

		if (
			is_page( $page_ids )
			|| is_singular( 'give_forms' )
		) {
			self::set_nocache_constants();
			nocache_headers();
		}
	}

	/**
	 * Set constants to prevent caching by some plugins.
	 *
	 * @since  2.0.5
	 * @access public
	 * @credit WooCommerce
	 *
	 * @param  mixed $return Value to return. Previously hooked into a filter.
	 *
	 * @return mixed
	 */
	public static function set_nocache_constants( $return = true ) {
		give_maybe_define_constant( 'DONOTCACHEPAGE', true );
		give_maybe_define_constant( 'DONOTCACHEOBJECT', true );
		give_maybe_define_constant( 'DONOTCACHEDB', true );

		return $return;
	}

	/**
	 * Notices function.
	 *
	 * @since  2.0.5
	 * @access public
	 * @credit WooCommerce
	 */
	public function __notices() {
		if ( ! function_exists( 'w3tc_pgcache_flush' ) || ! function_exists( 'w3_instance' ) ) {
			return;
		}

		/* @var W3_Config $config */
		$config   = w3_instance( 'W3_Config' );
		$enabled  = $config->get_integer( 'dbcache.enabled' );
		$settings = array_map( 'trim', $config->get_array( 'dbcache.reject.sql' ) );

		if ( $enabled && ! in_array( 'give', $settings, true ) ) {
			?>
			<div class="error">
				<p><?php echo wp_kses_post( sprintf( __( 'In order for <strong>database caching</strong> to work with Give you must add %1$s to the "Ignored query stems" option in <a href="%2$s">W3 Total Cache settings</a>.', 'give' ), '<code>give</code>', esc_url( admin_url( 'admin.php?page=w3tc_dbcache#dbcache_reject_sql' ) ) ) ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Get cache key.
	 *
	 * @since  1.8.7
	 *
	 * @param  string $action     Cache key prefix.
	 * @param  array  $query_args (optional) Query array.
	 * @param  bool   $is_prefix
	 *
	 * @return string
	 */
	public static function get_key( $action, $query_args = null, $is_prefix = true ) {
		// Bailout.
		if ( empty( $action ) ) {
			return new WP_Error( 'give_invalid_cache_key_action', __( 'Do not pass empty action to generate cache key.', 'give' ) );
		}

		// Set cache key.
		$cache_key = $is_prefix ? "give_cache_{$action}" : $action;

		// Bailout.
		if ( ! empty( $query_args ) ) {
			$cache_key = "{$cache_key}_" . substr( md5( serialize( $query_args ) ), 0, 15 );
		}

		/**
		 * Filter the cache key name.
		 *
		 * @since 2.0
		 */
		return apply_filters( 'give_get_cache_key', $cache_key, $action, $query_args );
	}

	/**
	 * Get cache.
	 *
	 * @since  1.8.7
	 *
	 * @param  string $cache_key
	 * @param  bool   $custom_key
	 * @param  mixed  $query_args
	 *
	 * @return mixed
	 */
	public static function get( $cache_key, $custom_key = false, $query_args = array() ) {
		if ( ! self::is_valid_cache_key( $cache_key ) ) {
			if( empty( $cache_key ) ) {
				return new WP_Error( 'give_empty_cache_key', __( 'Do not pass invalid empty cache key', 'give' ) );
			}elseif ( ! $custom_key ) {
				return new WP_Error( 'give_invalid_cache_key', __( 'Cache key format should be give_cache_*', 'give' ) );
			}

			$cache_key = self::get_key( $cache_key, $query_args );
		}

		$option = get_option( $cache_key );

		// Backward compatibility (<1.8.7).
		if ( ! is_array( $option ) || empty( $option ) || ! array_key_exists( 'expiration', $option ) ) {
			return $option;
		}

		// Get current time.
		$current_time = current_time( 'timestamp', 1 );

		if ( empty( $option['expiration'] ) || ( $current_time < $option['expiration'] ) ) {
			$option = $option['data'];
		} else {
			$option = false;
		}

		return $option;
	}

	/**
	 * Set cache.
	 *
	 * @since  1.8.7
	 *
	 * @param  string   $cache_key
	 * @param  mixed    $data
	 * @param  int|null $expiration Timestamp should be in GMT format.
	 * @param  bool     $custom_key
	 * @param  mixed    $query_args
	 *
	 * @return mixed
	 */
	public static function set( $cache_key, $data, $expiration = null, $custom_key = false, $query_args = array() ) {
		if ( ! self::is_valid_cache_key( $cache_key ) ) {
			if ( ! $custom_key ) {
				return new WP_Error( 'give_invalid_cache_key', __( 'Cache key format should be give_cache_*', 'give' ) );
			}

			$cache_key = self::get_key( $cache_key, $query_args );
		}

		$option_value = array(
			'data'       => $data,
			'expiration' => ! is_null( $expiration )
				? ( $expiration + current_time( 'timestamp', 1 ) )
				: null,
		);

		$result = update_option( $cache_key, $option_value, false );

		return $result;
	}

	/**
	 * Delete cache.
	 *
	 * Note: only for internal use
	 *
	 * @since  1.8.7
	 *
	 * @param  string|array $cache_keys
	 *
	 * @return bool|WP_Error
	 */
	public static function delete( $cache_keys ) {
		$result       = true;
		$invalid_keys = array();

		if ( ! empty( $cache_keys ) ) {
			$cache_keys = is_array( $cache_keys ) ? $cache_keys : array( $cache_keys );

			foreach ( $cache_keys as $cache_key ) {
				if ( ! self::is_valid_cache_key( $cache_key ) ) {
					$invalid_keys[] = $cache_key;
					$result         = false;
				}

				delete_option( $cache_key );
			}
		}

		if ( ! $result ) {
			$result = new WP_Error(
				'give_invalid_cache_key',
				__( 'Cache key format should be give_cache_*', 'give' ),
				$invalid_keys
			);
		}

		return $result;
	}

	/**
	 * Get list of options like.
	 *
	 * Note: only for internal use
	 *
	 * @since  1.8.7
	 * @access public
	 *
	 * @param string $option_name
	 * @param bool   $fields
	 *
	 * @return array
	 */
	public static function get_options_like( $option_name, $fields = false ) {
		global $wpdb;

		$field_names = $fields ? 'option_name, option_value' : 'option_name';

		if ( $fields ) {
			$options = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT {$field_names }
						FROM {$wpdb->options}
						Where option_name
						LIKE '%%%s%%'",
					"give_cache_{$option_name}"
				),
				ARRAY_A
			);
		} else {
			$options = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT *
						FROM {$wpdb->options}
						Where option_name
						LIKE '%%%s%%'",
					"give_cache_{$option_name}"
				),
				1
			);
		}

		if ( ! empty( $options ) && $fields ) {
			foreach ( $options as $index => $option ) {
				$option['option_value'] = maybe_unserialize( $option['option_value'] );
				$options[ $index ]      = $option;
			}
		}

		return $options;
	}

	/**
	 * Check cache key validity.
	 *
	 * @since  1.8.7
	 * @access public
	 *
	 * @param $cache_key
	 *
	 * @return bool
	 */
	public static function is_valid_cache_key( $cache_key ) {
		$is_valid = ( false !== strpos( $cache_key, 'give_cache_' ) );


		/**
		 * Filter the flag which tell about cache key valid or not
		 *
		 * @since 2.0
		 */
		return apply_filters( 'give_is_valid_cache_key', $is_valid, $cache_key );
	}


	/**
	 * Get cache from group
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param int    $id
	 * @param string $group
	 *
	 * @return mixed
	 */
	public static function get_group( $id, $group = '' ) {
		$cached_data = null;

		// Bailout.
		if ( self::$instance->is_cache && ! empty( $id ) ) {
			$group = self::$instance->filter_group_name( $group );

			$cached_data = wp_cache_get( $id, $group );
			$cached_data = false !== $cached_data ? $cached_data : null;
		}

		return $cached_data;
	}

	/**
	 * Cache small chunks inside group
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param int    $id
	 * @param mixed  $data
	 * @param string $group
	 * @param int    $expire
	 *
	 * @return bool
	 */
	public static function set_group( $id, $data, $group = '', $expire = 0 ) {
		$status = false;

		// Bailout.
		if ( ! self::$instance->is_cache || empty( $id ) ) {
			return $status;
		}

		$group = self::$instance->filter_group_name( $group );

		$status = wp_cache_set( $id, $data, $group, $expire );

		return $status;
	}

	/**
	 * Cache small db query chunks inside group
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param int   $id
	 * @param mixed $data
	 *
	 * @return bool
	 */
	public static function set_db_query( $id, $data ) {
		$status = false;

		// Bailout.
		if ( ! self::$instance->is_cache || empty( $id ) ) {
			return $status;
		}

		return self::set_group( $id, $data, 'give-db-queries', 0 );
	}

	/**
	 * Get cache from group
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param string $id
	 *
	 * @return mixed
	 */
	public static function get_db_query( $id ) {
		return self::get_group( $id, 'give-db-queries' );
	}

	/**
	 * Delete group cache
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param int|array $ids
	 * @param string    $group
	 * @param int       $expire
	 *
	 * @return bool
	 */
	public static function delete_group( $ids, $group = '', $expire = 0 ) {
		$status = false;

		// Bailout.
		if ( ! self::$instance->is_cache || empty( $ids ) ) {
			return $status;
		}

		$group_prefix = $group;
		$group = self::$instance->filter_group_name( $group );

		// Delete single or multiple cache items from cache.
		if ( ! is_array( $ids ) ) {
			$status = wp_cache_delete( $ids, $group );
			self::$instance->get_incrementer( true );

			/**
			 * Fire action when cache deleted for specific id.
			 *
			 * @since 2.0
			 *
			 * @param string $ids
			 * @param string $group
			 * @param int    $expire
			 */
			do_action( "give_deleted_{$group_prefix}_cache", $ids, $group, $expire, $status );

		} else {
			foreach ( $ids as $id ) {
				$status = wp_cache_delete( $id, $group );
				self::$instance->get_incrementer( true );

				/**
				 * Fire action when cache deleted for specific id .
				 *
				 * @since 2.0
				 *
				 * @param string $ids
				 * @param string $group
				 * @param int    $expire
				 */
				do_action( "give_deleted_{$group_prefix}_cache", $id, $group, $expire, $status );
			}
		}

		return $status;
	}





	/**
	 * Get unique incrementer.
	 *
	 * @see    https://core.trac.wordpress.org/ticket/4476
	 * @see    https://www.tollmanz.com/invalidation-schemes/
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param bool   $refresh
	 * @param string $incrementer_key
	 *
	 * @return string
	 */
	public function get_incrementer( $refresh = false, $incrementer_key = 'give-cache-incrementer-db-queries' ) {
		$incrementer_value = wp_cache_get( $incrementer_key );

		if ( false === $incrementer_value || true === $refresh ) {
			$incrementer_value = (string) microtime( true );
			wp_cache_set( $incrementer_key, $incrementer_value );
		}

		return $incrementer_value;
	}


	/**
	 * Flush cache on cache setting enable/disable
	 * Note: only for internal use
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param bool $force Delete cache forcefully.
	 *
	 * @return bool
	 */
	public static function flush_cache( $force = false ) {
		if (
			$force
			|| ( Give_Admin_Settings::is_saving_settings() && isset( $_POST['cache'] ) && give_is_setting_enabled( give_clean( $_POST['cache'] ) ) )
			|| ( wp_doing_ajax() &&  isset( $_GET['action'] ) && 'give_cache_flush' === give_clean( $_GET['action'] ) )
		) {
			self::$instance->get_incrementer( true );
			self::$instance->get_incrementer( true, 'give-cache-incrementer' );

			/**
			 * Fire the action when all cache deleted.
			 *
			 * @since 2.1.0
			 */
			do_action( 'give_flushed_cache' );

			return true;
		}

		return false;
	}


	/**
	 * Filter the group name
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param $group
	 *
	 * @return mixed
	 */
	public function filter_group_name( $group ) {
		/**
		 * Filter the group name
		 *
		 * @since 2.1.0
		 */
		$filtered_group = apply_filters( 'give_cache_filter_group_name', '', $group );

		if ( empty( $filtered_group ) ) {

			switch ( $group ) {
				case 'give-db-queries':
					$incrementer = self::$instance->get_incrementer();
					break;

				default:
					$incrementer = self::$instance->get_incrementer( false, 'give-cache-incrementer' );

			}

			$current_blog_id = get_current_blog_id();
			$filtered_group   = "{$group}_{$current_blog_id}_{$incrementer}";
		}

		return $filtered_group;
	}


	/**
	 * Disable cache.
	 *
	 * @since  2.0
	 * @access public
	 */
	public static function disable() {
		self::get_instance()->is_cache = false;
	}

	/**
	 * Enable cache.
	 *
	 * @since  2.0
	 * @access public
	 */
	public static function enable() {
		self::get_instance()->is_cache = true;
	}
}

// Initialize
Give_Cache::get_instance();
