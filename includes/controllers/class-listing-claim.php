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
final class Listing_Claim extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					'listing_claims_resource'            => [
						'path' => '/listing-claims',
						'rest' => true,
					],

					'listing_claim_submit_action'        => [
						'base'   => 'listing_claims_resource',
						'method' => 'POST',
						'action' => [ $this, 'submit_listing_claim' ],
						'rest'   => true,
					],

					'listing_claim_submit_page'          => [
						'path' => '/claim-listing/?(?P<listing_claim_id>\d+)?',
					],

					'listing_claim_submit_complete_page' => [
						'base'     => 'listing_claim_submit_page',
						'path'     => '/complete',
						'title'    => [ $this, 'get_listing_claim_submit_complete_title' ],
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
	 * Submits listing claim.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function submit_listing_claim( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Validate form.
		$form = ( new Forms\Listing_Claim_Submit() )->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Get user ID.
		$user_id = $request->get_param( 'user' ) ? $request->get_param( 'user' ) : get_current_user_id();

		// Get user.
		$user = Models\User::query()->get_by_id( $user_id );

		if ( empty( $user ) ) {
			return hp\rest_error( 400 );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_users' ) && get_current_user_id() !== $user->get_id() ) {
			return hp\rest_error( 403 );
		}

		// Get listing.
		$listing = Models\Listing::query()->get_by_id( $request->get_param( 'listing' ) );

		if ( empty( $listing ) || $listing->get_status() !== 'publish' || $listing->is_verified() ) {
			return hp\rest_error( 400 );
		}

		if ( $listing->get_user__id() === $user->get_id() ) {
			return hp\rest_error( 403, hivepress()->translator->get_string( 'you_cant_claim_your_own_listings' ) );
		}

		// Get claim status.
		$status = 'publish';

		if ( current_user_can( 'edit_users' ) && $request->get_param( 'status' ) ) {
			$status = sanitize_key( $request->get_param( 'status' ) );
		} else {
			if ( hp\is_plugin_active( 'woocommerce' ) && get_option( 'hp_product_listing_claim' ) ) {
				$status = 'draft';
			} elseif ( get_option( 'hp_listing_claim_enable_moderation' ) ) {
				$status = 'pending';
			}
		}

		// Add claim.
		$claim = ( new Models\Listing_Claim() )->fill(
			array_merge(
				$form->get_values(),
				[
					'status'  => $status,
					'user'    => $user->get_id(),
					'listing' => $listing->get_id(),
				]
			)
		);

		if ( ! $claim->save() ) {
			return hp\rest_error( 400, $claim->_get_errors() );
		}

		return hp\rest_response(
			201,
			[
				'id' => $claim->get_id(),
			]
		);
	}

	/**
	 * Gets listing claim submit complete title.
	 *
	 * @return string
	 */
	public function get_listing_claim_submit_complete_title() {
		$title = esc_html__( 'Claim Submitted', 'hivepress-claim-listings' );

		if ( is_user_logged_in() ) {

			// Get claim.
			$claim = null;

			if ( hivepress()->request->get_param( 'listing_claim_id' ) ) {
				$claim = Models\Listing_Claim::query()->get_by_id( hivepress()->request->get_param( 'listing_claim_id' ) );
			} else {
				$claim = Models\Listing_Claim::query()->filter(
					[
						'user'       => get_current_user_id(),
						'status__in' => [ 'draft', 'pending', 'publish' ],
					]
				)->order( [ 'created_date' => 'desc' ] )
				->get_first();
			}

			// Set page title.
			if ( $claim && $claim->get_status() === 'publish' ) {
				$title = esc_html__( 'Claim Approved', 'hivepress-claim-listings' );
			}

			// Set request context.
			hivepress()->request->set_context( 'listing_claim', $claim );
		}

		return $title;
	}

	/**
	 * Redirects listing claim submit complete page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_claim_submit_complete_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Get claim.
		$claim = hivepress()->request->get_context( 'listing_claim' );

		if ( empty( $claim ) || $claim->get_user__id() !== get_current_user_id() || ! in_array( $claim->get_status(), [ 'draft', 'pending', 'publish' ], true ) ) {
			return true;
		}

		if ( $claim->get_status() === 'draft' ) {

			// Get product ID.
			$product_id = absint( get_option( 'hp_product_listing_claim' ) );

			if ( hp\is_plugin_active( 'woocommerce' ) && $product_id ) {

				// Add product to cart.
				WC()->cart->empty_cart();
				WC()->cart->add_to_cart( $product_id, 1, 0, [], [ 'hp_listing_claim' => $claim->get_id() ] );

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
					'listing_claim' => hivepress()->request->get_context( 'listing_claim' ),
				],
			]
		) )->render();
	}
}
