<?php
/**
 * Listing claim submit form block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing claim submit form block class.
 *
 * @class Listing_Claim_Submit_Form
 */
class Listing_Claim_Submit_Form extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'form' => 'listing_claim_submit',
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {
		if ( is_singular( 'hp_listing' ) ) {

			// Set listing ID.
			$this->values['listing_id'] = get_the_ID();

			// Set redirect URL.
			if ( ! get_option( 'hp_listing_claim_enable_moderation' ) || ( class_exists( 'WooCommerce' ) && get_option( 'hp_product_listing_claim' ) ) ) {
				$this->attributes['data-redirect'] = Controllers\Listing_Claim::get_url( 'submit_complete', [ 'listing_id' => get_the_ID() ] );
			}
		}

		parent::boot();
	}
}
