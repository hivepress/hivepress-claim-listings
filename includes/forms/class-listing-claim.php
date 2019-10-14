<?php
/**
 * Listing claim form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing claim form class.
 *
 * @class Listing_Claim
 */
class Listing_Claim extends Model_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	protected static $name;

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
				'title'   => esc_html__( 'Claim Listing', 'hivepress-claim-listings' ),
				'message' => esc_html__( 'Your todo.', 'hivepress-claim-listings' ),
				'model'   => 'listing_claim',
				'action'  => hp\get_rest_url( '/todo' ),

				'fields'  => [
					'listing_id' => [
						'type' => 'number',
					],
				],

				'button'  => [
					'label' => esc_html__( 'Claim Listing', 'hivepress-claim-listings' ),
				],
			],
			$args
		);

		parent::init( $args );
	}
}
