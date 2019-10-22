<?php
/**
 * Styles configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'claim_listings_frontend' => [
		'handle'  => 'hp-claim-listings-frontend',
		'src'     => HP_CLAIM_LISTINGS_URL . '/assets/css/frontend.min.css',
		'version' => HP_CLAIM_LISTINGS_VERSION,
	],
];
