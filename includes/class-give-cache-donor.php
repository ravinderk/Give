<?php
/**
 * Class for managing cache
 * Note: only use for internal purpose.
 *
 * @package     Give
 * @subpackage  Classes/Give_Cache_Donor
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Cache_Donor {
	/**
	 * Instance.
	 *
	 * @since  2.4.0
	 * @access private
	 * @var
	 */
	static private $instance;

	/**
	 * Instance.
	 *
	 * @since  2.4.0
	 * @access private
	 *
	 * @var Give_Cache
	 */
	private $give_cache_instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.4.0
	 * @access public
	 * @return Give_Cache_Donor
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function setup() {
		$this->give_cache_instance = Give_Cache::get_instance();

		add_action( 'give_deleted_give-donors_cache', array( $this, 'delete_donor_related_cache' ), 10, 3 );
	}

	/**
	 * Delete donor related cache
	 * Note: only use for internal purpose.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param string $id
	 * @param string $group
	 * @param int    $expire
	 */
	public function delete_donor_related_cache( $id, $group, $expire ) {
		$donation_ids = Give()->donors->get_column( 'payment_ids', $id );

		if ( ! empty( $donation_ids ) ) {
			$donation_ids = array_map( 'trim', (array) explode( ',', trim( $donation_ids  ) ) );

			foreach ( $donation_ids as $donation ) {
				wp_cache_delete( $donation, $this->give_cache_instance->filter_group_name( 'give-donations' ) );
			}
		}

		$this->give_cache_instance->get_incrementer( true );
	}
}

Give_Cache_Donor::get_instance();
