<?php
/**
 * Listing claim submit complete page template.
 *
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
class Listing_Claim_Submit_Complete_Page extends Page {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_container' => [
						'blocks' => [
							'page_content' => [
								'type'   => 'container',
								'tag'    => 'main',
								'_order' => 10,

								'blocks' => [
									'page_title' => [
										'type'   => 'part',
										'path'   => 'page/page-title',
										'_order' => 5,
									],

									'listing_claim_complete_message' => [
										'type'   => 'part',
										'path'   => 'listing-claim/submit/listing-claim-complete-message',
										'_order' => 10,
									],
								],
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
