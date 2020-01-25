<?php
/**
 * Listing claim approve email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing claim approve email class.
 *
 * @class Listing_Claim_Approve
 */
class Listing_Claim_Approve extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Claim Approved', 'hivepress-claim-listings' ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
