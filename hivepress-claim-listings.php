<?php
/**
 * Plugin Name: HivePress Claim Listings
 * Description: Charge users for claiming listings.
 * Version: 1.1.3
 * Author: HivePress
 * Author URI: https://hivepress.io/
 * Text Domain: hivepress-claim-listings
 * Domain Path: /languages/
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Register extension directory.
add_filter(
	'hivepress/v1/extensions',
	function( $extensions ) {
		$extensions[] = __DIR__;

		return $extensions;
	}
);
