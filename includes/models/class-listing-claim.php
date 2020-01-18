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
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [
					'details' => [
						'label'      => hivepress()->translator->get_string( 'details' ),
						'type'       => 'textarea',
						'max_length' => 10240,
						'required'   => true,
						'_alias'     => 'post_content',
					],

					'status'  => [
						'type'       => 'text',
						'max_length' => 128,
						'_alias'     => 'post_status',
					],

					'user'    => [
						'type'      => 'number',
						'min_value' => 1,
						'required'  => true,
						'_alias'    => 'post_author',
						'_model'    => 'user',
					],

					'listing' => [
						'type'      => 'number',
						'min_value' => 1,
						'required'  => true,
						'_alias'    => 'post_parent',
						'_model'    => 'listing',
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
