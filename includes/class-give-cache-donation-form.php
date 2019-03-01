<?php
/**
 * Class for managing cache
 * Note: only use for internal purpose.
 *
 * @package     Give
 * @subpackage  Classes/Give_Cache_Donation_Form
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Cache_Donation_Form {
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
	 * @return Give_Cache_Donation_Form
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

		add_action( 'save_post_give_forms', array( $this, '__delete_all_cache' ) );
	}

	/**
	 * Delete form related cache
	 * Note: only use for internal purpose.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param int $form_id
	 */
	public function __delete_all_cache( $form_id ) {
		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $form_id ) ) {
			return;
		}

		$donation_query = new Give_Payments_Query(
			array(
				'number'     => - 1,
				'give_forms' => $form_id,
				'output'     => '',
				'fields'     => 'ids',
			)
		);

		$donations = $donation_query->get_payments();

		if ( ! empty( $donations ) ) {
			/* @var Give_Payment $donation */
			foreach ( $donations as $donation_id ) {
				wp_cache_delete( $donation_id, $this->give_cache_instance->filter_group_name( 'give-donations' ) );
				wp_cache_delete( give_get_payment_donor_id( $donation_id ), $this->give_cache_instance->filter_group_name( 'give-donors' ) );
			}
		}

		$this->give_cache_instance->get_incrementer( true );
	}
}

Give_Cache_Donation_Form::get_instance();
