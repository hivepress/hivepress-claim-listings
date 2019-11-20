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
				'alias'    => 'post_content',
				'type'     => 'textarea',
				'required' => true,
				'order'    => 10,
			],
		],
	],

	'listing_claim_settings' => [
		'title'  => hivepress()->translator->get_string( 'settings' ),
		'screen' => 'listing_claim',

		'fields' => [
			'listing' => [
				'label'     => hivepress()->translator->get_string( 'listing' ),
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
