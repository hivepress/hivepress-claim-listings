<?php
/**
 * Listing claim controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Forms;
use HivePress\Models;
use HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing claim controller class.
 *
 * @class Listing_Claim
 */
class Listing_Claim extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					[
						'path'   => '/listing-claims',
						'rest'   => true,

						'routes' => [
							[
								'method' => 'POST',
								'action' => [ $this, 'submit_claim' ],
							],
						],
					],

					'submit_complete' => [
						'title'    => esc_html__( 'Claim Submitted', 'hivepress-claim-listings' ),
						'path'     => '/claim-listing/(?P<listing_id>\d+)/complete',
						'redirect' => [ $this, 'redirect_listing_claim_submit_complete_page' ],
						'action'   => [ $this, 'render_listing_claim_submit_complete_page' ],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Submits claim.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function submit_claim( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Validate form.
		$form = new Forms\Listing_Claim_Submit();

		$form->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Get user.
		$user_id = $request->get_param( 'user_id' ) ? $request->get_param( 'user_id' ) : get_current_user_id();
		$user    = get_userdata( $user_id );

		if ( false === $user ) {
			return hp\rest_error( 400 );
		}

		if ( get_current_user_id() !== $user->ID && ! current_user_can( 'edit_users' ) ) {
			return hp\rest_error( 403 );
		}

		// Get listing ID.
		$listing_id = hp\get_post_id(
			[
				'post_type'   => 'hp_listing',
				'post_status' => 'publish',
				'post__in'    => [ absint( $request->get_param( 'listing_id' ) ) ],
			]
		);

		if ( 0 === $listing_id ) {
			return hp\rest_error( 400 );
		}

		// Get claim ID.
		$claim_id = hp\get_post_id(
			[
				'post_type'   => 'hp_listing_claim',
				'post_status' => [ 'draft', 'pending', 'publish' ],
				'post_parent' => $listing_id,
				'author'      => $user_id,
			]
		);

		if ( 0 !== $claim_id ) {
			return hp\rest_error( 400, esc_html__( "You've already submitted a claim.", 'hivepress-claim-listings' ) );
		}

		// Get claim status.
		$status = 'publish';

		if ( $request->get_param( 'status' ) && current_user_can( 'edit_users' ) ) {
			$status = sanitize_key( $request->get_param( 'status' ) );
		} else {
			if ( class_exists( 'WooCommerce' ) && get_option( 'hp_product_listing_claim' ) ) {
				$status = 'draft';
			} elseif ( get_option( 'hp_listing_claim_enable_moderation' ) ) {
				$status = 'pending';
			}
		}

		// Add claim.
		$claim = new Models\Listing_Claim();

		$claim->fill(
			array_merge(
				$form->get_values(),
				[
					'status'     => $status,
					'user_id'    => $user->ID,
					'listing_id' => $listing_id,
				]
			)
		);

		if ( ! $claim->save() ) {
			return hp\rest_error( 400, $claim->_get_errors() );
		}

		return new \WP_Rest_Response(
			[
				'data' => [
					'id' => $claim->get_id(),
				],
			],
			200
		);
	}

	/**
	 * Redirects listing claim submit complete page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_claim_submit_complete_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return add_query_arg( 'redirect', rawurlencode( hp\get_current_url() ), User::get_url( 'login_user' ) );
		}

		// Get listing ID.
		$listing_id = hp\get_post_id(
			[
				'post_type'   => 'hp_listing',
				'post_status' => 'publish',
				'post__in'    => [ absint( get_query_var( 'hp_listing_id' ) ) ],
			]
		);

		if ( 0 === $listing_id ) {
			return true;
		}

		// Get claim ID.
		$claim_id = hp\get_post_id(
			[
				'post_type'   => 'hp_listing_claim',
				'post_status' => [ 'draft', 'pending', 'publish' ],
				'post_parent' => $listing_id,
				'author'      => get_current_user_id(),
			]
		);

		if ( 0 === $claim_id ) {
			return true;
		} elseif ( get_post_status( $claim_id ) === 'draft' ) {
			if ( class_exists( 'WooCommerce' ) && get_option( 'hp_product_listing_claim' ) ) {

				// Add product to cart.
				WC()->cart->empty_cart();
				WC()->cart->add_to_cart( get_option( 'hp_product_listing_claim' ) );

				return wc_get_page_permalink( 'checkout' );
			}

			return true;
		}

		return false;
	}

	/**
	 * Renders listing claim submit complete page.
	 *
	 * @return string
	 */
	public function render_listing_claim_submit_complete_page() {
		return ( new Blocks\Template(
			[
				'template' => 'listing_claim_submit_complete_page',

				'context'  => [
					'listing' => Models\Listing::query()->get_by_id( get_query_var( 'hp_listing_id' ) ),
				],
			]
		) )->render();
	}
}
