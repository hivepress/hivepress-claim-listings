<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing_claim->get_status() === 'publish' ) :
	?>
	<p><?php printf( esc_html( hivepress()->translator->get_string( 'claim_for_listing_has_been_approved' ) ), $listing_claim->get_listing__title() ); ?></p>
	<button type="button" class="button" data-component="link" data-url="<?php echo esc_url( hivepress()->router->get_url( 'listing_edit_page', [ 'listing_id' => $listing_claim->get_listing__id() ] ) ); ?>"><?php echo esc_html( hivepress()->translator->get_string( 'edit_listing' ) ); ?></button>
<?php else : ?>
	<p><?php printf( esc_html( hivepress()->translator->get_string( 'claim_for_listing_has_been_submitted' ) ), $listing_claim->get_listing__title() ); ?></p>
	<button type="button" class="button" data-component="link" data-url="<?php echo esc_url( hivepress()->router->get_url( 'user_account_page' ) ); ?>"><?php echo esc_html( hivepress()->translator->get_string( 'return_to_my_account' ) ); ?></button>
	<?php
endif;
