<?php
/**
 * Claim controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Forms;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Claim controller class.
 *
 * @class Claim
 */
class Claim extends Controller {

	/**
	 * Controller name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Controller routes.
	 *
	 * @var array
	 */
	protected static $routes = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Controller arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					[
						'path'      => '/claims',
						'rest'      => true,

						'endpoints' => [
							[
								'methods' => 'POST',
								'action'  => 'submit_claim',
							],
						],
					],
				],
			],
			$args
		);

		parent::init( $args );
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

		// Get claim status.
		$status = 'publish';

		if ( $request->get_param( 'status' ) && current_user_can( 'edit_users' ) ) {
			$status = sanitize_key( $request->get_param( 'status' ) );
		} else {

			// Get product ID.
			$product_id = 0;

			if ( get_option( 'hp_product_claim' ) ) {
				$product_id = hp\get_post_id(
					[
						'post_type'   => 'product',
						'post_status' => 'publish',
						'post__in'    => [ absint( get_option( 'hp_product_claim' ) ) ],
					]
				);
			}

			// Set claim status.
			if ( 0 !== $product_id ) {
				$status = 'draft';
			} elseif ( get_option( 'hp_claim_enable_moderation' ) ) {
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
			return hp\rest_error( 400 );
		}

		// Set claim title.
		$claim->set_title( '#' . $claim->get_id() );
		$claim->save();

		return new \WP_Rest_Response(
			[
				'data' => [
					'id' => $claim->get_id(),
				],
			],
			200
		);
	}
}
