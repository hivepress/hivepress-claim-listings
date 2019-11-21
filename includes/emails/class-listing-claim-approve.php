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
	 * Email name.
	 *
	 * @var string
	 */
	protected static $name;

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
				'subject' => esc_html__( 'Claim Approved', 'hivepress-claim-listings' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! Your claim for listing "%listing_title%" has been approved, click on the following link to edit it: %listing_url%', 'hivepress-claim-listings' ) ),
			],
			$args
		);

		parent::init( $args );
	}
}
