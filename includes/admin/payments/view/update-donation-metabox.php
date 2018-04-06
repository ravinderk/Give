<?php $donation_date = strtotime( $this->donation->date ); ?>
<div id="give-order-update" class="postbox give-order-data">

	<h3 class="hndle"><?php _e( 'Update Donation', 'give' ); ?></h3>

	<div class="inside">
		<div class="give-admin-box">

			<?php
			/**
			 * Fires in donation details page, before the sidebar update-payment metabox.
			 *
			 * @since 1.0
			 *
			 * @param int $payment_id Payment id.
			 */
			do_action( 'give_view_donation_details_totals_before', $this->donation->ID );
			?>

			<div class="give-admin-box-inside">
				<p>
					<label for="give-payment-status"
					       class="strong"><?php _e( 'Status:', 'give' ); ?></label>&nbsp;
					<select id="give-payment-status" name="give-payment-status"
					        class="medium-text">
						<?php foreach ( give_get_payment_statuses() as $key => $status ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $this->donation->status, $key, true ); ?>>
								<?php echo esc_html( $status ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="give-donation-status status-<?php echo sanitize_title( $this->donation->status ); ?>">
						<span class="give-donation-status-icon"></span>
					</span>
				</p>
			</div>

			<div class="give-admin-box-inside">
				<p>
					<label for="give-payment-date"
					       class="strong"><?php _e( 'Date:', 'give' ); ?></label>&nbsp;
					<input type="text" id="give-payment-date"
					       name="give-payment-date"
					       value="<?php echo esc_attr( date( 'm/d/Y', $donation_date ) ); ?>"
					       class="medium-text give_datepicker"/>
				</p>
			</div>

			<div class="give-admin-box-inside">
				<p>
					<label for="give-payment-time-hour"
					       class="strong"><?php _e( 'Time:', 'give' ); ?></label>&nbsp;
					<input type="number" step="1" max="24"
					       id="give-payment-time-hour"
					       name="give-payment-time-hour"
					       value="<?php echo esc_attr( date_i18n( 'H', $donation_date ) ); ?>"
					       class="small-text give-payment-time-hour"/>&nbsp;:&nbsp;
					<input type="number" step="1" max="59"
					       id="give-payment-time-min"
					       name="give-payment-time-min"
					       value="<?php echo esc_attr( date( 'i', $donation_date ) ); ?>"
					       class="small-text give-payment-time-min"/>
				</p>
			</div>

			<?php
			/**
			 * Fires in donation details page, in the sidebar update-payment metabox.
			 *
			 * Allows you to add new inner items.
			 *
			 * @since 1.0
			 *
			 * @param int $payment_id Payment id.
			 */
			do_action( 'give_view_donation_details_update_inner', $this->donation->ID );
			?>

			<div class="give-order-payment give-admin-box-inside">
				<p>
					<label for="give-payment-total"
					       class="strong"><?php _e( 'Total Donation:', 'give' ); ?></label>&nbsp;
					<?php echo give_currency_symbol( $this->donation->currency ); ?>
					&nbsp;<input id="give-payment-total" name="give-payment-total"
					             type="text" class="small-text give-price-field"
					             value="<?php echo esc_attr( give_format_decimal( give_donation_amount( $this->donation->ID ), false, false ) ); ?>"/>
				</p>
			</div>

			<?php
			/**
			 * Fires in donation details page, after the sidebar update-donation metabox.
			 *
			 * @since 1.0
			 *
			 * @param int $payment_id Payment id.
			 */
			do_action( 'give_view_donation_details_totals_after', $this->donation->ID );
			?>

		</div>
		<!-- /.give-admin-box -->

	</div>
	<!-- /.inside -->

	<div class="give-order-update-box give-admin-box">
		<?php
		/**
		 * Fires in donation details page, before the sidebar update-payment metabox actions buttons.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_view_donation_details_update_before', $this->donation->ID );
		?>

		<div id="major-publishing-actions">
			<div id="publishing-action">
				<input type="submit" class="button button-primary right"
				       value="<?php esc_attr_e( 'Save Donation', 'give' ); ?>"/>
				<?php
				if ( give_is_payment_complete( $this->donation->ID ) ) {
					echo sprintf(
						'<a href="%1$s" id="give-resend-receipt" class="button-secondary right">%2$s</a>',
						esc_url(
							add_query_arg(
								array(
									'give-action' => 'email_links',
									'purchase_id' => $this->donation->ID,
								)
							)
						),
						__( 'Resend Receipt', 'give' )
					);
				}
				?>
			</div>
			<div class="clear"></div>
		</div>

		<?php
		/**
		 * Fires in donation details page, after the sidebar update-payment metabox actions buttons.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_view_donation_details_update_after', $this->donation->ID );
		?>

	</div>
	<!-- /.give-order-update-box -->

</div>
<!-- /#give-order-data -->