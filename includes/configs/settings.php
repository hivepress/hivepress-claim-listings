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

				'fields' => [],
			],
		],
	],
];
