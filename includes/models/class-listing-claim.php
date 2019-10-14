<?php
/**
 * Listing claim model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing claim model class.
 *
 * @class Listing_Claim
 */
class Listing_Claim extends Post {

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Model fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Model aliases.
	 *
	 * @var array
	 */
	protected static $aliases = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Model arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields'  => [
					'listing_id' => [
						'type'      => 'number',
						'min_value' => 0,
						'required'  => true,
					],
				],

				'aliases' => [
					'post_parent' => 'listing_id',
				],
			],
			$args
		);

		parent::init( $args );
	}
}
