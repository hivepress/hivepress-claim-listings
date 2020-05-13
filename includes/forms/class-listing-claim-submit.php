<?php
/**
 * Listing claim submit form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing claim submit form class.
 *
 * @class Listing_Claim_Submit
 */
class Listing_Claim_Submit extends Model_Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'   => hivepress()->translator->get_string( 'claim_listing' ),
				'captcha' => false,
				'model'   => 'listing_claim',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'description' => hivepress()->translator->get_string( 'provide_details_to_verify_listing_ownership' ),
				'message'     => esc_html__( 'Your claim has been submitted.', 'hivepress-claim-listings' ),
				'action'      => hivepress()->router->get_url( 'listing_claim_submit_action' ),

				'fields'      => [
					'details' => [
						'required' => true,
						'_order'   => 10,
					],

					'listing' => [
						'display_type' => 'hidden',
					],
				],

				'button'      => [
					'label' => esc_html__( 'Submit Claim', 'hivepress-claim-listings' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
