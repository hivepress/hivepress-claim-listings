<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
// todo.
?>
<a href="#<?php if ( is_user_logged_in() ) : ?>listing_claim<?php else : ?>user_login<?php endif; ?>_modal" class="hp-listing__action hp-link"><i class="hp-icon fas fa-shield-alt"></i><span><?php esc_html_e( 'Claim Listing', 'hivepress-claim-listings' ); ?></span></a>
