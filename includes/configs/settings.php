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
			'claims' => [
				'title'  => esc_html__( 'Claims', 'hivepress-claim-listings' ),
				'order'  => 40,

				'fields' => [
					'product_listing_claim'           => [
						'label'       => hivepress()->translator->get_string( 'ecommerce_product' ),
						'description' => esc_html__( 'Choose a product that must be purchased in order to submit a claim.', 'hivepress-claim-listings' ),
						'type'        => 'select',
						'options'     => 'posts',
						'post_type'   => 'product',
						'order'       => 10,
					],

					'listing_claim_enable_moderation' => [
						'label'   => hivepress()->translator->get_string( 'moderation' ),
						'caption' => esc_html__( 'Manually approve new claims', 'hivepress-claim-listings' ),
						'type'    => 'checkbox',
						'default' => true,
						'order'   => 20,
					],
				],
			],

			'emails'   => [
				'fields' => [
					'email_listing_claim_approve' => [
						'label'       => esc_html__( 'Claim Approved', 'hivepress-claim-listings' ),
						'description' => esc_html__( 'This email is sent to users when claim is approved, the following tokens are available: %user_name%, %listing_title%, %listing_url%.', 'hivepress-claim-listings' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Your claim for listing "%listing_title%" has been approved, click on the following link to edit it: %listing_url%', 'hivepress-claim-listings' ) ),
						'html'        => 'post',
						'required'    => true,
						'autoload'    => false,
						'order'       => 40,
					],

					'email_listing_claim_reject'  => [
						'label'       => esc_html__( 'Claim Rejected', 'hivepress-claim-listings' ),
						'description' => esc_html__( 'This email is sent to users when claim is rejected, the following tokens are available: %user_name%, %listing_title%.', 'hivepress-claim-listings' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Unfortunately, your claim for listing "%listing_title%" has been rejected.', 'hivepress-claim-listings' ) ),
						'html'        => 'post',
						'required'    => true,
						'autoload'    => false,
						'order'       => 50,
					],
				],
			],
		],
	],
];
