<?php
/**
 * Meta boxes configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'listing_claim_details' => [
		'title'  => esc_html__( 'Details', 'hivepress-claim-listings' ),
		'screen' => 'listing_claim',
		'fields' => [],
	],
];
