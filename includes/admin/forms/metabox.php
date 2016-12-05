<?php
/**
 * Metabox Functions
 *
 * @package     Give
 * @subpackage  Admin/Forms
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add Shortcode Copy Field to Publish Metabox
 *
 * @since: 1.0
 */
function give_add_shortcode_to_publish_metabox() {

	if ( 'give_forms' !== get_post_type() ) {
		return false;
	}
	global $post;

	//Shortcode column with select all input
	$shortcode = htmlentities( '[give_form id="' . $post->ID . '"]' );
	echo '<div class="shortcode-wrap box-sizing"><label for="shortcode-input">' . esc_html__( 'Give Form Shortcode:', 'give' ) . '</label><input onClick="this.setSelectionRange(0, this.value.length)" type="text" name="shortcode-input" id="shortcode-input" class="shortcode-input" readonly value="' . $shortcode . '"></div>';
}

add_action( 'post_submitbox_misc_actions', 'give_add_shortcode_to_publish_metabox' );


/**
 * Add display location setting to feature image metabox.
 *
 * @since 1.8
 *
 * @param $content
 * @param $content
 * @param $thumbnail_id
 *
 * @return string
 */
function give_add_featured_image_display_settings( $content, $post_id, $thumbnail_id ) {
	// Make sure we affect only 'give_forms' post type.
	if ( 'give_forms' != get_post_type( $post_id ) || empty( $thumbnail_id ) ) {
		return $content;
	}

	// Add 'give_featured_img_pos' field.
	$field_id = 'give_featured_img_pos';

	$field_value = esc_attr( get_post_meta( $post_id, $field_id, true ) );
	$field_value = $field_value ? $field_value : 'none';

	$field_text = esc_html__( 'Image Position:', 'give' );

	ob_start();
	?>
	<div class="give-feature-image-metabox">
		<p>
			<strong class="label"><?php echo $field_text; ?></strong><br>
			<span class="give-field-description"><?php _e( 'Please select the position you would like to display for your single donation form\'s featured image.', 'give' ); ?></span>
		</p>

		<input type="radio" name="<?php echo $field_id; ?>" id="<?php echo "{$field_id}_none"?>" value="none"<?php echo checked( $field_value, 'none', false ); ?>>
		<label for="<?php echo "{$field_id}_none"?>"><?php _e( 'None', 'give' ); ?></label><br>

		<input type="radio" name="<?php echo $field_id; ?>" id="<?php echo "{$field_id}_left"?>" value="left"<?php echo checked( $field_value, 'left', false ); ?>>
		<label for="<?php echo "{$field_id}_left"?>"><?php _e( 'Left', 'give' ); ?></label><br>

		<input type="radio" name="<?php echo $field_id; ?>" id="<?php echo "{$field_id}_right"?>" value="right"<?php echo checked( $field_value, 'right', false ); ?>>
		<label for="<?php echo "{$field_id}_right"?>"><?php _e( 'Right', 'give' ); ?></label><br>

		<input type="radio" name="<?php echo $field_id; ?>" id="<?php echo "{$field_id}_above"?>" value="above"<?php echo checked( $field_value, 'above', false ); ?>>
		<label for="<?php echo "{$field_id}_above"?>"><?php _e( 'Above The Form', 'give' ); ?></label>
	</div>

	<?php
	$content .= ob_get_clean();

	return $content;

}

add_filter( 'admin_post_thumbnail_html', 'give_add_featured_image_display_settings', 10, 2 );
