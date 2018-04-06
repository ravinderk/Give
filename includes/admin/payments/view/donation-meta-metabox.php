<div id="give-order-details" class="postbox give-order-data">

	<h3 class="hndle"><?php _e( 'Donation Meta', 'give' ); ?></h3>

	<div class="inside">
		<div class="give-admin-box">

			<?php
			/**
			 * Fires in donation details page, before the donation-meta metabox.
			 *
			 * @since 1.0
			 *
			 * @param int $payment_id Payment id.
			 */
			do_action( 'give_view_donation_details_payment_meta_before', $this->donation->ID );

			$gateway = give_get_payment_gateway( $this->donation->ID );
			if ( $gateway ) :
				?>
				<div class="give-order-gateway give-admin-box-inside">
					<p>
						<strong><?php _e( 'Gateway:', 'give' ); ?></strong>&nbsp;
						<?php echo give_get_gateway_admin_label( $gateway ); ?>
					</p>
				</div>
			<?php endif; ?>

			<div class="give-order-payment-key give-admin-box-inside">
				<p>
					<strong><?php _e( 'Key:', 'give' ); ?></strong>&nbsp;
					<?php echo give_get_payment_key( $this->donation->ID ); ?>
				</p>
			</div>

			<div class="give-order-ip give-admin-box-inside">
				<p>
					<strong><?php _e( 'IP:', 'give' ); ?></strong>&nbsp;
					<?php echo esc_html( give_get_payment_user_ip( $this->donation->ID ) ); ?>
				</p>
			</div>

			<?php
			// Display the transaction ID present.
			// The transaction ID is the charge ID from the gateway.
			// For instance, stripe "ch_BzvwYCchqOy5Nt".
			if ( $this->donation->transaction_id != $this->donation->ID ) : ?>
				<div class="give-order-tx-id give-admin-box-inside">
					<p>
						<strong>
							<?php _e( 'Transaction ID:', 'give' ); ?>&nbsp;
							<span
								class="give-tooltip give-icon give-icon-question"
								data-tooltip="<?php echo sprintf( esc_attr__( 'The transaction ID within %s.', 'give' ), $gateway ); ?>">
							</span>
						</strong>&nbsp;
						<?php echo apply_filters( "give_payment_details_transaction_id-{$gateway}", $this->donation->transaction_id, $this->donation->ID ); ?>
					</p>
				</div>
			<?php endif; ?>

			<div class="give-admin-box-inside">
				<p><?php $purchase_url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&donor=' . absint( give_get_payment_donor_id( $this->donation->ID ) ) ); ?>
					<a href="<?php echo $purchase_url; ?>"><?php _e( 'View all donations for this donor &raquo;', 'give' ); ?></a>
				</p>
			</div>

			<?php
			/**
			 * Fires in donation details page, after the donation-meta metabox.
			 *
			 * @since 1.0
			 *
			 * @param int $payment_id Payment id.
			 */
			do_action( 'give_view_donation_details_payment_meta_after', $this->donation->ID );
			?>

		</div>
		<!-- /.column-container -->

	</div>
	<!-- /.inside -->

</div>
<!-- /#give-order-data -->