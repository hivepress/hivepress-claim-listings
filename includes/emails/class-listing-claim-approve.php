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
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => esc_html__( 'Claim Approved', 'hivepress-claim-listings' ),
				'description' => esc_html__( 'This email is sent to users when a claim is approved.', 'hivepress-claim-listings' ),
				'recipient'   => hivepress()->translator->get_string( 'user' ),
				'tokens'      => [ 'user_name', 'listing_title', 'listing_url', 'user', 'listing', 'claim' ],
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
				'subject' => esc_html__( 'Claim Approved', 'hivepress-claim-listings' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! Your claim for listing "%listing_title%" has been approved, click on the following link to edit it: %listing_url%', 'hivepress-claim-listings' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
