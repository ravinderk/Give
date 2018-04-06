<h1 id="transaction-details-heading" class="wp-heading-inline">
	<?php
	printf(
	/* translators: %s: donation number */
		esc_html__( 'Donation %s', 'give' ),
		$this->donation->number
	);


	if ( 'test' == $this->donation->mode ) {
		echo Give()->tooltips->render_span( array(
			'label'       => __( 'This donation was made in test mode.', 'give' ),
			'tag_content' => __( 'Test Donation', 'give' ),
			'position'    => 'right',
			'attributes'  => array(
				'id'    => 'test-payment-label',
				'class' => 'give-item-label give-item-label-orange'
			)
		) );
	}
	?>
</h1>

<?php
/**
 * Fires in donation details page, before the page content and after the H1 title output.
 *
 * @since 1.0
 *
 * @param int $payment_id Payment id.
 */
do_action( 'give_view_donation_details_before', $this->donation->ID );

echo '<hr class="wp-header-end">';