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
	 * Form description.
	 *
	 * @var string
	 */
	protected static $description;

	/**
	 * Form title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Form message.
	 *
	 * @var string
	 */
	protected static $message;

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected static $model;

	/**
	 * Form action.
	 *
	 * @var string
	 */
	protected static $action;

	/**
	 * Form method.
	 *
	 * @var string
	 */
	protected static $method = 'POST';

	/**
	 * Form captcha.
	 *
	 * @var bool
	 */
	protected static $captcha = false;

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Form button.
	 *
	 * @var object
	 */
	protected static $button;

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'       => hivepress()->translator->get_string( 'claim_listing' ),
				'description' => hivepress()->translator->get_string( 'provide_details_to_verify_listing_ownership' ),
				'message'     => esc_html__( 'Your claim has been submitted.', 'hivepress-claim-listings' ),
				'model'       => 'listing_claim',
				'action'      => hp\get_rest_url( '/listing-claims' ),

				'fields'      => [
					'details'    => [
						'order' => 10,
					],

					'listing_id' => [
						'type' => 'hidden',
					],
				],

				'button'      => [
					'label' => esc_html__( 'Submit Claim', 'hivepress-claim-listings' ),
				],
			],
			$args
		);

		parent::init( $args );
	}
}
