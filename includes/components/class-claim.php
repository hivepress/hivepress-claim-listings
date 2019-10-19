<?php
/**
 * Claim component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Emails;

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

		// Update claim status.
		add_action( 'transition_post_status', [ $this, 'update_claim_status' ], 10, 3 );

		// Update order status.
		if ( class_exists( 'WooCommerce' ) ) {
			add_action( 'woocommerce_order_status_changed', [ $this, 'update_order_status' ], 10, 4 );
		}

		if ( ! is_admin() ) {

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_block', [ $this, 'alter_listing_view_block' ] );
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_block' ] );
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ] );
		}
	}

	/**
	 * Updates claim status.
	 *
	 * @param string  $new_status New status.
	 * @param string  $old_status Old status.
	 * @param WP_Post $claim Claim object.
	 */
	public function update_claim_status( $new_status, $old_status, $claim ) {
		if ( 'hp_listing_claim' === $claim->post_type ) {

			// Get listing ID.
			$listing_id = hp\get_post_id(
				[
					'post_type'   => 'hp_listing',
					'post_status' => 'publish',
					'post__in'    => [ absint( $claim->post_parent ) ],
				]
			);

			if ( 0 !== $listing_id ) {
				if ( 'pending' === $new_status ) {

					// Send email.
					( new Emails\Listing_Claim_Submit(
						[
							'recipient' => get_option( 'admin_email' ),
							'tokens'    => [
								'listing_title' => get_the_title( $listing_id ),
								'claim_url'     => get_edit_post_link( $claim->ID ),
								'claim_details' => $claim->post_content,
							],
						]
					) )->send();
				} elseif ( in_array( $new_status, [ 'publish', 'trash' ], true ) ) {

					// Get user.
					$user = get_userdata( $claim->post_author );

					if ( false !== $user ) {
						if ( 'publish' === $new_status ) {

							// Approve claim.
							wp_update_post(
								[
									'ID'          => $listing_id,
									'post_author' => $user->ID,
								]
							);

							update_post_meta( $listing_id, 'hp_verified', '1' );

							// Send email.
							( new Emails\Listing_Claim_Approve(
								[
									'recipient' => $user->user_email,
									'tokens'    => [
										'user_name'     => $user->display_name,
										'listing_title' => get_the_title( $listing_id ),
										'listing_url'   => get_permalink( $listing_id ),
									],
								]
							) )->send();
						} else {

							// Reject claim.
							wp_update_post(
								[
									'ID'          => $listing_id,
									'post_author' => 0,
								]
							);

							delete_post_meta( $listing_id, 'hp_verified' );

							// Send email.
							( new Emails\Listing_Claim_Reject(
								[
									'recipient' => $user->user_email,
									'tokens'    => [
										'user_name'     => $user->display_name,
										'listing_title' => get_the_title( $listing_id ),
									],
								]
							) )->send();
						}
					}
				}
			}
		}
	}

	/**
	 * Updates order status.
	 *
	 * @param int      $order_id Order ID.
	 * @param string   $old_status Old status.
	 * @param string   $new_status New status.
	 * @param WC_Order $order Order object.
	 */
	public function update_order_status( $order_id, $old_status, $new_status, $order ) {

		// Get product ID.
		$product_id = absint( get_option( 'hp_product_claim' ) );

		if ( 0 !== $product_id && in_array(
			$product_id,
			array_map(
				function( $item ) {
					return $item->get_product_id();
				},
				$order->get_items()
			),
			true
		) ) {

			// Get claim ID.
			$claim_id = hp\get_post_id(
				[
					'post_type' => 'hp_listing_claim',
					'author'    => $order->get_user_id(),
				]
			);

			if ( 0 !== $claim_id ) {

				// Get listing ID.
				$listing_id = hp\get_post_id(
					[
						'post_type'   => 'hp_listing',
						'post_status' => 'publish',
						'post__in'    => [ absint( wp_get_post_parent_id( $claim_id ) ) ],
					]
				);

				if ( 0 !== $listing_id ) {
					if ( in_array( $new_status, [ 'processing', 'completed' ], true ) ) {

						// Submit claim.
						wp_update_post(
							[
								'ID'          => $claim_id,
								'post_status' => get_option( 'hp_claim_enable_moderation' ) ? 'pending' : 'publish',
							]
						);
					} elseif ( in_array( $new_status, [ 'failed', 'cancelled', 'refunded' ], true ) ) {

						// Reject claim.
						wp_update_post(
							[
								'ID'          => $claim_id,
								'post_status' => 'trash',
							]
						);
					}
				}
			}
		}
	}

	/**
	 * Alters listing view block.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listing_view_block( $template ) {
		if ( get_post_meta( get_the_ID(), 'hp_verified', true ) ) {

			// Add verified badge.
			$template = hp\merge_trees(
				$template,
				[
					'blocks' => [
						'listing_details_primary' => [
							'blocks' => [
								'listing_verified_badge' => [
									'type'     => 'element',
									'filepath' => 'listing/view/listing-verified-badge',
									'order'    => 10,
								],
							],
						],
					],
				],
				'blocks'
			);
		}

		return $template;
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
