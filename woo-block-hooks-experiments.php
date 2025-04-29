<?php
/**
 * Plugin Name: Block Hooks Experiments for WooCommerce
 * Plugin URI: https://woocommerce.com/
 * Author: Automattic
 * Author URI: https://woocommerce.com
 * Requires at least: 6.6
 * Requires PHP: 7.4
 *
 * @package woocommerce-block-hooks-experiments
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/create-blocks/create-accordion-header-block.php';
require_once __DIR__ . '/create-blocks/create-accordion-panel-block.php';
require_once __DIR__ . '/create-blocks/create-accordion-item-block.php';

add_filter(
	'hooked_block_types',
	function ( $hooked_block_types, $relative_position, $anchor_block_type, $context ) {
		if ( 'woocommerce/accordion-group' === $anchor_block_type && 'last_child' === $relative_position ) {
			$hooked_block_types[] = 'core/paragraph';
		}
		return $hooked_block_types;
	},
	10,
	4
);

function set_block_parent( $metadata ) {
    // Only add the parent to our test block type.
    if ( isset( $metadata['name'] ) && 'core/paragraph' === $metadata['name'] ) {
		$metadata['parent'] = 'woocommerce/accordion-panel';
    }
	return $metadata;
};
add_filter( 'block_type_metadata', 'set_block_parent' );

function find_block_wrapper_path( $ancestor, $descendant ) {
	$ancestor_block_type_definition = WP_Block_Type_Registry::get_instance()->get_registered( $ancestor );
	$allowed_blocks = $ancestor_block_type_definition->allowed_blocks ?? array();
	if ( empty( $allowed_blocks ) || in_array( $descendant, $allowed_blocks, true ) ) {
		// Check if the descendant also lists the ancestor as its parent.
		$descendant_block_type_definition = WP_Block_Type_Registry::get_instance()->get_registered( $descendant );
		if ( ! empty( $descendant_block_type_definition->parent ) && $ancestor === $descendant_block_type_definition->parent ) {
			return array( $descendant);
		}
	}

	foreach ( $allowed_blocks as $allowed_block ) {
		$path = find_block_wrapper_path( $allowed_block, $descendant );
		if ( ! empty( $path ) ) {
			return array_merge( array( $allowed_block ), $path );
		}
	}
}

add_filter(
	'hooked_block_core/paragraph',
	function (
		$parsed_hooked_block,
		$hooked_block_type,
		$relative_position,
		$parsed_anchor_block,
		$context
	) {

		if ( is_null( $parsed_hooked_block ) ) {
			return $parsed_hooked_block;
		}

		// TODO: Verify that the anchor Accordion Group block is a child of the Product Details block.
		
		if ( 'woocommerce/accordion-group' === $parsed_anchor_block['blockName'] && $relative_position === 'last_child' ) {
			$parsed_hooked_block['innerContent'] = array( '<p>Hello World!</p>' );

			$path = find_block_wrapper_path( $parsed_anchor_block['blockName'], $hooked_block_type );

			$wrapper = $parsed_hooked_block;
			array_pop( $path );
			foreach( array_reverse( $path ) as $block ) {
				$wrapper_block_function = 'create_' . str_replace( array( '/', '-' ) , '_', $block ) . '_block';
				if ( function_exists( $wrapper_block_function ) ) {
					$wrapper = call_user_func( $wrapper_block_function, array(), array( $wrapper ) );
				}
			}

			$parsed_hooked_block = $wrapper;
		}

		return $parsed_hooked_block;
	},
	10,
	5
);






