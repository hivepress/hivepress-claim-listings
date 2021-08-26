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
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => esc_html__( 'Claim Rejected', 'hivepress-claim-listings' ),
				'description' => esc_html__( 'This email is sent to users when a claim is rejected.', 'hivepress-claim-listings' ),
				'recipient'   => hivepress()->translator->get_string( 'user' ),
				'tokens'      => [ 'user_name', 'listing_title', 'user', 'listing', 'claim' ],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Claim Rejected', 'hivepress-claim-listings' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! Unfortunately, your claim for listing "%listing_title%" has been rejected.', 'hivepress-claim-listings' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
