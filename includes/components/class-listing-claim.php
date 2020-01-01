<?php
/**
 * Listing claim component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Emails;
use HivePress\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing claim component class.
 *
 * @class Listing_Claim
 */
final class Listing_Claim {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Update claim.
		add_action( 'hivepress/v1/models/listing_claim/create', [ $this, 'update_claim' ] );

		// Update claim status.
		add_action( 'hivepress/v1/models/listing_claim/update_status', [ $this, 'update_claim_status' ], 10, 3 );

		if ( class_exists( 'WooCommerce' ) ) {

			// Update order status.
			add_action( 'woocommerce_order_status_changed', [ $this, 'update_order_status' ], 10, 4 );

			// Redirect order page.
			add_action( 'template_redirect', [ $this, 'redirect_order_page' ] );
		}

		// Filter form arguments.
		add_filter( 'hivepress/v1/forms/listing_claim_submit', [ $this, 'filter_form_args' ] );

		if ( is_admin() ) {

			// Manage admin columns.
			add_filter( 'manage_hp_listing_claim_posts_columns', [ $this, 'add_admin_columns' ] );
			add_action( 'manage_hp_listing_claim_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );
		} else {

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ] );

			// Set page title.
			add_filter( 'hivepress/v1/controllers/listing_claim/routes/submit_complete', [ $this, 'set_page_title' ] );
		}
	}

	/**
	 * Updates claim.
	 *
	 * @param int $claim_id Claim ID.
	 */
	public function update_claim( $claim_id ) {

		// Remove action.
		remove_action( 'hivepress/v1/models/listing_claim/create', [ $this, 'update_claim' ] );

		// Get claim.
		$claim = get_post( $claim_id );

		// Set claim title.
		$title = '#' . $claim_id;

		if ( $claim->post_title !== $title ) {
			wp_update_post(
				[
					'ID'         => $claim_id,
					'post_title' => $title,
				]
			);
		}
	}

	/**
	 * Updates claim status.
	 *
	 * @param int    $claim_id Claim ID.
	 * @param string $new_status New status.
	 * @param string $old_status Old status.
	 */
	public function update_claim_status( $claim_id, $new_status, $old_status ) {

		// Get claim.
		$claim = get_post( $claim_id );

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
							'claim_details' => $claim->post_content,
							'claim_url'     => admin_url(
								'post.php?' . http_build_query(
									[
										'action' => 'edit',
										'post'   => $claim->ID,
									]
								)
							),
						],
					]
				) )->send();
			} elseif ( in_array( $new_status, [ 'publish', 'trash' ], true ) ) {

				// Get user.
				$user = get_userdata( $claim->post_author );

				if ( false !== $user ) {
					if ( 'publish' === $new_status ) {

						// Approve claim.
						update_post_meta( $listing_id, 'hp_verified', '1' );
						update_post_meta( $claim->ID, 'hp_user', get_post_field( 'post_author', $listing_id ) );

						wp_update_post(
							[
								'ID'          => $listing_id,
								'post_author' => $user->ID,
							]
						);

						// Send email.
						( new Emails\Listing_Claim_Approve(
							[
								'recipient' => $user->user_email,
								'tokens'    => [
									'user_name'     => $user->display_name,
									'listing_title' => get_the_title( $listing_id ),
									'listing_url'   => Controllers\Listing::get_url( 'edit_listing', [ 'listing_id' => $listing_id ] ),
								],
							]
						) )->send();
					} else {

						// Reject claim.
						delete_post_meta( $listing_id, 'hp_verified' );

						if ( absint( get_post_field( 'post_author', $listing_id ) ) === $user->ID ) {
							wp_update_post(
								[
									'ID'          => $listing_id,
									'post_author' => absint( get_post_meta( $claim->ID, 'hp_user', true ) ),
								]
							);
						}

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
		$product_id = absint( get_option( 'hp_product_listing_claim' ) );

		if ( 0 !== $product_id && in_array( $product_id, $this->get_product_ids( $order ), true ) ) {

			// Get claim ID.
			$claim_id = $this->get_claim_id( $order->get_user_id(), [ 'draft', 'publish' ] );

			if ( 0 !== $claim_id ) {
				if ( in_array( $new_status, [ 'processing', 'completed' ], true ) ) {

					// Submit claim.
					wp_update_post(
						[
							'ID'          => $claim_id,
							'post_status' => get_option( 'hp_listing_claim_enable_moderation' ) ? 'pending' : 'publish',
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

	/**
	 * Redirects order page.
	 */
	public function redirect_order_page() {
		if ( is_wc_endpoint_url( 'order-received' ) ) {

			// Get product ID.
			$product_id = absint( get_option( 'hp_product_listing_claim' ) );

			if ( 0 !== $product_id ) {

				// Get order.
				$order = wc_get_order( get_query_var( 'order-received' ) );

				if ( ! empty( $order ) && in_array( $product_id, $this->get_product_ids( $order ), true ) && in_array( $order->get_status(), [ 'processing', 'completed' ], true ) ) {

					// Get claim ID.
					$claim_id = $this->get_claim_id( $order->get_user_id(), [ 'pending', 'publish' ] );

					// Redirect page.
					if ( 0 !== $claim_id ) {
						wp_safe_redirect( Controllers\Listing_Claim::get_url( 'submit_complete', [ 'listing_id' => wp_get_post_parent_id( $claim_id ) ] ) );

						exit();
					}
				}
			}
		}
	}

	/**
	 * Filters form arguments.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function filter_form_args( $form ) {

		// Get product.
		$product = false;

		if ( class_exists( 'WooCommerce' ) && get_option( 'hp_product_listing_claim' ) ) {
			$product = wc_get_product( get_option( 'hp_product_listing_claim' ) );
		}

		// Unset message.
		if ( ! get_option( 'hp_listing_claim_enable_moderation' ) || ! empty( $product ) ) {
			$form['message'] = null;
		}

		// Set button caption.
		if ( ! empty( $product ) ) {
			$form['button']['label'] = sprintf( esc_html__( 'Claim for %s', 'hivepress-claim-listings' ), wp_strip_all_tags( wc_price( $product->get_price() ) ) );
		} elseif ( ! get_option( 'hp_listing_claim_enable_moderation' ) ) {
			$form['button']['label'] = hivepress()->translator->get_string( 'claim_listing' );
		}

		return $form;
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
				'listing' => hivepress()->translator->get_string( 'listing' ),
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
				$output = '<a href="' . esc_url(
					admin_url(
						'post.php?' . http_build_query(
							[
								'action' => 'edit',
								'post'   => $listing_id,
							]
						)
					)
				) . '">' . esc_html( get_the_title( $listing_id ) ) . '</a>';
			}

			echo $output;
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
							'listing_claim_submit_modal' => [
								'type'   => 'modal',
								'title'  => hivepress()->translator->get_string( 'claim_listing' ),

								'blocks' => [
									'listing_claim_submit_form' => [
										'type'       => 'listing_claim_submit_form',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-form--narrow' ],
										],
									],
								],
							],

							'listing_claim_submit_link'  => [
								'type'   => 'part',
								'path'   => 'listing/view/page/listing-claim-submit-link',
								'_order' => 30,
							],
						],
					],
				],
			],
			'blocks'
		);
	}

	/**
	 * Sets page title.
	 *
	 * @param array $route Route arguments.
	 * @return array
	 */
	public function set_page_title( $route ) {

		// Get listing ID.
		$listing_id = hp\get_post_id(
			[
				'post_type'   => 'hp_listing',
				'post_status' => 'publish',
				'author'      => get_current_user_id(),
				'post__in'    => [ absint( get_query_var( 'hp_listing_id' ) ) ],
			]
		);

		// Set page title.
		if ( 0 !== $listing_id ) {
			$route['title'] = esc_html__( 'Claim Approved', 'hivepress-claim-listings' );
		}

		return $route;
	}

	/**
	 * Gets claim ID.
	 *
	 * @param int   $user_id User ID.
	 * @param mixed $status Claim status.
	 * @return int
	 */
	protected function get_claim_id( $user_id, $status ) {
		return hp\get_post_id(
			[
				'post_type'   => 'hp_listing_claim',
				'post_status' => $status,
				'author'      => $user_id,
			]
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

	/**
	 * Gets product IDs.
	 *
	 * @param WC_Order $order Order object.
	 * @return array
	 */
	protected function get_product_ids( $order ) {
		return array_map(
			function( $item ) {
				return $item->get_product_id();
			},
			$order->get_items()
		);
	}
}
