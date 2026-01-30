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
				'_order' => 10,

				'fields' => [
					'listing_claim_categories'        => [
						'label'       => hivepress()->translator->get_string( 'listing_categories' ),
						'description' => esc_html__( 'Select categories where claiming should be available, or leave empty for all categories.', 'hivepress-claim-listings' ),
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_listing_category' ],
						'multiple'    => true,
						'_order'      => 10,
					],

					'product_listing_claim'           => [
						'label'       => hivepress()->translator->get_string( 'ecommerce_product' ),
						'description' => esc_html__( 'Choose a product that must be purchased in order to submit a claim.', 'hivepress-claim-listings' ),
						'type'        => 'select',
						'options'     => 'posts',
						'option_args' => [ 'post_type' => 'product' ],
						'_order'      => 20,
					],

					'listing_claim_enable_moderation' => [
						'label'   => hivepress()->translator->get_string( 'moderation' ),
						'caption' => esc_html__( 'Manually approve new claims', 'hivepress-claim-listings' ),
						'type'    => 'checkbox',
						'default' => true,
						'_order'  => 30,
					],
				],
			],
		],
	],
];
