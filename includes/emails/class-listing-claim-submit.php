<?php
/**
 * Listing claim submit email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing claim submit email class.
 *
 * @class Listing_Claim_Submit
 */
class Listing_Claim_Submit extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Claim Submitted', 'hivepress-claim-listings' ),
				'body'    => hp\sanitize_html( __( 'A new claim for listing "%listing_title%" %claim_url% has been submitted with the following details: %claim_details%', 'hivepress-claim-listings' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
