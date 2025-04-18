<?php declare( strict_types=1 );

require_once __DIR__ . '/util.php';

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