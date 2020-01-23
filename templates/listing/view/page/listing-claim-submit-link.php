<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! $listing->is_verified() ) :
	?>
	<a href="#<?php if ( is_user_logged_in() ) : ?>listing_claim_submit<?php else :	?>user_login<?php endif; ?>_modal" class="hp-listing__action hp-listing__action--claim hp-link"><i class="hp-icon fas fa-check-circle"></i><span><?php echo esc_html( hivepress()->translator->get_string( 'claim_listing' ) ); ?></span></a>
	<?php
endif;
