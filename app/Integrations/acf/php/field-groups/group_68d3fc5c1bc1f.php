<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_68d3fc5c1bc1f',
	'title' => 'Taxonomies',
	'fields' => array(
		array(
			'key' => 'field_68d3fc5cfafe4',
			'label' => 'Image',
			'name' => 'thumbnail_id',
			'aria-label' => '',
			'type' => 'image',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_field' => '',
			'hide_label' => '',
			'hide_instructions' => '',
			'hide_required' => '',
			'uploader' => '',
			'admin_column_enabled' => 1,
			'admin_column_position' => 2,
			'admin_column_width' => '40px',
			'return_format' => 'array',
			'library' => 'all',
			'upload_folder' => '',
			'acfe_thumbnail' => 1,
			'acfe_settings' => '',
			'acfe_validate' => '',
			'min_width' => '',
			'min_height' => '',
			'min_size' => '',
			'max_width' => '',
			'max_height' => '',
			'max_size' => '',
			'mime_types' => '',
			'instruction_placement' => '',
			'acfe_permissions' => '',
			'allow_in_bindings' => 0,
			'preview_size' => 'medium',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'taxonomy',
				'operator' => '==',
				'value' => 'product_audience',
			),
			array(
				'param' => 'taxonomy',
				'operator' => '==',
				'value' => 'product_occasion',
			),
			array(
				'param' => 'taxonomy',
				'operator' => '==',
				'value' => 'product_season',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 0,
	'acfe_autosync' => array(
		0 => 'php',
	),
	'acfe_form' => 1,
	'acfe_display_title' => '',
	'acfe_permissions' => '',
	'acfe_meta' => '',
	'acfe_note' => '',
	'modified' => 1758753658,
));

endif;