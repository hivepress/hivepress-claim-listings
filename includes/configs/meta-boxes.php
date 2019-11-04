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
	'listing_claim_settings' => [
		'title'  => esc_html__( 'Settings', 'hivepress-claim-listings' ),
		'screen' => 'listing_claim',
		'fields' => [
			'listing' => [
				'label'     => esc_html__( 'Listing', 'hivepress-claim-listings' ),
				'alias'     => 'post_parent',
				'type'      => 'select',
				'options'   => 'posts',
				'post_type' => 'hp_listing',
				'required'  => true,
				'order'     => 10,
			],
		],
	],
];
