<?php
/**
 * Listing claim component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing claim component class.
 *
 * @class Listing_Claim
 */
final class Listing_Claim extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Validate claim.
		add_filter( 'hivepress/v1/models/listing_claim/errors', [ $this, 'validate_claim' ], 10, 2 );

		// Create claim.
		add_action( 'hivepress/v1/models/listing_claim/create', [ $this, 'create_claim' ] );

		// Update claim status.
		add_action( 'hivepress/v1/models/listing_claim/update_status', [ $this, 'update_claim_status' ], 10, 3 );

		if ( hp\is_plugin_active( 'woocommerce' ) ) {

			// Update order status.
			add_action( 'woocommerce_order_status_changed', [ $this, 'update_order_status' ], 10, 4 );

			// Redirect order page.
			add_action( 'template_redirect', [ $this, 'redirect_order_page' ] );
		}

		if ( is_admin() ) {

			// Manage admin columns.
			add_filter( 'manage_hp_listing_claim_posts_columns', [ $this, 'add_admin_columns' ] );
			add_action( 'manage_hp_listing_claim_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );
		} else {

			// Alter submission form.
			add_filter( 'hivepress/v1/forms/listing_claim_submit', [ $this, 'alter_submission_form' ] );

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Validates claim.
	 *
	 * @param array  $errors Error messages.
	 * @param object $claim Claim object.
	 * @return array
	 */
	public function validate_claim( $errors, $claim ) {
		if ( ! $claim->get_id() && empty( $errors ) ) {

			// Get claim ID.
			$claim_id = Models\Listing_Claim::query()->filter(
				[
					'user'    => $claim->get_user__id(),
					'listing' => $claim->get_listing__id(),
				]
			)->get_first_id();

			// Add error.
			if ( $claim_id ) {
				$errors[] = esc_html__( 'You\'ve already submitted a claim.', 'hivepress-claim-listings' );
			}
		}

		return $errors;
	}

	/**
	 * Creates claim.
	 *
	 * @param int $claim_id Claim ID.
	 */
	public function create_claim( $claim_id ) {

		// Get claim.
		$claim = Models\Listing_Claim::query()->get_by_id( $claim_id );

		// Set title.
		$claim->set_title( '#' . $claim->get_id() )->save();
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
		$claim = Models\Listing_Claim::query()->get_by_id( $claim_id );

		// Get listing.
		$listing = $claim->get_listing();

		if ( empty( $listing ) ) {
			return;
		}

		if ( 'pending' === $new_status ) {

			// Send email.
			( new Emails\Listing_Claim_Submit(
				[
					'recipient' => get_option( 'admin_email' ),

					'tokens'    => [
						'listing_title' => $listing->get_title(),
						'claim_details' => $claim->get_details(),
						'claim_url'     => admin_url(
							'post.php?' . http_build_query(
								[
									'action' => 'edit',
									'post'   => $claim->get_id(),
								]
							)
						),
					],
				]
			) )->send();
		} elseif ( in_array( $new_status, [ 'publish', 'trash' ], true ) ) {

			// Get user.
			$user = $claim->get_user();

			if ( empty( $user ) ) {
				return;
			}

			if ( 'publish' === $new_status ) {

				// Update listing.
				$listing->fill(
					[
						'verified' => true,
						'user'     => $user->get_id(),
					]
				)->save();

				// Send email.
				( new Emails\Listing_Claim_Approve(
					[
						'recipient' => $user->get_email(),

						'tokens'    => [
							'user_name'     => $user->get_display_name(),
							'listing_title' => $listing->get_title(),
							'listing_url'   => hivepress()->router->get_url( 'listing_edit_page', [ 'listing_id' => $listing->get_id() ] ),
						],
					]
				) )->send();
			} else {

				// Remove verified status.
				$listing->set_verified( null );

				if ( $listing->get_user__id() === $user->get_id() ) {

					// Get user ID.
					$user_id = Models\User::query()->filter(
						[
							'role' => 'administrator',
						]
					)->get_first_id();

					// Set user.
					$listing->set_user( $user_id );
				}

				// Update listing.
				$listing->save();

				// Send email.
				( new Emails\Listing_Claim_Reject(
					[
						'recipient' => $user->get_email(),

						'tokens'    => [
							'user_name'     => $user->get_display_name(),
							'listing_title' => $listing->get_title(),
						],
					]
				) )->send();
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

		// Check user.
		if ( ! $order->get_user_id() ) {
			return;
		}

		// Check product.
		$product_id = absint( get_option( 'hp_product_listing_claim' ) );

		if ( empty( $product_id ) || ! in_array( $product_id, hivepress()->woocommerce->get_order_product_ids( $order ), true ) ) {
			return;
		}

		// Get claim.
		$claim = Models\Listing_Claim::query()->filter(
			[
				'user'       => $order->get_user_id(),
				'status__in' => [ 'draft', 'pending' ],
			]
		)->order( [ 'created_date' => 'desc' ] )
		->get_first();

		if ( empty( $claim ) ) {
			return;
		}

		// Update status.
		if ( in_array( $new_status, [ 'processing', 'completed' ], true ) ) {
			$claim->fill(
				[
					'status' => get_option( 'hp_listing_claim_enable_moderation' ) ? 'pending' : 'publish',
				]
			)->save();
		} elseif ( in_array( $new_status, [ 'failed', 'cancelled', 'refunded' ], true ) ) {
			$claim->set_status( 'trash' )->save();
		}
	}

	/**
	 * Redirects order page.
	 */
	public function redirect_order_page() {

		// Check authentication.
		if ( ! is_user_logged_in() || ! is_wc_endpoint_url( 'order-received' ) ) {
			return;
		}

		// Get product ID.
		$product_id = absint( get_option( 'hp_product_listing_claim' ) );

		if ( empty( $product_id ) ) {
			return;
		}

		// Get order.
		$order = wc_get_order( get_query_var( 'order-received' ) );

		if ( empty( $order ) || ! in_array( $order->get_status(), [ 'processing', 'completed' ], true ) || ! in_array( $product_id, hivepress()->woocommerce->get_order_product_ids( $order ), true ) ) {
			return;
		}

		// Get claim ID.
		$claim_id = Models\Listing_Claim::query()->filter(
			[
				'user'       => get_current_user_id(),
				'status__in' => [ 'pending', 'publish' ],
			]
		)->order( [ 'created_date' => 'desc' ] )
		->get_first_id();

		if ( empty( $claim_id ) ) {
			return;
		}

		// Redirect page.
		wp_safe_redirect( hivepress()->router->get_url( 'listing_claim_submit_complete_page' ) );

		exit;
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
			$listing_id = wp_get_post_parent_id( $claim_id );

			if ( $listing_id ) {

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
	 * Alters submission form.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function alter_submission_form( $form ) {

		// Get product.
		$product = null;

		if ( hp\is_plugin_active( 'woocommerce' ) && get_option( 'hp_product_listing_claim' ) ) {
			$product = wc_get_product( get_option( 'hp_product_listing_claim' ) );
		}

		if ( $product ) {

			// Set form arguments.
			$form = hp\merge_arrays(
				$form,
				[
					'message'  => null,
					'redirect' => hivepress()->router->get_url( 'listing_claim_submit_complete_page' ),

					'button'   => [
						'label' => sprintf( esc_html__( 'Claim for %s', 'hivepress-claim-listings' ), hivepress()->woocommerce->get_product_price_text( $product ) ),
					],
				]
			);
		}

		return $form;
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
								'_order' => 40,
							],
						],
					],
				],
			]
		);
	}
}
