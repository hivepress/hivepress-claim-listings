<?php
/**
 * Listing claim submit form block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

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

		parent::bootstrap();
	}
}
