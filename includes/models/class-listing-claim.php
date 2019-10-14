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
					'title'      => [
						'type'       => 'text',
						'max_length' => 128,
						'required'   => true,
					],

					'details'    => [
						'label'      => esc_html__( 'Details', 'hivepress-claim-listings' ),
						'type'       => 'textarea',
						'max_length' => 10240,
						'required'   => true,
					],

					'status'     => [
						'type'       => 'text',
						'max_length' => 128,
					],

					'user_id'    => [
						'type'      => 'number',
						'min_value' => 0,
						'required'  => true,
					],

					'listing_id' => [
						'type'      => 'number',
						'min_value' => 0,
						'required'  => true,
					],
				],

				'aliases' => [
					'post_title'   => 'title',
					'post_content' => 'details',
					'post_status'  => 'status',
					'post_author'  => 'user_id',
					'post_parent'  => 'listing_id',
				],
			],
			$args
		);

		parent::init( $args );
	}
}
