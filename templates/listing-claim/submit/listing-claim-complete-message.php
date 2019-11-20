<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_current_user_id() === $listing->get_user_id() ) :
	?>
	<p><?php printf( esc_html__( 'Thank you! Your claim for "%s" has been approved and you can start managing it.', 'hivepress-claim-listings' ), $listing->get_title() ); ?></p>
	<button type="button" class="button" data-component="link" data-url="<?php echo esc_url( hivepress()->router->get_url( 'listing/edit_listing', [ 'listing_id' => $listing->get_id() ] ) ); ?>"><?php echo esc_html( hivepress()->translator->get_string( 'edit_listing' ) ); ?></button>
<?php else : ?>
	<p><?php printf( esc_html__( 'Thank you! Your claim for "%s" has been submitted and will be reviewed as soon as possible.', 'hivepress-claim-listings' ), $listing->get_title() ); ?></p>
	<button type="button" class="button" data-component="link" data-url="<?php echo esc_url( hivepress()->router->get_url( 'user/view_account' ) ); ?>"><?php echo esc_html( hivepress()->translator->get_string( 'return_to_my_account' ) ); ?></button>
	<?php
endif;
