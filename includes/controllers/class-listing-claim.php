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
						'path' => '/claim-listing',
					],

					'listing_claim_submit_complete_page' => [
						'title'    => esc_html__( 'Claim Submitted', 'hivepress-claim-listings' ),
						'base'     => 'listing_claim_submit_page',
						'path'     => '/complete',
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

		// Get user.
		$user_id = $request->get_param( 'user' ) ? $request->get_param( 'user' ) : get_current_user_id();

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

		if ( empty( $listing ) || $listing->get_status() !== 'publish' ) {
			return hp\rest_error( 400 );
		}

		// todo check if already submitted.
		// Get claim status.
		$status = 'publish';

		if ( current_user_can( 'edit_users' ) && $request->get_param( 'status' ) ) {
			$status = sanitize_key( $request->get_param( 'status' ) );
		} else {
			if ( hivepress()->woocommerce->is_active() && get_option( 'hp_product_listing_claim' ) ) {
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
}
