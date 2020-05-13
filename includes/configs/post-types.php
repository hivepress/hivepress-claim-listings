<?php
/**
 * Post types configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'listing_claim' => [
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => 'edit.php?post_type=hp_listing',
		'supports'     => [ 'title', 'author' ],

		'labels'       => [
			'name'               => esc_html__( 'Claims', 'hivepress-claim-listings' ),
			'singular_name'      => esc_html__( 'Claim', 'hivepress-claim-listings' ),
			'add_new'            => esc_html_x( 'Add New', 'claim', 'hivepress-claim-listings' ),
			'add_new_item'       => esc_html__( 'Add Claim', 'hivepress-claim-listings' ),
			'edit_item'          => esc_html__( 'Edit Claim', 'hivepress-claim-listings' ),
			'new_item'           => esc_html__( 'Add Claim', 'hivepress-claim-listings' ),
			'all_items'          => esc_html__( 'Claims', 'hivepress-claim-listings' ),
			'search_items'       => esc_html__( 'Search Claims', 'hivepress-claim-listings' ),
			'not_found'          => esc_html__( 'No claims found.', 'hivepress-claim-listings' ),
			'not_found_in_trash' => esc_html__( 'No claims found.', 'hivepress-claim-listings' ),
		],
	],
];
