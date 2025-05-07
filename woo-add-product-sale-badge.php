<?php
/**
 * Plugin Name: Add Product Sale Badge to Product Image via Block Hooks
 * Plugin URI: https://woocommerce.com/
 * Author: Automattic
 * Author URI: https://woocommerce.com
 * Requires at least: 6.6
 * Requires PHP: 7.4
 *
 * @package woocommerce-block-hooks-experiments
 */

add_filter(
    'hooked_block_types',
    function ( $hooked_block_types, $relative_position, $anchor_block_type ) {
        if ( 'woocommerce/product-image' === $anchor_block_type && 'first_child' === $relative_position ) {
            $hooked_block_types[] = 'woocommerce/product-sale-badge';
        }
        return $hooked_block_types;
    },
    10,
    3
);

add_filter(
    'hooked_block_woocommerce/product-sale-badge',
    function( $parsed_hooked_block, $hooked_block_type, $relative_position, $parsed_anchor_block ) {
        if ( 'woocommerce/product-image' === $parsed_anchor_block['blockName'] && 'first_child' === $relative_position ) {
            $parsed_hooked_block['attrs']['isDescendentOfQueryLoop'] = true;
            $parsed_hooked_block['attrs']['isDescendentOfSingleProductTemplate'] = true;
        }
        return $parsed_hooked_block;
    },
    10,
    4
);

add_filter( 'woocommerce_product_sale_badge_label', function( $badge_text, $product ) {
	$regular_price = $product->get_regular_price();
	$sale_price    = $product->get_sale_price();

	$percentage_off = ( $regular_price - $sale_price ) / $regular_price * 100;

	if ( $percentage_off > 5 ) {
		$badge_text = round( $percentage_off ) . '%';
	} else {
		$badge_text = '%';
	}

	return $badge_text;
}, 10, 2 );