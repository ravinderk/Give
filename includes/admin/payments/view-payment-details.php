<?php
/**
 * View Donation Details
 *
 * @package     Give
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'view_give_payments' ) ) {
	wp_die(
		__( 'Sorry, you are not allowed to access this page.', 'give' ), __( 'Error', 'give' ), array(
			'response' => 403,
		)
	);
}

class Give_Donation_Detail_Page {

	/**
	 * Donation onbect.
	 *
	 * @since  2.1.0
	 * @access private
	 *
	 * @var Give_Payment
	 */
	private $donation;

	/**
	 * Instance.
	 *
	 * @since  2.1.0
	 * @access private
	 * @var
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.1.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.1.0
	 * @access static
	 * @return Give_Donation_Detail_Page
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();

			self::$instance->render();
		}

		return self::$instance;
	}

	/**
	 * Render donation detail page
	 *
	 * @since  2.1.0
	 * @access public
	 */
	public function render() {
		// Page validation.
		if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
			wp_die(
				__( 'Donation ID not supplied. Please try again.', 'give' ),
				__( 'Error', 'give' ),
				array( 'response' => 400 )
			);
		}

		$this->donation = new Give_Payment( absint( $_GET['id'] ) );

		// Verify Donation.
		if ( ! $this->donation->ID ) {
			wp_die(
				__( 'The specified ID does not belong to a donation. Please try again.', 'give' ),
				__( 'Error', 'give' ),
				array( 'response' => 400 )
			);
		}
		?>
		<div class="wrap give-wrap">

			<?php include_once 'view/header.php'; ?>

			<form id="give-edit-order-form" method="post">

				<?php
				/**
				 * Fires in donation details page, in the form before the order details.
				 *
				 * @since 1.0
				 *
				 * @param int $payment_id Payment id.
				 */
				do_action( 'give_view_donation_details_form_top', $this->donation->ID );
				?>

				<div id="poststuff">
					<div id="give-dashboard-widgets-wrap">
						<div id="post-body" class="metabox-holder columns-2">
							<?php include_once 'view/sidebar.php'; ?>
							<?php include_once 'view/main-content.php'; ?>
						</div>
						<!-- /#post-body -->
					</div>
					<!-- #give-dashboard-widgets-wrap -->
				</div>
				<!-- /#post-stuff -->

				<?php
				/**
				 * Fires in donation details page, in the form after the order details.
				 *
				 * @since 1.0
				 *
				 * @param int $payment_id Payment id.
				 */
				do_action( 'give_view_donation_details_form_bottom', $this->donation->ID );

				wp_nonce_field( 'give_update_payment_details_nonce' );
				?>

				<input type="hidden" name="give_payment_id" value="<?php echo esc_attr( $this->donation->ID ); ?>"/>
				<input type="hidden" name="give_action" value="update_payment_details"/>
			</form>

			<?php
			/**
			 * Fires in donation details page, after the order form.
			 *
			 * @since 1.0
			 *
			 * @param int $payment_id Payment id.
			 */
			do_action( 'give_view_donation_details_after', $this->donation->ID );
			?>
		</div><!-- /.wrap -->
		<?php
	}
}

Give_Donation_Detail_Page::get_instance();