<?php
/**
 * Delete Test Transactions
 *
 * This class handles batch processing of deleting test transactions
 *
 * @subpackage  Admin/Tools/Give_Tools_Delete_Test_Transactions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Tools_Delete_Test_Transactions Class
 *
 * @since 1.5
 */
class Give_Tools_Delete_Test_Transactions extends Give_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 1.5
	 */
	public $export_type = '';

	/**
	 * Allows for a non-form batch processing to be run.
	 * @since  1.5
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 * @since  1.5
	 * @var integer
	 */
	public $per_step = 30;

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since  1.5
	 * @global object       $wpdb      Used to query the database using the WordPress Database API
	 * @global Give_Logging $give_logs Used to query logs
	 *
	 * @return array|bool $data The data for the CSV file
	 */
	public function get_data() {
		/* @var Give_Logging $give_logs */
		/* @var wpdb $wpdb */
		global $wpdb, $give_logs;

		$all_payment_ids = $this->get_stored_data( 'give_temp_delete_test_ids' );

		// Bailout.
		if ( empty( $all_payment_ids ) ) {
			return false;
		}

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_payment_ids = array_values( array_slice( $all_payment_ids, $offset, $this->per_step, true ) );

		if ( ! empty( $step_payment_ids ) ) {

			$sql = array();
			$step_payment_ids = implode( ',', $step_payment_ids );

			$sql[] = "DELETE FROM $wpdb->posts WHERE id IN ($step_payment_ids)";
			$sql[] = "DELETE FROM $wpdb->postmeta WHERE post_id IN ($step_payment_ids)";
			$sql[] = "DELETE FROM $wpdb->comments WHERE comment_post_ID IN ($step_payment_ids)";
			
			if( $comment_ids = $wpdb->get_col( "SELECT comment_ID FROM $wpdb->comments" ) ) {
				$comment_ids = implode( ',', $comment_ids );
				$sql[] = "DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN ($comment_ids)";
			}

			if( $log_ids = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_give_log_payment_id' AND meta_value IN ($step_payment_ids)" ) ) {
				$log_ids = implode( ',', $log_ids );
				$sql[] = "DELETE FROM $wpdb->posts WHERE ID IN ($log_ids)";
				$sql[] = "DELETE FROM $wpdb->postmeta WHERE post_id IN ($log_ids)";
			}

			if ( ! empty( $sql ) ) {
				foreach ( $sql as $query ) {
					$wpdb->query( $query );
				}
			}

			return true;

		}

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$items = $this->get_stored_data( 'give_temp_delete_test_ids' );
		$total = count( $items );

		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the payments export
	 *
	 * @since 1.5
	 *
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
	}

	/**
	 * Process a step
	 *
	 * @since 1.5
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to delete test transactions.', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;

			return true;
		} else {
			update_option( 'give_earnings_total', give_get_total_earnings( true ) );
			Give_Cache::delete( Give_Cache::get_key('give_estimated_monthly_stats' ) );

			$this->delete_data( 'give_temp_delete_test_ids' );

			// Reset the sequential order numbers
			if ( give_get_option( 'enable_sequential' ) ) {
				delete_option( 'give_last_payment_number' );
			}

			$this->done    = true;
			$this->message = __( 'Test transactions successfully deleted.', 'give' );

			return false;
		}
	}

	/**
	 * Headers
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
		}
	}

	/**
	 * Perform the export
	 *
	 * @access public
	 * @since 1.5
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		give_die();
	}

	/**
	 * Pre Fetch
	 */
	public function pre_fetch() {

		if ( $this->step == 1 ) {
			$this->delete_data( 'give_temp_delete_test_ids' );
		}

		$payment_ids = get_option( 'give_temp_delete_test_ids', array() );

		if ( empty( $payment_ids ) ) {
			$args = apply_filters( 'give_tools_reset_stats_total_args', array(
				'number' => - 1,
				// ONLY TEST MODE TRANSACTIONS!!!
				'meta_query' => array(
					'relation' => 'OR',

					// all payments in test mode
					array(
						'key'   => '_give_payment_mode',
						'value' => 'test'
					),

					//All payment with test Donation gateway
					array(
						'key'   => '_give_payment_gateway',
						'value' => 'manual'
					)
				)
			) );

			$payment = new Give_Payments_Query( $args );
			$payment_ids = ( $payment_ids = $payment->get_payments() ) ?
				wp_list_pluck( $payment_ids, 'ID' ) :
				array();

			// Allow filtering of items to remove with an unassociative array for each item.
			// The array contains the unique ID of the item, and a 'type' for you to use in the execution of the get_data method.
			$payment_ids = apply_filters( 'give_delete_test_items', $payment_ids );

			$this->store_data( 'give_temp_delete_test_ids', $payment_ids );
		}

	}

	/**
	 * Given a key, get the information from the Database Directly
	 *
	 * @since  1.5
	 *
	 * @param  string $key The option_name
	 *
	 * @return mixed       Returns the data from the database
	 */
	private function get_stored_data( $key ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = '%s'", $key ) );

		return empty( $value ) ? false : maybe_unserialize( $value );
	}

	/**
	 * Give a key, store the value
	 *
	 * @since  1.5
	 *
	 * @param  string $key The option_name
	 * @param  mixed $value The value to store
	 *
	 * @return void
	 */
	private function store_data( $key, $value ) {
		global $wpdb;

		$value = maybe_serialize( $value );

		$data = array(
			'option_name'  => $key,
			'option_value' => $value,
			'autoload'     => 'no',
		);

		$formats = array(
			'%s',
			'%s',
			'%s',
		);

		$wpdb->replace( $wpdb->options, $data, $formats );
	}

	/**
	 * Delete an option
	 *
	 * @since  1.5
	 *
	 * @param  string $key The option_name to delete
	 *
	 * @return void
	 */
	private function delete_data( $key ) {
		global $wpdb;
		$wpdb->delete( $wpdb->options, array( 'option_name' => $key ) );
	}

}
