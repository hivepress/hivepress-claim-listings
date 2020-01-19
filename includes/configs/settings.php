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

			'emails'     => [
				'title'  => hivepress()->translator->get_string( 'emails' ),
				'_order' => 1000,

				'fields' => [
					'email_listing_claim_approve' => [
						'label'       => esc_html__( 'Claim Approved', 'hivepress-claim-listings' ),
						'description' => esc_html__( 'This email is sent to users when claim is approved.', 'hivepress-claim-listings' ) . ' ' . sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '%user_name%, %listing_title%, %listing_url%' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Your claim for listing "%listing_title%" has been approved, click on the following link to edit it: %listing_url%', 'hivepress-claim-listings' ) ),
						'max_length'  => 2048,
						'html'        => true,
						'_autoload'   => false,
						'_order'      => 10,
					],

					'email_listing_claim_reject'  => [
						'label'       => esc_html__( 'Claim Rejected', 'hivepress-claim-listings' ),
						'description' => esc_html__( 'This email is sent to users when claim is rejected.', 'hivepress-claim-listings' ) . ' ' . sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '%user_name%, %listing_title%' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Unfortunately, your claim for listing "%listing_title%" has been rejected.', 'hivepress-claim-listings' ) ),
						'max_length'  => 2048,
						'html'        => true,
						'_autoload'   => false,
						'_order'      => 20,
					],
				],
			],
		],
	],
];
