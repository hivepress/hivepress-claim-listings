<?php
/**
 * Plugin Name: HivePress Claim Listings
 * Description: Charge users for claiming listings.
 * Requires at least: 5.7
 * Requires PHP: 7.4
 * Version: 1.1.4
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
