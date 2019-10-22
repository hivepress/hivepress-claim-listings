<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! get_post_meta( get_the_ID(), 'hp_verified', true ) && ( ! is_user_logged_in() || get_current_user_id() !== get_the_author_meta( 'ID' ) ) ) :
	?>
	<a href="#<?php if ( is_user_logged_in() ) : ?>listing_claim_submit<?php else : ?>user_login<?php endif; ?>_modal" class="hp-listing__action hp-link"><i class="hp-icon fas fa-check-circle"></i><span><?php esc_html_e( 'Claim Listing', 'hivepress-claim-listings' ); ?></span></a>
	<?php
endif;
