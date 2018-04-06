<div id="postbox-container-1" class="postbox-container">
	<div id="side-sortables" class="meta-box-sortables ui-sortable">

		<?php
		/**
		 * Fires in donation details page, before the sidebar.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_view_donation_details_sidebar_before', $this->donation->ID );

		include_once 'update-donation-metabox.php';
		include_once 'donation-meta-metabox.php';

		/**
		 * Fires in donation details page, before the sidebar.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_view_donation_details_sidebar_metabox', $this->donation );

		/**
		 * Fires in donation details page, after the sidebar.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_view_donation_details_sidebar_after', $this->donation->ID );
		?>

	</div>
	<!-- /#side-sortables -->
</div>
<!-- /#postbox-container-1 -->