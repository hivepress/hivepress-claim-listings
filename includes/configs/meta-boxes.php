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
	'listing_claim_details'  => [
		'title'  => hivepress()->translator->get_string( 'details' ),
		'screen' => 'listing_claim',

		'fields' => [
			'details' => [
				'type'       => 'textarea',
				'max_length' => 10240,
				'_alias'     => 'post_content',
				'_order'     => 10,
			],
		],
	],

	'listing_claim_settings' => [
		'title'  => hivepress()->translator->get_string( 'settings' ),
		'screen' => 'listing_claim',

		'fields' => [
			'listing' => [
				'label'       => hivepress()->translator->get_string( 'listing' ),
				'type'        => 'select',
				'options'     => 'posts',
				'option_args' => [ 'post_type' => 'hp_listing' ],
				'source'      => hivepress()->router->get_url( 'listings_resource' ),
				'required'    => true,
				'_alias'      => 'post_parent',
				'_order'      => 10,
			],
		],
	],
];
