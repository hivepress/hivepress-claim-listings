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

		if ( is_admin() ) {

			// Manage admin columns.
			add_filter( 'manage_hp_listing_claim_posts_columns', [ $this, 'add_admin_columns' ] );
			add_action( 'manage_hp_listing_claim_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );

			// Add meta fields.
			add_filter( 'hivepress/v1/meta_boxes/listing_claim_details', [ $this, 'add_meta_fields' ] );

			// Delete meta values.
			add_action( 'save_post_hp_listing_claim', [ $this, 'delete_meta_values' ], 99 );

			// Filter editor settings.
			add_filter( 'wp_editor_settings', [ $this, 'filter_editor_settings' ] );
		} else {

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
			$listing_id = $this->get_listing_id( $claim->ID );

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
					'post_type'   => 'hp_listing_claim',
					'post_status' => [ 'draft', 'publish' ],
					'author'      => $order->get_user_id(),
				]
			);

			if ( 0 !== $claim_id ) {
				if ( $this->get_listing_id( $claim_id ) !== 0 ) {
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
	 * Adds admin columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function add_admin_columns( $columns ) {
		return array_merge(
			array_slice( $columns, 0, 3, true ),
			[
				'listing' => esc_html__( 'Listing', 'hivepress-claim-listings' ),
			],
			array_slice( $columns, 3, null, true )
		);
	}

	/**
	 * Renders admin columns.
	 *
	 * @param string $column Column name.
	 * @param int    $claim_id Claim ID.
	 */
	public function render_admin_columns( $column, $claim_id ) {
		if ( 'listing' === $column ) {
			$output = '&mdash;';

			// Get listing ID.
			$listing_id = $this->get_listing_id( $claim_id );

			if ( 0 !== $listing_id ) {

				// Render column value.
				$output = '<a href="' . esc_url( get_edit_post_link( $listing_id ) ) . '">' . esc_html( get_the_title( $listing_id ) ) . '</a>';
			}

			echo $output;
		}
	}

	/**
	 * Adds meta fields.
	 *
	 * @param array $meta_box Meta box arguments.
	 * @return array
	 */
	public function add_meta_fields( $meta_box ) {
		return array_merge(
			$meta_box,
			[
				'fields' => [
					'listing' => [
						'label'      => esc_html__( 'Listing', 'hivepress-claim-listings' ),
						'type'       => 'select',
						'options'    => 'posts',
						'post_type'  => 'hp_listing',
						'value'      => $this->get_listing_id( get_the_ID() ),
						'order'      => 10,

						'attributes' => [
							'disabled' => true,
						],
					],
				],
			]
		);
	}

	/**
	 * Deletes meta values.
	 *
	 * @param int $claim_id Claim ID.
	 */
	public function delete_meta_values( $claim_id ) {
		delete_post_meta( $claim_id, 'hp_listing' );
	}

	/**
	 * Filters editor settings.
	 *
	 * @param array $settings Editor settings.
	 * @return array
	 */
	public function filter_editor_settings( $settings ) {
		$current_screen = get_current_screen();

		if ( ! is_null( $current_screen ) && 'hp_listing_claim' === $current_screen->post_type ) {
			$settings = array_merge(
				$settings,
				[
					'media_buttons' => false,
					'tinymce'       => false,
					'quicktags'     => false,
				]
			);
		}

		return $settings;
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
										'type'       => 'listing_claim_submit_form',
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

	/**
	 * Gets listing ID.
	 *
	 * @param int $claim_id Claim ID.
	 * @return int
	 */
	protected function get_listing_id( $claim_id ) {
		return hp\get_post_id(
			[
				'post_type'   => 'hp_listing',
				'post_status' => 'publish',
				'post__in'    => [ absint( wp_get_post_parent_id( $claim_id ) ) ],
			]
		);
	}
}
