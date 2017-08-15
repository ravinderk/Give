<?php
/**
 * Earnings / Sales Stats
 *
 * @package     Give
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Stats Class
 *
 * This class is for retrieving stats for earnings and sales.
 *
 * Stats can be retrieved for date ranges and pre-defined periods.
 *
 * @since 1.0
 */
class Give_Payment_Stats extends Give_Stats {

	/**
	 * Retrieve sale stats
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $form_id    int          The donation form to retrieve stats for. If false, gets stats for all forms
	 * @param  $start_date string|bool  The starting date for which we'd like to filter our sale stats. If false, we'll use the default start date of `this_month`
	 * @param  $end_date   string|bool  The end date for which we'd like to filter our sale stats. If false, we'll use the default end date of `this_month`
	 * @param  $status     string|array The sale status(es) to count. Only valid when retrieving global stats
	 *
	 * @return float|int                Total amount of donations based on the passed arguments.
	 */
	public function get_sales( $form_id = 0, $start_date = false, $end_date = false, $status = 'publish' ) {

		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}

		$args = array(
			'status'     => 'publish',
			'start_date' => $this->start_date,
			'end_date'   => $this->end_date,
			'fields'     => 'ids',
			'number'     => - 1,
		);

		if ( ! empty( $form_id ) ) {
			$args['give_forms'] = $form_id;
		}

		/* @var Give_Payments_Query $payments */
		$payments = new Give_Payments_Query( $args );
		$payments = $payments->get_payments();

		return count( $payments );
	}


	/**
	 * Retrieve earning stats
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $form_id     int         The donation form to retrieve stats for. If false, gets stats for all forms.
	 * @param  $start_date  string|bool The starting date for which we'd like to filter our donation earnings stats. If false, method will use the default start date of `this_month`.
	 * @param  $end_date    string|bool The end date for which we'd like to filter the donations stats. If false, method will use the default end date of `this_month`.
	 * @param  $gateway_id  string|bool The gateway to get earnings for such as 'paypal' or 'stripe'.
	 *
	 * @return float|int                Total amount of donations based on the passed arguments.
	 */
	public function get_earnings( $form_id = 0, $start_date = false, $end_date = false, $gateway_id = false ) {
		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}

		$args = array(
			'status'     => 'publish',
			'give_forms' => $form_id,
			'start_date' => $this->start_date,
			'end_date'   => $this->end_date,
			'fields'     => 'ids',
			'number'     => - 1,
		);


		// Filter by Gateway ID meta_key
		if ( $gateway_id ) {
			$args['meta_query'][] = array(
				'key'   => '_give_payment_gateway',
				'value' => $gateway_id,
			);
		}

		// Filter by Gateway ID meta_key
		if ( $form_id ) {
			$args['meta_query'][] = array(
				'key'   => '_give_payment_form_id',
				'value' => $form_id,
			);
		}

		if ( ! empty( $args['meta_query'] ) && 1 < count( $args['meta_query'] ) ) {
			$args['meta_query']['relation'] = 'AND';
		}

		$args = apply_filters( 'give_stats_earnings_args', $args );
		$key  = Give_Cache::get_key( 'give_stats', $args );

		//Set transient for faster stats
		$earnings = Give_Cache::get( $key );

		if ( false === $earnings ) {

			$this->timestamp = false;
			$payments        = new Give_Payments_Query( $args );
			$payments        = $payments->get_payments();
			$earnings        = 0;

			if ( ! empty( $payments ) ) {
				foreach ( $payments as $payment ) {
					$earnings += give_get_payment_amount( $payment->ID );
				}

			}

			// Cache the results for one hour
			Give_Cache::set( $key, give_sanitize_amount_for_db( $earnings ), 60 * 60 );
		}

		//return earnings
		return round( $earnings, give_currency_decimal_filter() );

	}

	/**
	 * Retrieve earning stat transient key
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $form_id     int         The donation form to retrieve stats for. If false, gets stats for all forms
	 * @param  $start_date  string|bool The starting date for which we'd like to filter our donation earnings stats. If false, we'll use the default start date of `this_month`
	 * @param  $end_date    string|bool The end date for which we'd like to filter our sale stats. If false, we'll use the default end date of `this_month`
	 * @param  $gateway_id  string|bool The gateway to get earnings for such as 'paypal' or 'stripe'
	 *
	 * @return float|int                Total amount of donations based on the passed arguments.
	 */
	public function get_earnings_cache_key( $form_id = 0, $start_date = false, $end_date = false, $gateway_id = false ) {

		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}

		$args = array(
			'status'     => 'publish',
			'give_forms' => $form_id,
			'start_date' => $this->start_date,
			'end_date'   => $this->end_date,
			'fields'     => 'ids',
			'number'     => - 1,
		);


		// Filter by Gateway ID meta_key
		if ( $gateway_id ) {
			$args['meta_query'][] = array(
				'key'   => '_give_payment_gateway',
				'value' => $gateway_id,
			);
		}

		// Filter by Gateway ID meta_key
		if ( $form_id ) {
			$args['meta_query'][] = array(
				'key'   => '_give_payment_form_id',
				'value' => $form_id,
			);
		}

		if ( ! empty( $args['meta_query'] ) && 1 < count( $args['meta_query'] ) ) {
			$args['meta_query']['relation'] = 'AND';
		}

		$args = apply_filters( 'give_stats_earnings_args', $args );
		$key  = Give_Cache::get_key( 'give_stats', $args );

		//return earnings
		return $key;

	}

	/**
	 * Get the best selling forms
	 *
	 * @since  1.0
	 * @access public
	 * @global wpdb $wpdb
	 *
	 * @param       $number int The number of results to retrieve with the default set to 10.
	 *
	 * @return array       Best selling forms
	 */
	public function get_best_selling( $number = 10 ) {
		global $wpdb;

		$meta_table = __give_v20_bc_table_details( 'form' );

		$give_forms = $wpdb->get_results( $wpdb->prepare(
			"SELECT {$meta_table['column']['id']} as form_id, max(meta_value) as sales
				FROM {$meta_table['name']} WHERE meta_key='_give_form_sales' AND meta_value > 0
				GROUP BY meta_value+0
				DESC LIMIT %d;", $number
		) );

		var_dump( $wpdb->prepare(
			"SELECT {$meta_table['column']['id']} as form_id, max(meta_value) as sales
				FROM {$meta_table['name']} WHERE meta_key='_give_form_sales' AND meta_value > 0
				GROUP BY meta_value+0
				DESC LIMIT %d;", $number
		));

		return $give_forms;
	}

}