<?php
/**
 * Fires on the donation details page.
 *
 * @since 1.0
 *
 * @param int $payment_id Payment id.
 */
do_action( 'give_view_donation_details_donor_detail_before', $this->donation->ID );
?>

<div id="give-donor-details" class="postbox">
	<h3 class="hndle"><?php _e( 'Donor Details', 'give' ); ?></h3>

	<div class="inside">

		<div class="column-container donor-info">
			<div class="column">
				<p>
					<strong><?php _e( 'Donor ID:', 'give' ); ?></strong><br>
					<?php
					if ( ! empty( $this->donation->donor_id ) ) {
						printf(
							'<a href="%1$s">%2$s</a>',
							admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $this->donation->donor_id ),
							$this->donation->donor_id
						);
					}
					?>
					<span>(<a href="#new"
							  class="give-payment-new-donor"><?php _e( 'Create New Donor', 'give' ); ?></a>)</span>
				</p>
				<p>
					<strong><?php _e( 'Donor Since:', 'give' ); ?></strong><br>
					<?php echo date_i18n( give_date_format(), strtotime( Give()->donors->get_column( 'date_created', $this->donation->donor_id ) ) ) ?>
				</p>
			</div>
			<div class="column">
				<p>
					<strong><?php _e( 'Donor Name:', 'give' ); ?></strong><br>
					<?php
					$donor_billing_name = give_get_donor_name_by( $this->donation->ID, 'donation' );
					$donor_name         = give_get_donor_name_by( $this->donation->donor_id, 'donor' );

					// Check whether the donor name and WP_User name is same or not.
					if ( sanitize_title( $donor_billing_name ) != sanitize_title( $donor_name ) ) {
						echo $donor_billing_name . ' (<a href="' . esc_url( admin_url( "edit.php?post_type=give_forms&page=give-donors&view=overview&id={$this->donation->donor_id}" ) ) . '">' . $donor_name . '</a>)';
					} else {
						echo $donor_name;
					}
					?>
				</p>
				<p>
					<strong><?php _e( 'Donor Email:', 'give' ); ?></strong><br>
					<?php echo $this->donation->email; ?>
				</p>
			</div>
			<div class="column">
				<p>
					<strong><?php _e( 'Change Donor:', 'give' ); ?></strong><br>
					<?php
					echo Give()->html->donor_dropdown(
						array(
							'selected' => $this->donation->donor_id,
							'name'     => 'donor-id',
						)
					);
					?>
				</p>
				<p>
					<?php if ( ! empty( $this->donation->company ) ) {
						?>
						<strong><?php esc_html_e( 'Company Name:', 'give' ); ?></strong>
						<br>
						<?php
						echo $this->donation->company;
					} ?>
				</p>
			</div>
		</div>

		<div class="column-container new-donor" style="display: none">
			<div class="column">
				<p>
					<label for="give-new-donor-first-name"><?php _e( 'New Donor First Name:', 'give' ); ?></label>
					<input id="give-new-donor-first-name" type="text"
						   name="give-new-donor-first-name" value=""
						   class="medium-text"/>
				</p>
			</div>
			<div class="column">
				<p>
					<label for="give-new-donor-last-name"><?php _e( 'New Donor Last Name:', 'give' ); ?></label>
					<input id="give-new-donor-last-name" type="text"
						   name="give-new-donor-last-name" value=""
						   class="medium-text"/>
				</p>
			</div>
			<div class="column">
				<p>
					<label for="give-new-donor-email"><?php _e( 'New Donor Email:', 'give' ); ?></label>
					<input id="give-new-donor-email" type="email"
						   name="give-new-donor-email" value=""
						   class="medium-text"/>
				</p>
			</div>
			<div class="column">
				<p>
					<input type="hidden" name="give-current-donor"
						   value="<?php echo $this->donation->donor_id; ?>"/>
					<input type="hidden" id="give-new-donor" name="give-new-donor"
						   value="0"/>
					<a href="#cancel"
					   class="give-payment-new-donor-cancel give-delete"><?php _e( 'Cancel', 'give' ); ?></a>
					<br>
					<em><?php _e( 'Click "Save Donation" to create new donor.', 'give' ); ?></em>
				</p>
			</div>
		</div>
		<?php
		/**
		 * Fires on the donation details page, in the donor-details metabox.
		 *
		 * The hook is left here for backwards compatibility.
		 *
		 * @since 1.7
		 *
		 * @param array $payment_meta Payment meta.
		 * @param array $user_info    User information.
		 */
		do_action( 'give_payment_personal_details_list', $this->donation->payment_meta, $this->donation->user_info );

		/**
		 * Fires on the donation details page, in the donor-details metabox.
		 *
		 * @since 1.7
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_payment_view_details', $this->donation->ID );
		?>

	</div>
	<!-- /.inside -->
</div>
<!-- /#give-donor-details -->