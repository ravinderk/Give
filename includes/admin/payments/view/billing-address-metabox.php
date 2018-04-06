<?php
/**
 * Fires on the donation details page, before the billing metabox.
 *
 * @since 1.0
 *
 * @param int $payment_id Payment id.
 */
do_action( 'give_view_donation_details_billing_before', $this->donation->ID );
?>

<div id="give-billing-details" class="postbox">
	<h3 class="hndle"><?php _e( 'Billing Address', 'give' ); ?></h3>

	<div class="inside">

		<div id="give-order-address">

			<div class="order-data-address">
				<div class="data column-container">

					<?php
					$address = $this->donation->address;

					$address['country'] = ( ! empty( $address['country'] ) ? $address['country'] : give_get_country() );
					$address['state'] = ( ! empty( $address['state'] ) ? $address['state'] : '' );

					// Get the country list that does not have any states init.
					$no_states_country = give_no_states_country_list();
					?>

					<div class="row">
						<div id="give-order-address-country-wrap">
							<label class="order-data-address-line"><?php _e( 'Country:', 'give' ); ?></label>
							<?php
							echo Give()->html->select(
								array(
									'options'          => give_get_country_list(),
									'name'             => 'give-payment-address[0][country]',
									'selected'         => $address['country'],
									'show_option_all'  => false,
									'show_option_none' => false,
									'chosen'           => true,
									'placeholder'      => esc_attr__( 'Select a country', 'give' ),
									'data'             => array( 'search-type' => 'no_ajax' ),
								)
							);
							?>
						</div>
					</div>

					<div class="row">
						<div class="give-wrap-address-line1">
							<label for="give-payment-address-line1"
							       class="order-data-address"><?php _e( 'Address 1:', 'give' ); ?></label>
							<input id="give-payment-address-line1" type="text"
							       name="give-payment-address[0][line1]"
							       value="<?php echo esc_attr( $address['line1'] ); ?>"
							       class="medium-text"/>
						</div>
					</div>

					<div class="row">
						<div class="give-wrap-address-line2">
							<label for="give-payment-address-line2"
							       class="order-data-address-line"><?php _e( 'Address 2:', 'give' ); ?></label>
							<input id="give-payment-address-line2" type="text"
							       name="give-payment-address[0][line2]"
							       value="<?php echo esc_attr( $address['line2'] ); ?>"
							       class="medium-text"/>
						</div>
					</div>

					<div class="row">
						<div class="give-wrap-address-city">
							<label for="give-payment-address-city"
							       class="order-data-address-line"><?php esc_html_e( 'City:', 'give' ); ?></label>
							<input id="give-payment-address-city" type="text"
							       name="give-payment-address[0][city]"
							       value="<?php echo esc_attr( $address['city'] ); ?>"
							       class="medium-text"/>
						</div>
					</div>

					<?php
					$state_exists = ( ! empty( $address['country'] ) && array_key_exists( $address['country'], $no_states_country ) ? true : false );
					?>
					<div class="row">
						<div class="<?php echo( ! empty( $state_exists ) ? 'column-full' : 'column' ); ?> give-column give-column-state">
							<div id="give-order-address-state-wrap"
							     class="<?php echo( ! empty( $state_exists ) ? 'give-hidden' : '' ); ?>">
								<label for="give-payment-address-state"
								       class="order-data-address-line"><?php esc_html_e( 'State / Province / County:', 'give' ); ?></label>
								<?php
								$states = give_get_states( $address['country'] );
								if ( ! empty( $states ) ) {
									echo Give()->html->select(
										array(
											'options'          => $states,
											'name'             => 'give-payment-address[0][state]',
											'selected'         => $address['state'],
											'show_option_all'  => false,
											'show_option_none' => false,
											'chosen'           => true,
											'placeholder'      => esc_attr__( 'Select a state', 'give' ),
											'data'             => array( 'search-type' => 'no_ajax' ),
										)
									);
								} else {
									?>
									<input id="give-payment-address-state"
									       type="text"
									       name="give-payment-address[0][state]"
									       value="<?php echo esc_attr( $address['state'] ); ?>"
									       class="medium-text"/>
									<?php
								}
								?>
							</div>
						</div>

						<div class="<?php echo( ! empty( $state_exists ) ? 'column-full' : 'column' ); ?> give-column give-column-zip">
							<div class="give-wrap-address-zip">
								<label for="give-payment-address-zip"
								       class="order-data-address-line"><?php _e( 'Zip / Postal Code:', 'give' ); ?></label>
								<input id="give-payment-address-zip" type="text"
								       name="give-payment-address[0][zip]"
								       value="<?php echo esc_attr( $address['zip'] ); ?>"
								       class="medium-text"/>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /#give-order-address -->

		<?php
		/**
		 * Fires in donation details page, in the billing metabox, after all the fields.
		 *
		 * Allows you to insert new billing address fields.
		 *
		 * @since 1.7
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_payment_billing_details', $this->donation->ID );
		?>

	</div>
	<!-- /.inside -->
</div>
<!-- /#give-billing-details -->

<?php
/**
 * Fires on the donation details page, after the billing metabox.
 *
 * @since 1.0
 *
 * @param int $payment_id Payment id.
 */
do_action( 'give_view_donation_details_billing_after', $this->donation->ID );
?>