<?php
/**
 * Render Read More block
 */

$read_more_id = $attributes['readMoreId'] ?? 0;
$read_more    = $read_more_id ? get_post( $read_more_id ) : null;

if ( ! $read_more ) {
	return; // Output nothing if the post doesn't exist
}

?>
<p <?php echo wp_kses_data( get_block_wrapper_attributes( [ 'class' => 'dmg-read-more' ] ) ); ?>>
	<?php esc_html_e( 'Read More: ', 'dmg-read-more' ); ?>
	<a href="<?php echo esc_url( get_permalink( $read_more ) ); ?>">
		<?php echo esc_html( $read_more->post_title ); ?>
	</a>
</p>
