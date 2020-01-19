<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! $listing->is_verified() ) :
	?>
	<a href="#<?php if ( is_user_logged_in() ) : ?>listing_claim_submit_modal_<?php echo esc_attr( $listing->get_id() ); else :	?>user_login_modal<?php endif; ?>" class="hp-listing__action hp-listing__action--claim hp-link"><i class="hp-icon fas fa-check-circle"></i><span><?php echo esc_html( hivepress()->translator->get_string( 'claim_listing' ) ); ?></span></a>
	<?php
endif;
