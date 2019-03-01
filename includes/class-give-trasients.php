<?php
/**
 * Class for managing cache
 * Note: only use for internal purpose.
 *
 * @package     Give
 * @subpackage  Classes/Give_Transient
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Transient {
	/**
	 * Instance.
	 *
	 * @since  2.4.0
	 * @access protected
	 * @var Give_Transient
	 */
	static protected $instance;

	/**
	 * Flag to check if caching enabled or not.
	 *
	 * @since  2.4.0
	 * @access protected
	 * @var Give_Cache
	 */
	private $give_cache_instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.4.0
	 * @access private
	 * Give_Transient constructor.
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.4.0
	 * @access public
	 * @return static
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Give_Transient ) ) {
			self::$instance = new Give_Transient();

			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup hooks.
	 *
	 * @since  2.4.0
	 * @access public
	 */
	public function setup() {
		$this->give_cache_instance = Give_Cache::get_instance();

		// weekly delete all expired cache.
		Give_Cron::add_weekly_event( array( __CLASS__, 'delete_expired_transients' ) );
	}


	/**
	 * Get cache key.
	 *
	 * @since  2.4.0
	 *
	 * @param  string $action     Cache key prefix.
	 * @param  array  $query_args (optional) Query array.
	 * @param  bool   $is_prefix
	 *
	 * @return string
	 */
	public static function get_key( $action, $query_args = null, $is_prefix = true ) {

		return Give_Cache::get_key( $action, $query_args, $is_prefix );
	}

	/**
	 * Get cache.
	 *
	 * @since  2.4.0
	 *
	 * @param  string $cache_key
	 * @param  bool   $custom_key
	 * @param  mixed  $query_args
	 *
	 * @return mixed
	 */
	public static function get( $cache_key, $custom_key = false, $query_args = array() ) {
		if ( ! self::is_valid_cache_key( $cache_key ) ) {
			if ( empty( $cache_key ) ) {
				return new WP_Error( 'give_empty_cache_key', __( 'Do not pass invalid empty cache key', 'give' ) );
			} elseif ( ! $custom_key ) {
				return new WP_Error( 'give_invalid_cache_key', __( 'Cache key format should be give_cache_*', 'give' ) );
			}

			$cache_key = self::get_key( $cache_key, $query_args );
		}

		$option = get_option( $cache_key );

		// Backward compatibility (<2.4.0).
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
	 * Get list of options like.
	 *
	 * Note: only for internal use
	 *
	 * @since  2.4.0
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
	 * Set cache.
	 *
	 * @since  2.4.0
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
	 * @since  2.4.0
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
	 * Check cache key validity.
	 *
	 * @since  2.4.0
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
	 * Delete expired transients cache.
	 *
	 * Note: only for internal use
	 *
	 * @since  2.4.0
	 * @access public
	 * @global wpdb $wpdb
	 *
	 * @param bool  $force If set to true then all cached values will be delete instead of only expired
	 *
	 * @return bool
	 */
	public static function delete_expired_transients( $force = false ) {
		global $wpdb;
		$options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value
						FROM {$wpdb->options}
						Where option_name
						LIKE '%%%s%%'",
				'give_cache'
			),
			ARRAY_A
		);

		// Bailout.
		if ( empty( $options ) ) {
			return false;
		}

		$current_time = current_time( 'timestamp', 1 );

		// Delete log cache.
		foreach ( $options as $option ) {
			$option['option_value'] = maybe_unserialize( $option['option_value'] );

			if (
				(
					! self::is_valid_cache_key( $option['option_name'] )
					|| ! is_array( $option['option_value'] ) // Backward compatibility (<2.4.0).
					|| ! array_key_exists( 'expiration', $option['option_value'] ) // Backward compatibility (<2.4.0).
					|| empty( $option['option_value']['expiration'] )
					|| ( $current_time < $option['option_value']['expiration'] )
				)
				&& ! $force
			) {
				continue;
			}

			self::delete( $option['option_name'] );
		}
	}
}

// Initialize
Give_Transient::get_instance();
