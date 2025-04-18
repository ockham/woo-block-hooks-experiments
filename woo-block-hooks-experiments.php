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

			// We wrap our hooked paragraph block in an Accordion Panel block inside of an Accordion Item block.
			$parsed_hooked_block = create_accordion_item_block(
				array(), // Attributes for Accordion Item block.
				array(
					// Inner blocks of the Accordion Item block: Header and Panel.
					create_accordion_header_block( array( 'title' => 'Accordion Header block' ) ),
					create_accordion_panel_block( array(), array( $parsed_hooked_block ) )
				)
			);
		}

		return $parsed_hooked_block;
	},
	10,
	5
);






