<?php
/**
 * Settings configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'listing_claims' => [
		'title'    => esc_html__( 'Claims', 'hivepress-claim-listings' ),
		'_order'   => 30,

		'sections' => [
			'submission' => [
				'title'  => hivepress()->translator->get_string( 'submission' ),
				'_order' => 10,

				'fields' => [
					'product_listing_claim'           => [
						'label'       => hivepress()->translator->get_string( 'ecommerce_product' ),
						'description' => esc_html__( 'Choose a product that must be purchased in order to submit a claim.', 'hivepress-claim-listings' ),
						'type'        => 'select',
						'options'     => 'posts',
						'option_args' => [ 'post_type' => 'product' ],
						'_order'      => 10,
					],

					'listing_claim_enable_moderation' => [
						'label'   => hivepress()->translator->get_string( 'moderation' ),
						'caption' => esc_html__( 'Manually approve new claims', 'hivepress-claim-listings' ),
						'type'    => 'checkbox',
						'default' => true,
						'_order'  => 20,
					],
				],
			],
		],
	],
];
