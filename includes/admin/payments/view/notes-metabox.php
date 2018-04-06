<div id="give-payment-notes" class="postbox">
	<h3 class="hndle"><?php _e( 'Donation Notes', 'give' ); ?></h3>

	<div class="inside">
		<div id="give-payment-notes-inner">
			<?php
			$notes = give_get_payment_notes( $this->donation->ID );
			if ( ! empty( $notes ) ) {
				$no_notes_display = ' style="display:none;"';
				foreach ( $notes as $note ) :

					echo give_get_payment_note_html( $note, $this->donation->ID );

				endforeach;
			} else {
				$no_notes_display = '';
			}

			echo '<p class="give-no-payment-notes"' . $no_notes_display . '>' . esc_html__( 'No donation notes.', 'give' ) . '</p>';
			?>
		</div>
		<textarea name="give-payment-note" id="give-payment-note" class="large-text"></textarea>

		<div class="give-clearfix">
			<button id="give-add-payment-note"
					class="button button-secondary button-small"
					data-payment-id="<?php echo absint( $this->donation->ID ); ?>">
				<?php _e( 'Add Note', 'give' ); ?>
			</button>
		</div>

	</div>
	<!-- /.inside -->
</div>
<!-- /#give-payment-notes -->