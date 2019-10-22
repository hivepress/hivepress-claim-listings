<?php
/**
 * Listing claim submit complete page template.
 *
 * @template listing_claim_submit_complete_page
 * @description Listing claim submission page (completed).
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing claim submit complete page template class.
 *
 * @class Listing_Claim_Submit_Complete_Page
 */
class Listing_Claim_Submit_Complete_Page extends Listing_Submit_Page {

	/**
	 * Template name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Template blocks.
	 *
	 * @var array
	 */
	protected static $blocks = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Template arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_content' => [
						'blocks' => [
							'listing_claim_complete_message' => [
								'type'     => 'element',
								'filepath' => 'listing-claim/submit/listing-claim-complete-message',
								'order'    => 10,
							],
						],
					],
				],
			],
			$args,
			'blocks'
		);

		parent::init( $args );
	}
}
