<?php
/**
 * Strings configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'claim_listing'                               => esc_html__( 'Claim Listing', 'hivepress-claim-listings' ),
	'provide_details_to_verify_listing_ownership' => esc_html__( 'Please provide details that will help us verify that you\'re the owner of this listing.', 'hivepress-claim-listings' ),
	'you_cant_claim_your_own_listings'            => esc_html__( 'You can\'t claim your own listings.', 'hivepress-claim-listings' ),
	/* translators: %s: listing title. */
	'claim_for_listing_has_been_submitted'        => esc_html__( 'Thank you! Your claim for listing "%s" has been submitted and will be reviewed as soon as possible.', 'hivepress-claim-listings' ),
	/* translators: %s: listing title. */
	'claim_for_listing_has_been_approved'         => esc_html__( 'Thank you! Your claim for listing "%s" has been approved and you can start managing it.', 'hivepress-claim-listings' ),
];
