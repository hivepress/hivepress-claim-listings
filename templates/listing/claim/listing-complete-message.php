<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_current_user_id() === $listing->get_user_id() ) :
	?>
	<p><?php printf( esc_html__( 'Thank you! Your claim for listing "%s" has been approved and todo.', 'hivepress' ), $listing->get_title() ); ?></p>
	<button type="button" class="button" data-component="link" data-url="todo"><?php esc_html_e( 'Edit Listing', 'hivepress' ); ?></button>
<?php else : ?>
	<p><?php printf( esc_html__( 'Thank you! Your claim for listing "%s" has been submitted and will be reviewed as soon as possible.', 'hivepress' ), $listing->get_title() ); ?></p>
	<button type="button" class="button" data-component="link" data-url="<?php echo esc_url( hivepress()->router->get_url( 'user/view_account' ) ); ?>"><?php esc_html_e( 'Return to My Account', 'hivepress' ); ?></button>
	<?php
endif;
