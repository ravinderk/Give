<?php $column_count = 'columns-3'; ?>
<div id="give-donation-overview" class="postbox <?php echo $column_count; ?>">
	<h3 class="hndle"><?php _e( 'Donation Information', 'give' ); ?></h3>
	<div class="inside">
		<div class="column-container">

			<div class="column">
				<!-- Form ID -->
				<p>
					<strong><?php _e( 'Donation Form ID:', 'give' ); ?></strong><br>
					<?php
					if ( $this->donation->form_id ) :
						printf(
							'<a href="%1$s">%2$s</a>',
							admin_url( "post.php?action=edit&post={$this->donation->form_id}" ),
							$this->donation->form_id
						);
					endif;
					?>
				</p>

				<!-- Form Title -->
				<p>
					<strong><?php esc_html_e( 'Donation Form Title:', 'give' ); ?></strong><br>
					<?php
					echo Give()->html->forms_dropdown(
						array(
							'selected'    => $this->donation->form_id,
							'name'        => 'give-payment-form-select',
							'id'          => 'give-payment-form-select',
							'chosen'      => true,
							'placeholder' => '',
						)
					);
					?>
				</p>
			</div>

			<div class="column">
				<!-- Donaiton Date -->
				<p>
					<strong><?php _e( 'Donation Date:', 'give' ); ?></strong><br>
					<?php echo date_i18n( give_date_format(), strtotime( $this->donation->date ) ); ?>
				</p>

				<!-- Donation Level -->
				<p>
					<strong><?php _e( 'Donation Level:', 'give' ); ?></strong><br>
					<span class="give-donation-level">
						<?php
						$var_prices = give_has_variable_prices( $this->donation->form_id );

						if ( empty( $var_prices ) ) {
							_e( 'n/a', 'give' );
						} else {
							$prices_atts = array();

							if ( $variable_prices = give_get_variable_prices( $this->donation->form_id ) ) {

								foreach ( $variable_prices as $variable_price ) {
									$prices_atts[ $variable_price['_give_id']['level_id'] ] = give_format_amount( $variable_price['_give_amount'], array( 'sanitize' => false ) );
								}
							}

							// Variable price dropdown options.
							$variable_price_dropdown_option = array(
								'id'               =>  $this->donation->form_id,
								'name'             => 'give-variable-price',
								'chosen'           => true,
								'show_option_all'  => '',
								'show_option_none' => ( '' === get_post_meta(  $this->donation->ID, '_give_payment_price_id', true ) ?__( 'None', 'give' ) : '' ),
								'select_atts'      => 'data-prices=' . esc_attr( wp_json_encode( $prices_atts ) ),
								'selected'         =>  $this->donation->price_id,
							);
							// Render variable prices select tag html.
							give_get_form_variable_price_dropdown( $variable_price_dropdown_option, true );
						}

						?>
					</span>
				</p>
			</div>

			<div class="column">

				<!-- Donation Total -->
				<p>
					<strong><?php esc_html_e( 'Total Donation:', 'give' ); ?></strong><br>
					<?php echo give_donation_amount(  $this->donation->ID, true ); ?>
				</p>

				<p>
					<?php
					/**
					 * Fires in donation details page, in the donation-information metabox, before the head elements.
					 *
					 * Allows you to add new TH elements at the beginning.
					 *
					 * @since 1.0
					 *
					 * @param int $payment_id Payment id.
					 */
					do_action( 'give_donation_details_thead_before',  $this->donation->ID );


					/**
					 * Fires in donation details page, in the donation-information metabox, after the head elements.
					 *
					 * Allows you to add new TH elements at the end.
					 *
					 * @since 1.0
					 *
					 * @param int $payment_id Payment id.
					 */
					do_action( 'give_donation_details_thead_after',  $this->donation->ID );

					/**
					 * Fires in donation details page, in the donation-information metabox, before the body elements.
					 *
					 * Allows you to add new TD elements at the beginning.
					 *
					 * @since 1.0
					 *
					 * @param int $payment_id Payment id.
					 */
					do_action( 'give_donation_details_tbody_before',  $this->donation->ID );

					/**
					 * Fires in donation details page, in the donation-information metabox, after the body elements.
					 *
					 * Allows you to add new TD elements at the end.
					 *
					 * @since 1.0
					 *
					 * @param int $payment_id Payment id.
					 */
					do_action( 'give_donation_details_tbody_after',  $this->donation->ID );
					?>
				</p>
			</div>
		</div>

	</div>
	<!-- /.inside -->

</div>
<!-- /#give-donation-overview -->