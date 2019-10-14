<?php
/**
 * Listing claim email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;
// todo.
use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing claim email class.
 *
 * @class Listing_Claim
 */
class Listing_Claim extends Email {

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
				'subject' => esc_html__( 'Listing Claimed', 'hivepress-claim-listings' ),
				'body'    => hp\sanitize_html( __( 'Listing "%listing_title%" %listing_url% has been claimed with the following details: %claim_details%', 'hivepress-claim-listings' ) ),
			],
			$args
		);

		parent::init( $args );
	}
}
