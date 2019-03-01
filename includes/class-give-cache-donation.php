<?php
/**
 * Class for managing cache
 * Note: only use for internal purpose.
 *
 * @package     Give
 * @subpackage  Classes/Give_Cache_Donation
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Cache_Donation {
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
	 * @return Give_Cache_Donation
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
	 * Delete payment related cache
	 * Note: only use for internal purpose.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param int $donation_id
	 */
	public function delete_payment_related_cache( $donation_id ) {
		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $donation_id ) ) {
			return;
		}

		if ( $donation_id && ( $donor_id = give_get_payment_donor_id( $donation_id ) ) ) {
			wp_cache_delete( $donor_id, $this->give_cache_instance->filter_group_name( 'give-donors' ) );
		}

		wp_cache_delete( $donation_id, $this->give_cache_instance->filter_group_name( 'give-donations' ) );

		$this->give_cache_instance->get_incrementer( true );
	}

	/**
	 * Delete donations related cache
	 * Note: only use for internal purpose.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param string $id
	 * @param string $group
	 * @param int    $expire
	 */
	public function delete_donations_related_cache( $id, $group, $expire ) {
		if ( $id && ( $donor_id = give_get_payment_donor_id( $id ) ) ) {
			wp_cache_delete( $donor_id, $this->give_cache_instance->filter_group_name( 'give-donors' ) );
		}

		$this->give_cache_instance->get_incrementer( true );
	}
}

Give_Cache_Donation::get_instance();
