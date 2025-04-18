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

function woo_get_block_wrapper_attributes( $block, $extra_wrapper_attributes = array() ) {
	$previous_block_to_render = WP_Block_Supports::$block_to_render;

	WP_Block_Supports::$block_to_render = $block;

	$block_wrapper_attributes = get_block_wrapper_attributes( $extra_wrapper_attributes );

	WP_Block_Supports::$block_to_render = $previous_block_to_render;

	return $block_wrapper_attributes;
}

function filter_block_attributes( $block_type_name, $attrs ) {
	$block_type            = WP_Block_Type_Registry::get_instance()->get_registered( $block_type_name );
	$known_attribute_names = $block_type->get_attributes();

	$known_attributes = array();

	foreach ( $known_attribute_names as $attribute_name => $attribute_definition ) {
		if ( isset( $attrs[ $attribute_name ] ) ) {
			$known_attributes[ $attribute_name ] = $attrs[ $attribute_name ];
		} elseif ( isset( $attribute_definition['default'] ) ) {
			$known_attributes[ $attribute_name ] = $attribute_definition['default'];
		}
	}
	return $known_attributes;
}

function create_accordion_header_block( $attrs ) {
	$block_type_name = 'woocommerce/accordion-header';

	$attrs = filter_block_attributes( $block_type_name, $attrs );

	$block = array(
		'blockName'   => $block_type_name,
		'attrs'       => $attrs,
		'innerBlocks' => array(),
	);

	// TODO: Add other icons.
	$icons = array(
		'plus' => '<svg
			width={ width || 24 }
			height={ height || 24 }
			viewBox="0 0 24 24"
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
		>
			<Path
				d="M11 12.5V17.5H12.5V12.5H17.5V11H12.5V6H11V11H6V12.5H11Z"
				fill="currentColor"
			/>
		</svg>',
	);

	$heading_classes = array( 'accordion-item__heading' );
	// TODO: Add conditional classes based on attributes.
	
	// Create the icon HTML
	$icon_classes = array( 'accordion-item__toggle-icon' );
	if ( ! empty( $attrs['icon'] ) ) {
		$icon = $icons[$attrs['icon']];
		$icon_classes[] = "has-icon-{$attrs['icon']}";
	}

	$icon_class_string = implode( ' ', $icon_classes );
	
	// Define tag name based on level
	$tag_name = "h{$attrs['level']}";

	$block_wrapper_attributes = woo_get_block_wrapper_attributes(
		$block,
		array( 'class' => implode( ' ', $heading_classes ) )
	);

	$block['innerContent'] = array(
		"<{$tag_name} {$block_wrapper_attributes}>",
		"<button class=\"accordion-item__toggle\">",
		"<span>{$attrs['title']}</span>",
		"<span class=\"{$icon_class_string}\" style=\"width:1.2em;height:1.2em;\">",
		$icon,
		"</span>",
		"</button>",
		"</{$tag_name}>"
	);
	return $block;
}

function create_accordion_panel_block( $attrs, $inner_blocks = array() ) {
	$block_type_name = 'woocommerce/accordion-panel';

	$attrs = filter_block_attributes( $block_type_name, $attrs );

	$block = array(
		'blockName'   => $block_type_name,
		'attrs'       => $attrs,
		'innerBlocks' => $inner_blocks,
	);

	$block_wrapper_attributes = woo_get_block_wrapper_attributes( $block );

	$block['innerContent'] = array_merge(
		array( "<div $block_wrapper_attributes>" ),
		array_fill( 0, count( $inner_blocks ), null ),
		array( "</div>" )
	);
	return $block;
}

function create_accordion_item_block( $attrs, $inner_blocks = array() ) {
	$block_type_name = 'woocommerce/accordion-item';

	$attrs = filter_block_attributes( $block_type_name, $attrs );

	$block = array(
		'blockName'   => $block_type_name,
		'attrs'       => $attrs,
		'innerBlocks' => $inner_blocks,
	);

	$block_wrapper_attributes = woo_get_block_wrapper_attributes( $block );

	$block['innerContent'] = array_merge(
		array( "<div $block_wrapper_attributes>" ),
		array_fill( 0, count( $inner_blocks ), null ),
		array( "</div>" )
	);
	return $block;
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






