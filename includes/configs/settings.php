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
	'listings' => [
		'sections' => [
			'claiming' => [
				'title'  => esc_html__( 'Claiming', 'hivepress-claim-listings' ),
				'order'  => 30,

				'fields' => [
					'product_claim_todo'           => [
						'label'       => esc_html__( 'WooCommerce Product', 'hivepress-claim-listings' ),
						'description' => esc_html__( 'Choose a WooCommerce product that must be purchased in order to submit a claim.', 'hivepress-claim-listings' ),
						'type'        => 'select',
						'options'     => 'posts',
						'post_type'   => 'product',
						'order'       => 10,
					],

					'claim_enable_moderation_todo' => [
						'label'   => esc_html__( 'Moderation', 'hivepress-claim-listings' ),
						'caption' => esc_html__( 'Manually approve new claims', 'hivepress-claim-listings' ),
						'type'    => 'checkbox',
						'default' => true,
						'order'   => 20,
					],
				],
			],
		],
	],
];
