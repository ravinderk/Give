<div id="postbox-container-2" class="postbox-container">

	<div id="normal-sortables" class="meta-box-sortables ui-sortable">

		<?php
		/**
		 * Fires in donation details page, before the main area.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_view_donation_details_main_before', $this->donation->ID );

		include_once 'donation-information-metabox.php';
		include_once 'donor-detail-metabox.php';
		include_once 'billing-address-metabox.php';
		include_once 'notes-metabox.php';

		/**
		 * Fires in donation details page, before the main area.
		 *
		 * @since 2.1.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_view_donation_details_main_content_metabox', $this->donation );

		/**
		 * Fires on the donation details page, after the main area.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_view_donation_details_main_after', $this->donation->ID );
		?>

	</div>
	<!-- /#normal-sortables -->
</div>
<!-- #postbox-container-2 -->