<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_68c6e8574e1b3',
	'title' => 'Product Video',
	'fields' => array(
		array(
			'key' => 'field_68c6ea0918552',
			'label' => 'Source',
			'name' => 'source',
			'aria-label' => '',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
				'null' => 'None',
				'internal' => 'Internal',
				'external' => 'External',
			),
			'default_value' => 'null',
			'return_format' => 'value',
			'multiple' => 0,
			'allow_custom' => 0,
			'placeholder' => '',
			'search_placeholder' => '',
			'allow_null' => 0,
			'allow_in_bindings' => 0,
			'ui' => 0,
			'ajax' => 0,
			'create_options' => 0,
			'save_options' => 0,
		),
		array(
			'key' => 'field_68c6e857278f1',
			'label' => 'Video',
			'name' => 'video',
			'aria-label' => '',
			'type' => 'file',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_68c6ea0918552',
						'operator' => '==',
						'value' => 'internal',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'uploader' => '',
			'return_format' => 'array',
			'min_size' => '',
			'max_size' => '',
			'mime_types' => 'mp4, webm, ogv, avi, mpg, mpeg, mov, wmv, 3gp, 3g2',
			'allow_in_bindings' => 0,
			'library' => 'all',
		),
		array(
			'key' => 'field_68c6eb1518553',
			'label' => 'Link',
			'name' => 'link',
			'aria-label' => '',
			'type' => 'oembed',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_68c6ea0918552',
						'operator' => '==',
						'value' => 'external',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'width' => '',
			'height' => '',
			'allow_in_bindings' => 0,
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'product',
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
	'acfe_display_title' => '',
	'acfe_autosync' => array(
		0 => 'php',
		1 => 'json',
	),
	'acfe_form' => 0,
	'acfe_meta' => '',
	'acfe_note' => '',
	'modified' => 1757867139,
));

endif;