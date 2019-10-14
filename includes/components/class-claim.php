<?php
/**
 * Claim component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Claim component class.
 *
 * @class Claim
 */
final class Claim {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		if ( ! is_admin() ) {

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ] );
		}
	}

	/**
	 * Alters listing view page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listing_view_page( $template ) {
		return hp\merge_trees(
			$template,
			[
				'blocks' => [
					'listing_actions_primary' => [
						'blocks' => [
							'listing_claim_modal' => [
								'type'    => 'modal',
								'caption' => esc_html__( 'Claim Listing', 'hivepress-claim-listings' ),

								'blocks'  => [
									'listing_claim_form' => [
										'type'       => 'form',
										'form'       => 'listing_claim',
										'order'      => 10,

										'attributes' => [
											'class' => [ 'hp-form--narrow' ],
										],
									],
								],
							],

							'listing_claim_link'  => [
								'type'     => 'element',
								'filepath' => 'listing/view/page/listing-claim-link',
								'order'    => 30,
							],
						],
					],
				],
			],
			'blocks'
		);
	}
}
