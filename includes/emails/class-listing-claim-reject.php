<?php
/**
 * Listing claim reject email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing claim reject email class.
 *
 * @class Listing_Claim_Reject
 */
class Listing_Claim_Reject extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Claim Rejected', 'hivepress-claim-listings' ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
