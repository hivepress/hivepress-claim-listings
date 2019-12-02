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
	 * Email subject.
	 *
	 * @var string
	 */
	protected static $subject;

	/**
	 * Email body.
	 *
	 * @var string
	 */
	protected static $body;

	/**
	 * Class initializer.
	 *
	 * @param array $args Email arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Claim Rejected', 'hivepress-claim-listings' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! Unfortunately, your claim for listing "%listing_title%" has been rejected.', 'hivepress-claim-listings' ) ),
			],
			$args
		);

		parent::init( $args );
	}
}
