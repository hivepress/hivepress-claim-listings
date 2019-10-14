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

		// Update status.
		add_action( 'transition_post_status', [ $this, 'update_status' ], 10, 3 );

		if ( ! is_admin() ) {

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ] );
		}
	}

	/**
	 * Updates status.
	 *
	 * @param string  $new_status New status.
	 * @param string  $old_status Old status.
	 * @param WP_Post $listing_claim Listing claim object.
	 */
	public function update_status( $new_status, $old_status, $listing_claim ) {
		if ( 'hp_listing_claim' === $listing_claim->post_type && 'pending' === $old_status ) {

			// Get user.
			$user = get_userdata( $listing_claim->post_author );

			if ( 'publish' === $new_status ) {

				// Send approval email.
				( new Emails\Listing_Todo(
					[
						'recipient' => $user->user_email,
						'tokens'    => [
							'user_name'     => $user->display_name,
							'listing_title' => $listing_claim->post_title,
							'listing_url'   => get_permalink( $listing_claim->ID ),
						],
					]
				) )->send();
			} elseif ( 'trash' === $new_status ) {

				// Send rejection email.
				( new Emails\Listing_Todo2(
					[
						'recipient' => $user->user_email,
						'tokens'    => [
							'user_name'     => $user->display_name,
							'listing_title' => $listing_claim->post_title,
							'listing_url'   => get_permalink( $listing_claim->ID ),
						],
					]
				) )->send();
			}
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
