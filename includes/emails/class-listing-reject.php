<?php
/**
 * Listing reject email.
 *
 * @package HivePress\Emails
 */
// todo.
namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing reject email class.
 *
 * @class Listing_Reject
 */
class Listing_Reject extends Email {

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
				'subject' => esc_html__( 'Listing Rejected', 'hivepress' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! Unfortunately, your listing "%listing_title%" has been rejected.', 'hivepress' ) ),
			],
			$args
		);

		parent::init( $args );
	}
}
