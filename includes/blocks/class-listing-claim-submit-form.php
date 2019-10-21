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
	 * Block type.
	 *
	 * @var string
	 */
	protected static $type;

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
	protected function bootstrap() {

		// Set listing ID.
		if ( is_singular( 'hp_listing' ) ) {
			$this->values['listing_id'] = get_the_ID();
		}

		// Get product ID.
		$product_id = 0;

		if ( get_option( 'hp_product_claim' ) && class_exists( 'WooCommerce' ) ) {
			$product_id = hp\get_post_id(
				[
					'post_type'   => 'product',
					'post_status' => 'publish',
					'post__in'    => [ absint( get_option( 'hp_product_claim' ) ) ],
				]
			);
		}

		// Set redirect URL.
		if ( 0 !== $product_id ) {
			$this->attributes['data-redirect'] = wc_get_page_permalink( 'checkout' );
		} elseif ( ! get_option( 'hp_claim_enable_moderation' ) ) {
			$this->attributes['data-redirect'] = Controllers\Claim::get_url( 'submit_complete' );
		}

		parent::bootstrap();
	}
}
