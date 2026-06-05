<?php
/**
 * The MetaBox config file of the plugin.
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/views
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

//
// Set a unique slug-like ID.
//
$prefix = '_sp_wqvpro_options';

// ===============================================================================================
// -----------------------------------------------------------------------------------------------
// FRAMEWORK SETTINGS
// -----------------------------------------------------------------------------------------------
// ===============================================================================================
$settings = array(
	'menu_title'       => __( 'Woo QuickView', 'woo-quickview' ),
	'menu_type'        => 'menu', // menu, submenu, options, theme, etc.
	'menu_slug'        => 'wqv_settings',
	'ajax_save'        => true,
	'show_reset_all'   => false,
	'show_search'      => false,
	'show_footer'      => false,
	'show_all_options' => false,
	'show_sub_menu'    => false,
	'nav'              => 'inline',
	'theme'            => 'light',
	'menu_position'    => 58,
	'menu_icon'        => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0NzEuMiA0NzIuNSIgZmlsbD0iI2E3YWFhZCIgeG1sbnM6dj0iaHR0cHM6Ly92ZWN0YS5pby9uYW5vIj48cGF0aCBkPSJNMzIyLjYgMjI2LjZjLTUuNyAwLTExLjUtMy4xLTE0LTkuNS0xNS45LTM3LjUtNTIuMi02MS4xLTkyLjktNjEuMXMtNzcgMjQuMi05Mi45IDYxLjFjLTMuMSA3LjYtMTIgMTEuNS0xOS43IDguMi03LjctMy4yLTExLjUtMTItOC4yLTE5LjcgMjAuNC00OC40IDY4LjEtODAuMSAxMjAuOS04MC4xczk5LjkgMzEuMSAxMjAuOSA4MC4xYzMuMSA3LjYtLjYgMTYuNi04LjIgMTkuNy0yLjEuNy00IDEuMy01LjkgMS4zeiIvPjxwYXRoIGQ9Ik0zMjIuNiAyMzEuM2MtOC4zIDAtMTUuMi00LjgtMTguMy0xMi40LTcuNC0xNy41LTE5LjctMzIuMi0zNS40LTQyLjVzLTM0LjEtMTUuNy01My4yLTE1LjdjLTM4LjYgMC03My40IDIyLjktODguNyA1OC4zLTIgNC44LTUuOSA4LjctMTAuOCAxMC43cy0xMC4yIDItMTUgMGMtNC45LTItOC43LTUuOC0xMC43LTEwLjctMi4xLTQuOS0yLjEtMTAuMyAwLTE1LjEgMTAuNC0yNC42IDI3LjctNDUuNCA1MC02MC4zIDIyLjQtMTQuOSA0OC40LTIyLjcgNzUuMi0yMi43czUyLjcgNy44IDc0LjkgMjIuNSAzOS42IDM1LjYgNTAuMiA2MC41YzIgNC45IDIgMTAuMi0uMSAxNS4yLTIuMSA0LjktNS44IDguNy0xMC43IDEwLjdsLS4zLjFjLTIgLjYtNC41IDEuNC03LjEgMS40em0tMTA2LjktNzkuOWMyMSAwIDQxLjEgNiA1OC4zIDE3LjIgMTcuMyAxMS4zIDMwLjcgMjcuNCAzOC45IDQ2Ljd2LjFjMS42IDQuMSA1LjIgNi42IDkuNyA2LjYgMS4xIDAgMi42LS41IDQuMS0xIDIuNS0xLjEgNC40LTMuMSA1LjUtNS43YTEwLjU2IDEwLjU2IDAgMCAwIC4xLThjLTkuOS0yMy4yLTI2LjEtNDIuNy00Ni44LTU2LjRTMjQwLjcgMTMwIDIxNS43IDEzMHMtNDkuMiA3LjMtNzAgMjEuMmExMjYuMjEgMTI2LjIxIDAgMCAwLTQ2LjYgNTYuMSA5Ljg3IDkuODcgMCAwIDAgMCA3LjljMS4xIDIuNiAzLjEgNC42IDUuOCA1LjcgMi41IDEuMSA1LjMgMSA3LjggMGExMS4xNCAxMS4xNCAwIDAgMCA1LjgtNS43YzE2LjgtMzguNyA1NC45LTYzLjggOTcuMi02My44eiIvPjxwYXRoIGQ9Ik0yMTUuNyAyOTguNWMtNTIuOCAwLTk5LjktMzEuMS0xMjAuOS04MC4xLTMuMS03LjYuNi0xNi42IDguMi0xOS43czE2LjYuNiAxOS43IDguMmMxNS45IDM3LjUgNTIuMiA2MS4xIDkyLjkgNjEuMXM3Ny0yNC4yIDkyLjktNjEuMWMzLjEtNy42IDEyLTExLjUgMTkuNy04LjIgNy43IDMuMiAxMS41IDEyIDguMiAxOS43LTIwLjIgNDguNC02Ny45IDgwLjEtMTIwLjcgODAuMXoiLz48cGF0aCBkPSJNMjE1LjcgMzAzLjJjLTI2LjggMC01Mi43LTcuOC03NC45LTIyLjVzLTM5LjYtMzUuNi01MC4yLTYwLjVjLTQuMS05LjkuOC0yMS43IDEwLjctMjUuOHMyMS43LjggMjUuOCAxMC44YzcuNCAxNy41IDE5LjcgMzIuMiAzNS40IDQyLjVzMzQuMSAxNS43IDUzLjIgMTUuN2MzOC42IDAgNzMuNC0yMi45IDg4LjctNTguMyAyLTQuOCA1LjktOC43IDEwLjgtMTAuN3MxMC4yLTIgMTUgMGM0LjkgMiA4LjcgNS44IDEwLjcgMTAuNyAyLjEgNC45IDIuMSAxMC4zIDAgMTUuMS0xMC40IDI0LjYtMjcuNyA0NS40LTUwIDYwLjMtMjIuNCAxNC44LTQ4LjQgMjIuNy03NS4yIDIyLjd6bS0xMDctMTAxYy0xLjMgMC0yLjYuMi0zLjguOC01LjMgMi4yLTcuOSA4LjUtNS43IDEzLjcgOS45IDIzLjIgMjYuMSA0Mi43IDQ2LjggNTYuNHM0NC44IDIwLjkgNjkuOCAyMC45IDQ5LjItNy4zIDcwLTIxLjJhMTI2LjIxIDEyNi4yMSAwIDAgMCA0Ni42LTU2LjEgOS44NyA5Ljg3IDAgMCAwIDAtNy45Yy0xLjEtMi42LTMuMS00LjYtNS44LTUuNy0yLjUtMS01LjMtMS03LjggMGExMS4xNCAxMS4xNCAwIDAgMC01LjggNS43Yy0xNi44IDM4LjgtNTQuOSA2My45LTk3LjIgNjMuOS0yMSAwLTQxLjEtNi01OC4zLTE3LjItMTcuMy0xMS4zLTMwLjctMjcuNC0zOC45LTQ2LjctMS44LTQuMS01LjgtNi42LTkuOS02LjZ6bTEwNyA1OC4yYy0yNi43IDAtNDguNC0yMS42LTQ4LjQtNDguNHMyMS42LTQ4LjQgNDguNC00OC40IDQ4LjQgMjEuNiA0OC40IDQ4LjQtMjEuNiA0OC40LTQ4LjQgNDguNHptMC02Ni44YTE4LjQgMTguNCAwIDEgMCAwIDM2LjggMTguNCAxOC40IDAgMSAwIDAtMzYuOHptMSAyMzkuOGMtNTcuOSAwLTExMi4zLTIyLjUtMTUzLjItNjMuNUMyMi41IDMyOSAwIDI3NC42IDAgMjE2LjdTMjIuNSAxMDQuNCA2My41IDYzLjVDMTA0LjQgMjIuNSAxNTguOCAwIDIxNi43IDBTMzI5IDIyLjUgMzY5LjkgNjMuNWM0MC45IDQwLjkgNjMuNSA5NS4zIDYzLjUgMTUzLjJTNDEwLjkgMzI5IDM2OS45IDM2OS45cy05NS4zIDYzLjUtMTUzLjIgNjMuNXptMC0zNzMuNEMxMzAuMyA2MCA2MCAxMzAuMyA2MCAyMTYuN3M3MC4zIDE1Ni43IDE1Ni43IDE1Ni43IDE1Ni43LTcwLjMgMTU2LjctMTU2LjdTMzAzLjEgNjAgMjE2LjcgNjB6Ii8+PHBhdGggZD0iTTQ2Mi40IDQ2My43aDBjLTExLjcgMTEuNy0zMC43IDExLjctNDIuNCAwbC02NC4zLTY0LjNjLTExLjctMTEuNy0xMS43LTMwLjcgMC00Mi40aDBjMTEuNy0xMS43IDMwLjctMTEuNyA0Mi40IDBsNjQuMyA2NC4zYzExLjcgMTEuNyAxMS43IDMwLjcgMCA0Mi40eiIvPjwvc3ZnPg==',
	'framework_title'  => __( 'Woo QuickView Settings', 'woo-quickview' ),
);
SP_WQV_Framework::createOptions( $prefix, $settings );
// ===============================================================================================
// -----------------------------------------------------------------------------------------------
// FRAMEWORK OPTIONS
// -----------------------------------------------------------------------------------------------
// ===============================================================================================
// $options = array();

// Quick View Button Settings.
SP_WQV_Framework::createSection(
	$prefix,
	array(
		'name'   => 'quick_view_btn_settings',
		'title'  => __( 'General', 'woo-quickview' ),
		'icon'   => 'fa fa-wrench',
		// begin: fields.
		'fields' => array(
			array(
				'id'         => 'wqvpro_enable_quick_view',
				'type'       => 'switcher',
				'title'      => __( ' Enable Quick View', 'woo-quickview' ),
				'subtitle'   => __( 'Enable/Disable quick view button.', 'woo-quickview' ),
				'default'    => true,
				'text_on'    => __( 'Enabled', 'woo-quickview' ),
				'text_off'   => __( 'Disabled', 'woo-quickview' ),
				'text_width' => 95,
			),
			array(
				'id'       => 'wqvpro_quick_view_button_position',
				'type'     => 'select',
				'title'    => __( 'Position', 'woo-quickview' ),
				'subtitle' => __( 'Select quick view button position.', 'woo-quickview' ),
				'default'  => 'after_add_to_cart',
				'options'  => array(
					'before_add_to_cart'        => __( 'Before Add to Cart button', 'woo-quickview' ),
					'after_add_to_cart'         => __( 'After Add to Cart button', 'woo-quickview' ),
					'below_product'             => __( 'Below Product', 'woo-quickview' ),
					'above_add_to_cart'         => __( 'Above Add to Cart button(Pro)', 'woo-quickview' ),
					'below_add_to_cart'         => __( 'Below Add to Cart button(Pro)', 'woo-quickview' ),
					'below_product_image'       => __( 'Below Product Image(Pro)', 'woo-quickview' ),
					'above_product_image'       => __( 'Above Product Image(Pro)', 'woo-quickview' ),
					'below_product_title'       => __( 'Below Product Title(Pro)', 'woo-quickview' ),
					'below_product_price'       => __( 'Below Product Price(Pro)', 'woo-quickview' ),
					'over_img_on_hover'         => __( 'Over Product Container on hover(Pro)', 'woo-quickview' ),
					'over_product_img_on_hover' => __( 'Over Product Image on hover(Pro)', 'woo-quickview' ),
					'via_shortcode'             => __( 'Via Shortcode(Pro)', 'woo-quickview' ),
				),
			),
			array(
				'id'       => 'wqvpro_quick_view_button_icon_only',
				'type'     => 'checkbox',
				'title'    => __( 'Hide Quick View Button Label', 'woo-quickview' ),
				'subtitle' => __( 'Check to hide quick view button label.', 'woo-quickview' ),
				'default'  => false,
			),
			array(
				'id'         => 'wqvpro_quick_view_button_text',
				'type'       => 'text',
				'title'      => __( 'Quick View Button Label', 'woo-quickview' ),
				'subtitle'   => __( 'Type quick view button custom label.', 'woo-quickview' ),
				'default'    => 'Quick View',
				'dependency' => array( 'wqvpro_quick_view_button_icon_only', '!=', 'true' ),
			),

			array(
				'id'       => 'wqvpro_quick_view_button_icon_option',
				'type'     => 'button_set',
				'title'    => __( 'Button Icon', 'woo-quickview' ),
				'subtitle' => __( 'Select quick view button icon option.', 'woo-quickview' ),
				'radio'    => true,
				'options'  => array(
					'font_icon'    => array(
						'option_name' => __( 'Icon', 'woo-quickview' ),
					),
					'custom_image' => array(
						'option_name' => __( 'Custom Icon', 'woo-quickview' ),
						'pro_only'    => true,
					),
					'no'           => array(
						'option_name' => __( 'None', 'woo-quickview' ),
					),
				),
				'default'  => 'no',
			),

			array(
				'id'         => 'wqvpro_quick_view_button_font_icon',
				'type'       => 'icon',
				'title'      => 'Select A Icon',
				'subtitle'   => 'Select quick view button icon',
				'text'       => false,
				'default'    => 'sp_wqvp-icon-1',
				'dependency' => array( 'wqvpro_quick_view_button_icon_option', '==', 'font_icon' ),
			),
			array(
				'id'           => 'wqvpro_quick_view_button_custom_image',
				'type'         => 'upload',
				'button_title' => __( 'Upload', 'woo-quickview' ),
				'title'        => __( 'Upload Custom Image', 'woo-quickview' ),
				'subtitle'     => __( 'Upload your custom image as icon. Image format PNG is recommended.', 'woo-quickview' ),
				'dependency'   => array( 'wqvpro_quick_view_button_icon_option', '==', 'custom_image' ),
			),
			array(
				'id'         => 'wqvpro_quick_view_button_icon_position',
				'type'       => 'button_set',
				'title'      => __( 'Icon Position', 'woo-quickview' ),
				'subtitle'   => __( 'Select icon position.', 'woo-quickview' ),
				'dependency' => array( 'wqvpro_quick_view_button_icon_option|wqvpro_quick_view_button_icon_only', '!=|==', 'no|false' ),
				'radio'      => true,
				'options'    => array(
					'icon_on_left'  => __(
						'Icon on the left',
						'woo-quickview'
					),
					'icon_on_right' => __( 'Icon on the right', 'woo-quickview' ),
				),
				'default'    => 'icon_on_left',
			),
			array(
				'id'         => 'wqvpro_font_icon_size',
				'type'       => 'spinner',
				'title'      => __( 'Icon Size', 'woo-quickview' ),
				'subtitle'   => __( 'Set a size for the quick view button icon.', 'woo-quickview' ),
				'default'    => '14',
				'unit'       => 'px',
				'min'        => 0,
				'max'        => 1000,
				'dependency' => array( 'wqvpro_quick_view_button_icon_option', '==', 'font_icon' ),
			),
			array(
				'id'       => 'wqvpro_qv_button_style',
				'type'     => 'button_set',
				'title'    => __( 'Button Style', 'woo-quickview' ),
				'subtitle' => __( 'Select quick view button style.', 'woo-quickview' ),
				'options'  => array(
					'custom_button'    => array(
						'option_name' => __( 'Custom Button', 'woo-quickview' ),
					),
					'button_css_class' => array(
						'option_name' => __( 'Button CSS Class', 'woo-quickview' ),
					),
				),
				'default'  => 'custom_button',
			),
			array(
				'id'         => 'wqvpro_qv_button_css_class',
				'type'       => 'text',
				'title'      => __( 'Add Button CSS Class Name(s)', 'woo-quickview' ),
				'subtitle'   => __( 'Type quick view button class label.', 'woo-quickview' ),
				'default'    => ' ',
				'dependency' => array( 'wqvpro_qv_button_style', '==', 'button_css_class' ),
			),
			array(
				'id'       => 'wqvpro_quick_view_button_color',
				'type'     => 'color_group',
				'title'    => __( 'Button Color', 'woo-quickview' ),
				'subtitle' => __( 'Set quick view button color.', 'woo-quickview' ),
				'options'  => array(
					'color1' => __( 'Color', 'woo-quickview' ),
					'color2' => __( 'Hover Color', 'woo-quickview' ),
					'color3' => __( 'Background', 'woo-quickview' ),
					'color4' => __( 'Hover Background', 'woo-quickview' ),
				),
				'default'  => array(
					'color1' => '#ffffff',
					'color2' => '#ffffff',
					'color3' => '#1A79BF',
					'color4' => '#176AA6',
				),
			),
			array(
				'id'       => 'wqvpro_add_button_border',
				'type'     => 'checkbox',
				'title'    => __( 'Add Button Border', 'woo-quickview' ),
				'subtitle' => __( 'Check to add border for the button.', 'woo-quickview' ),
				'default'  => false,
			),
			array(
				'id'          => 'wqvpro_quick_view_button_border',
				'type'        => 'border',
				'title'       => __( 'Button Border', 'woo-quickview' ),
				'subtitle'    => __( 'Set quick view button border.', 'woo-quickview' ),
				'option'      => array(
					'all'         => __( 'Width', 'woo-quickview' ),
					'style'       => __( 'Style', 'woo-quickview' ),
					'color'       => __( 'Color', 'woo-quickview' ),
					'hover_color' => __( 'Hover Color', 'woo-quickview' ),
				),
				'default'     => array(
					'all'         => '0',
					'style'       => 'solid',
					'color'       => '#1A79BF',
					'hover_color' => '#176AA6',
				),
				'hover_color' => true,
				'all'         => true,
				'dependency'  => array( 'wqvpro_add_button_border', '==', 'true' ),
			),
			array(
				'id'       => 'wqvpro_add_button_padding',
				'type'     => 'checkbox',
				'title'    => __( 'Add Button Padding', 'woo-quickview' ),
				'subtitle' => __( 'Check to add padding for the button.', 'woo-quickview' ),
				'default'  => false,
			),
			array(
				'id'         => 'wqvpro_quick_view_button_padding',
				'type'       => 'spacing',
				'title'      => __( 'Button Padding', 'woo-quickview' ),
				'subtitle'   => __( 'Set quick view button padding.', 'woo-quickview' ),
				'bottom'     => false,
				'right'      => false,
				'default'    => array(
					'top'  => '9',
					'left' => '16',
				),
				'units'      => array( 'px' ),
				'dependency' => array( 'wqvpro_add_button_padding', '==', 'true' ),
			),
			array(
				'id'       => 'wqvpro_qv_button_transition',
				'type'     => 'slider',
				'title'    => __( 'Button Transition', 'woo-quickview' ),
				'subtitle' => __( 'Set modal button transition all .5s ease.', 'woo-quickview' ),
				'step'     => .1,
				'min'      => 0,
				'max'      => 2,
				'unit'     => 's',
				'default'  => .5,
			),

			array(
				'id'         => 'wqvpro_qv_product_name_or_image',
				'type'       => 'switcher',
				'class'      => 'only_pro',
				'title'      => __( 'Quick View on Click Product Name or Image', 'woo-quickview' ),
				'subtitle'   => __( 'Enable/Disable to view the modal on click name or image.', 'woo-quickview' ),
				'text_on'    => __( 'Enabled', 'woo-quickview' ),
				'text_off'   => __( 'Disabled', 'woo-quickview' ),
				'text_width' => 95,
				'default'    => false,
			),


		), // end: fields.
	)
);

// ----------------------------------------
// Modal Settings -
// ----------------------------------------
SP_WQV_Framework::createSection(
	$prefix,
	array(
		'name'   => 'popup_settings',
		'title'  => __( 'Modal', 'woo-quickview' ),
		'icon'   => 'fas fa-external-link-alt',
		// begin: fields.
		'fields' => array(
			array(
				'id'         => 'wqv_quick_view_type',
				'class'      => 'wqvpro-layout-options',
				'type'       => 'image_select',
				'title'      => __( 'Quick View Type', 'woo-quickview' ),
				'subtitle'   => __( 'Select quick view layout type.', 'woo-quickview' ),
				'radio'      => true,
				'options'    => array(
					'modal_window'     => array(
						'image'       => SP_WQV_URL . 'admin/views/img/Modal-Window.svg',
						'option_name' => __( 'Modal Window', 'woo-quickview' ),
					),
					'slide_ins_right'  => array(
						'image'       => SP_WQV_URL . 'admin/views/img/Slide-Ins-Right.svg',
						'option_name' => __( 'Slide-Ins Right', 'woo-quickview' ),
						'pro_only'    => true,
					),
					'slide_ins_center' => array(
						'image'       => SP_WQV_URL . 'admin/views/img/Slide-Ins-Center.svg',
						'option_name' => __( 'Slide-Ins-Center', 'woo-quickview' ),
						'pro_only'    => true,
					),
					'slide_ins_left'   => array(
						'image'       => SP_WQV_URL . 'admin/views/img/Slide-Ins-Left.svg',
						'option_name' => __( 'Slide-Ins Left', 'woo-quickview' ),
						'pro_only'    => true,
					),
					'cascading'        => array(
						'image'       => SP_WQV_URL . 'admin/views/img/Cascading.svg',
						'option_name' => __( 'Cascading', 'woo-quickview' ),
						'pro_only'    => true,
					),
				),
				'default'    => 'modal_window',
				'attributes' => array(
					'data-depend-id' => 'wqvpro_quick_view_type',
				),
			),
			array(
				'id'                => 'wqvpro_modal_size',
				'type'              => 'dimensions_advanced',
				'title'             => __( 'Modal Size', 'woo-quickview' ),
				'subtitle'          => __( 'Set maximum width and height of the modal.', 'woo-quickview' ),
				'chosen'            => true,
				'bottom'            => false,
				'left'              => false,
				'color'             => false,
				'style'             => false,
				'top_icon'          => '<i class="fa fa-arrows-h"></i>',
				'right_icon'        => '<i class="fa fa-arrows-v"></i>',
				'top_placeholder'   => __( 'width', 'woo-quickview' ),
				'right_placeholder' => __( 'height', 'woo-quickview' ),
				'default'           => array(
					'top'   => '876',
					'right' => '438',
					'unit'  => 'px',
				),
				'dependency'        => array( 'wqvpro_quick_view_type', '==', 'modal_window' ),
			),

			array(
				'id'       => 'wqvpro_popup_box_bg',
				'type'     => 'color',
				'title'    => __( 'Modal Background', 'woo-quickview' ),
				'subtitle' => __( 'Set color for the modal background.', 'woo-quickview' ),
				'default'  => '#ffffff',
			),
			array(
				'id'       => 'wqvpro_content_padding',
				'type'     => 'spacing',
				'title'    => __( 'Content Padding', 'woo-quickview' ),
				'subtitle' => __( 'Set padding for modal content.', 'woo-quickview' ),
				'left'     => false,
				'top'      => false,
				'right'    => false,
				'bottom'   => true,
				'default'  => array(
					'bottom' => '20',
				),
				'units'    => array( 'px' ),
			),
			array(
				'id'       => 'wqvpro_popup_bg',
				'type'     => 'color',
				'title'    => __( 'Modal Overlay Background', 'woo-quickview' ),
				'subtitle' => __( 'Set modal overlay background color.', 'woo-quickview' ),
				'default'  => 'rgba( 0, 0, 0, 0.8)',
			),

			array(
				'id'       => 'wqvpro_popup_effect',
				'type'     => 'select',
				'title'    => __( 'Modal Effect', 'woo-quickview' ),
				'subtitle' => __( 'Select modal effect.', 'woo-quickview' ),
				'default'  => 'mfp-move-from-top',
				'options'  => array(
					'mfp-move-from-top'    => __( 'Move from Top', 'woo-quickview' ),
					'mfp-fade'             => __( 'Fade', 'woo-quickview' ),
					'mfp-zoom-in'          => __( 'Zoom in', 'woo-quickview' ),
					'mfp-zoom-out'         => __( 'Zoom Out', 'woo-quickview' ),
					'mfp-newspaper'        => __( 'Newspaper', 'woo-quickview' ),
					'mfp-move-horizontal'  => __( 'Move Horizontal', 'woo-quickview' ),
					'mfp-3d-unfold'        => __( '3d Unfold', 'woo-quickview' ),
					'mfp-slide-bottom'     => __( 'Slide Bottom', 'woo-quickview' ),
					'qvpro-slide-in-left'  => __( 'Slide Left(Pro)', 'woo-quickview' ),
					'qvpro-slide-in-right' => __( 'Slide Right(Pro)', 'woo-quickview' ),
				),
			),
			array(
				'id'       => 'wqvpro_quick_view_categories',
				'type'     => 'select',
				'title'    => __( 'Quick View Categories', 'woo-quickview' ),
				'subtitle' => __( 'Show the Quick View button only for products in selected categories.', 'woo-quickview' ),
				'options'  => array(
					'all'      => __( 'All', 'woo-quickview' ),
					'specific' => __( 'Specific(Pro)', 'woo-quickview' ),
				),
				'default'  => 'all',
			),
			array(
				'id'      => 'wqvpro_modal_z_index',
				'type'    => 'slider',
				'class'   => 'wqvpro_modal_z_index_style',
				'title'   => __( 'Modal Z-Index', 'woo-quickview' ),
				'step'    => 1,
				'min'     => 0,
				'max'     => 9999999,
				'default' => 999999,
			),
			array(
				'id'      => 'wqvpro_qv_subheading',
				'type'    => 'subheading',
				'content' => __( 'Product Content To Show', 'woo-quickview' ),
			),
			array(
				'id'       => 'wqvpro_show_product_content',
				'type'     => 'sortable',
				'title'    => __( 'Product Content or Information', 'woo-quickview' ),
				'subtitle' => __( 'Show/Hide the fields you want to show on the modal.', 'woo-quickview' ),
				'class'    => 'style_generator_sortable',
				'default'  => array(
					'title'        => true,
					'rating'       => true,
					'price'        => true,
					'excerpt'      => true,
					'add_to_cart'  => true,
					'meta'         => true,
					'social_share' => false,
				),
				'fields'   => array(
					array(
						'id'         => 'title',
						'type'       => 'switcher',
						'title'      => __( 'Title', 'woo-quickview' ),
						'text_on'    => __( 'Show', 'woo-quickview' ),
						'text_off'   => __( 'Hide', 'woo-quickview' ),
						'text_width' => 75,
					),
					array(
						'id'         => 'rating',
						'type'       => 'switcher',
						'title'      => __( 'Rating', 'woo-quickview' ),
						'text_on'    => __( 'Show', 'woo-quickview' ),
						'text_off'   => __( 'Hide', 'woo-quickview' ),
						'text_width' => 75,
					),
					array(
						'id'         => 'price',
						'type'       => 'switcher',
						'title'      => __( 'Price', 'woo-quickview' ),
						'text_on'    => __( 'Show', 'woo-quickview' ),
						'text_off'   => __( 'Hide', 'woo-quickview' ),
						'text_width' => 75,
					),
					array(
						'id'         => 'excerpt',
						'type'       => 'switcher',
						'title'      => __( 'Excerpt', 'woo-quickview' ),
						'text_on'    => __( 'Show', 'woo-quickview' ),
						'text_off'   => __( 'Hide', 'woo-quickview' ),
						'text_width' => 75,
					),
					array(
						'id'         => 'add_to_cart',
						'type'       => 'switcher',
						'title'      => __( 'Add To Cart', 'woo-quickview' ),
						'text_on'    => __( 'Show', 'woo-quickview' ),
						'text_off'   => __( 'Hide', 'woo-quickview' ),
						'text_width' => 75,
					),
					array(
						'id'         => 'meta',
						'type'       => 'switcher',
						'title'      => __( 'Meta', 'woo-quickview' ),
						'text_on'    => __( 'Show', 'woo-quickview' ),
						'text_off'   => __( 'Hide', 'woo-quickview' ),
						'text_width' => 75,
					),
					array(
						'id'         => 'social_share',
						'type'       => 'switcher',
						'class'      => 'only_pro',
						'title'      => __( 'Social Share', 'woo-quickview' ),
						'text_on'    => __( 'Show', 'woo-quickview' ),
						'text_off'   => __( 'Hide', 'woo-quickview' ),
						'text_width' => 75,
					),
				),
				'desc'     => sprintf(
					/* translators: 1: start link and strong tag, 2: close tag. */
					__( 'To unlock the social share and drag & drop sorting ability, %1$sUpgrade To Pro!%2$s', 'woo-quickview' ),
					'<a href="https://shapedplugin.com/quick-view-for-woocommerce/?ref=1" target="_blank"><strong>',
					'</strong></a>'
				),
			),

			array(
				'id'                => 'wqvpro_product_crop_size',
				'type'              => 'dimensions_advanced',
				'title'             => __( 'Product Custom Image Size', 'woo-quickview' ),
				'subtitle'          => __( 'Set a custom product image size for the modal.', 'woo-quickview' ),
				'chosen'            => true,
				'bottom'            => false,
				'left'              => false,
				'color'             => false,
				'class'             => 'wqv_product_custom_size only_pro',
				'top_icon'          => '<i class="fa fa-arrows-h"></i>',
				'right_icon'        => '<i class="fa fa-arrows-v"></i>',
				'top_placeholder'   => 'width',
				'right_placeholder' => 'height',
				'styles'            => array(
					'Soft-crop',
					'Hard-crop',
				),
				'default'           => array(
					'width'  => '',
					'height' => '',
					'style'  => 'Soft-crop',
					'unit'   => 'px',
				),
			),

			array(
				'id'      => 'wqvpro_qv_subheading',
				'type'    => 'subheading',
				'content' => __( 'Add to Cart and View Details Button', 'woo-quickview' ),
			),
			array(
				'id'         => 'wqvpro_aj_add_to_cart',
				'type'       => 'switcher',
				'title'      => __( ' Enable Ajax Add to Cart', 'woo-quickview' ),
				'subtitle'   => __( 'Enable/Disable ajax add to cart on the modal.', 'woo-quickview' ),
				'default'    => true,
				'text_on'    => __( 'Enabled', 'woo-quickview' ),
				'text_off'   => __( 'Disabled', 'woo-quickview' ),
				'text_width' => 95,
			),

			array(
				'id'       => 'wqvpro_add_to_cart_btn_bg',
				'type'     => 'color_group',
				'title'    => __( 'Add to Cart Button Color', 'woo-quickview' ),
				'subtitle' => __( 'Set add to cart button color.', 'woo-quickview' ),
				'default'  => array(
					'color1' => '#ffffff',
					'color2' => '#ffffff',
					'color3' => '#333333',
					'color4' => '#1a1a1a',
				),
				'options'  => array(
					'color1' => __( 'Color', 'woo-quickview' ),
					'color2' => __( 'Hover Color', 'woo-quickview' ),
					'color3' => __( 'Background', 'woo-quickview' ),
					'color4' => __( 'Hover Background', 'woo-quickview' ),
				),
			),
			array(
				'id'         => 'wqvpro_view_details_button',
				'type'       => 'switcher',
				'class'      => 'only_pro',
				'title'      => __( ' Add View Details Button', 'woo-quickview' ),
				'subtitle'   => __( 'Show/Hide view details button.', 'woo-quickview' ),
				'default'    => false,
				'text_on'    => __( 'Show', 'woo-quickview' ),
				'text_off'   => __( 'Hide', 'woo-quickview' ),
				'text_width' => 75,
			),
			array(
				'id'       => 'wqvpro_add_to_cart_btn_padding',
				'type'     => 'spacing',
				'title'    => __( 'Button Padding', 'woo-quickview' ),
				'subtitle' => __( 'Set add to cart and view details button padding.', 'woo-quickview' ),
				'bottom'   => false,
				'right'    => false,
				'default'  => array(
					'left' => '16',
					'top'  => '0',
				),
				'units'    => array( 'px' ),
			),

			array(
				'id'         => 'wqvpro_redirect_after_cart',
				'type'       => 'button_set',
				'title'      => __( 'Redirect After Add to Cart', 'woo-quickview' ),
				'subtitle'   => __( 'Select a page to redirect after add to cart.', 'woo-quickview' ),
				'options'    => array(
					'same_page'     => array(
						'option_name' => __( 'Same', 'woo-quickview' ),
					),
					'single_page'   => array(
						'option_name' => __( 'Single', 'woo-quickview' ),
					),
					'checkout_page' => array(
						'option_name' => __( 'Checkout', 'woo-quickview' ),
						'pro_only'    => true,
					),
				),
				'default'    => 'same_page',
				'dependency' => array( 'wqvpro_aj_add_to_cart', '!=', 'true' ),
			),
			array(
				'id'         => 'wqvpro_rating_start_color',
				'type'       => 'color_group',
				'title'      => __( 'Rating Color', 'woo-quickview' ),
				'subtitle'   => __( 'Set product star rating color.', 'woo-quickview' ),
				'default'    => array(
					'color1' => '#dadada',
					'color2' => '#ff9800',
				),
				'options'    => array(
					'color1' => __( 'Empty Color', 'woo-quickview' ),
					'color2' => __( 'Full Color', 'woo-quickview' ),
				),
				'dependency' => array( 'rating', '==', 'true' ),
			),
			array(
				'id'         => 'wqvpro_enable_on_wishlist',
				'type'       => 'switcher',
				'class'      => 'only_pro',
				'title'      => __( 'Enable Quick View on Wishlist', 'woo-quickview' ),
				'subtitle'   => __( 'Enable/Disable quick view on wishlist.', 'woo-quickview' ),
				'default'    => false,
				'text_on'    => __( 'Enabled', 'woo-quickview' ),
				'text_off'   => __( 'Disabled ', 'woo-quickview' ),
				'text_width' => 95,
			),
			array(
				'id'      => 'wqvpro_qv_subheading',
				'type'    => 'subheading',
				'content' => __( 'Modal Thumbnails Gallery Slider', 'woo-quickview' ),
			),
			array(
				'id'         => 'wqvpro_qv_thumbnails_type',
				'type'       => 'button_set',
				'title'      => __( 'Thumbnails Type', 'woo-quickview' ),
				'subtitle'   => __( 'Select thumbnails type.', 'woo-quickview' ),
				'radio'      => true,
				'options'    => array(
					'slider_mode'  => array(
						'option_name' => __( 'Slider Mode', 'woo-quickview' ),
						'pro_only'    => true,
					),
					'classic_mode' => array(
						'option_name' => __( 'Classic Mode', 'woo-quickview' ),
						'pro_only'    => true,
					),
					'not_show'     => array(
						'option_name' => __( 'Do not Show', 'woo-quickview' ),
					),
				),
				'attributes' => array(
					'data-depend-id' => 'wqvpro_qv_thumbnails_type',
				),
				'default'    => 'not_show',
			),
			array(
				'id'         => 'wqvpro_zoom_gallery_image',
				'type'       => 'switcher',
				'class'      => 'only_pro',
				'title'      => __( 'Enable Zoom for Gallery Image ', 'woo-quickview' ),
				'subtitle'   => __( 'Enable/Disable zoom for gallery image mouseover.', 'woo-quickview' ),
				'text_on'    => __( 'Enabled', 'woo-quickview' ),
				'text_off'   => __( 'Disabled', 'woo-quickview' ),
				'text_width' => 95,
				'default'    => false,
			),
			array(
				'id'         => 'wqvpro_product_image_lightbox',
				'type'       => 'switcher',
				'title'      => __( 'Enable Lightbox ', 'woo-quickview' ),
				'subtitle'   => __( 'Enable/Disable lightbox on click image.', 'woo-quickview' ),
				'default'    => false,
				'text_on'    => __( 'Enabled', 'woo-quickview' ),
				'text_off'   => __( 'Disabled', 'woo-quickview' ),
				'text_width' => 95,
			),
			array(
				'id'         => 'wqvpro_qv_image_title',
				'type'       => 'switcher',
				'title'      => __( ' Image Title ', 'woo-quickview' ),
				'subtitle'   => __( 'Show/Hide image title.', 'woo-quickview' ),
				'default'    => false,
				'text_on'    => __( 'Show', 'woo-quickview' ),
				'text_off'   => __( 'Hide', 'woo-quickview' ),
				'text_width' => 75,
				'dependency' => array( 'wqvpro_product_image_lightbox', '==', true ),
			),
			array(
				'id'      => 'wqvpro_qv_subheading',
				'type'    => 'subheading',
				'content' => __( 'Modal Close Button', 'woo-quickview' ),
			),
			array(
				'id'         => 'wqvpro_popup_close_button',
				'type'       => 'switcher',
				'title'      => __( 'Close Button Icon', 'woo-quickview' ),
				'subtitle'   => __( 'Show/hide modalclose button.', 'woo-quickview' ),
				'default'    => true,
				'text_on'    => __( 'Show', 'woo-quickview' ),
				'text_off'   => __( 'Hide ', 'woo-quickview' ),
				'text_width' => 75,
			),
			array(
				'id'         => 'wqvpro_close_button_position',
				'type'       => 'select',
				'title'      => __( 'Close Button Position', 'woo-quickview' ),
				'subtitle'   => __( 'Select a position for close button.', 'woo-quickview' ),
				'options'    => array(
					'inside'           => __( 'Inside Corner of Modal', 'woo-quickview' ),
					'outside'          => __( 'Outside Corner of Modal (Pro)', 'woo-quickview' ),
					'top_right_corner' => __( 'Top Right Corner of Web Browser (Pro)', 'woo-quickview' ),
				),
				'default'    => 'inside',
				'dependency' => array( 'wqvpro_popup_close_button|wqvpro_quick_view_type', '==|!=', 'true|cascading' ),
			),
			array(
				'id'         => 'wqvpro_popup_close_color',
				'type'       => 'color_group',
				'title'      => __( 'Icon Color', 'woo-quickview' ),
				'subtitle'   => __( 'Set modal close button icon color.', 'woo-quickview' ),
				'default'    => array(
					'color1' => '#9a9a9a',
					'color2' => '#ffffff',
				),
				'options'    => array(
					'color1' => __( 'Color', 'woo-quickview' ),
					'color2' => __( 'Hover Color', 'woo-quickview' ),
				),
				'dependency' => array( 'wqvpro_popup_close_button', '==', 'true' ),
			),
			array(
				'id'         => 'wqvpro_popup_close_size',
				'type'       => 'spinner',
				'title'      => __( 'Icon Size', 'woo-quickview' ),
				'subtitle'   => __( 'Set modal close button icon size.', 'woo-quickview' ),
				'default'    => '18',
				'unit'       => 'px',
				'min'        => 0,
				'max'        => 100,
				'dependency' => array( 'wqvpro_popup_close_button', '==', 'true' ),
			),

			array(
				'type'    => 'subheading',
				'content' => __( 'Preloader', 'woo-quickview' ),
			),
			array(
				'id'         => 'wqvpro_qv_preloader',
				'type'       => 'switcher',
				'title'      => __( ' Preloader', 'woo-quickview' ),
				'subtitle'   => __( 'Enable/Disable preloader on the modal.', 'woo-quickview' ),
				'default'    => true,
				'text_on'    => __( 'Enabled', 'woo-quickview' ),
				'text_off'   => __( 'Disabled', 'woo-quickview' ),
				'text_width' => 95,
			),
			array(
				'id'         => 'wqvpro_qv_preloader_type',
				'type'       => 'button_set',
				'title'      => __( 'Preloader Type', 'woo-quickview' ),
				'subtitle'   => __( 'Set a preloader type.', 'woo-quickview' ),
				'radio'      => true,
				'options'    => array(
					'preloader_image' => array(
						'option_name' => __( 'Preloader Icon', 'woo-quickview' ),
						'pro_only'    => true,
					),
					'loading_label'   => array(
						'option_name' => __( 'Loading Label', 'woo-quickview' ),
					),
				),
				'default'    => 'loading_label',
				'dependency' => array( 'wqvpro_qv_preloader', '==', 'true' ),
			),
			array(
				'id'         => 'wqvpro_loading_label',
				'type'       => 'text',
				'title'      => __( 'Loading Label', 'woo-quickview' ),
				'subtitle'   => __( 'Type loading label.', 'woo-quickview' ),
				'default'    => __( 'Loading...', 'woo-quickview' ),
				'dependency' => array( 'wqvpro_qv_preloader|wqvpro_qv_preloader_type', '==|==', 'true|loading_label' ),
			),
			array(
				'id'         => 'wqvpro_loading_color',
				'type'       => 'color',
				'title'      => __( 'Color', 'woo-quickview' ),
				'subtitle'   => __( 'Set loading label color.', 'woo-quickview' ),
				'default'    => '#ffffff',
				'dependency' => array( 'wqvpro_qv_preloader|wqvpro_qv_preloader_type', '==|==', 'true|loading_label' ),
			),
		),
	)
);

// ------------------------------
// Other Options                -
// ------------------------------
SP_WQV_Framework::createSection(
	$prefix,
	array(
		'name'   => 'other_options_section',
		'title'  => __( 'Advanced', 'woo-quickview' ),
		'icon'   => 'fa fa-cogs',
		'fields' => array(
			array(
				'id'         => 'wqvpro_data_remove',
				'type'       => 'checkbox',
				'title'      => esc_html__( 'Clean-up Data on Deletion', 'woo-quickview' ),
				'help_title' => esc_html__( 'Check this box if you would like Quick View for WooCommerce plugin to completely remove all of its data when the plugin is deleted.', 'woo-quickview' ),
			),
			array(
				'id'         => 'wqvpro_add_iphone_android',
				'type'       => 'switcher',
				'title'      => __( 'Quick View Button on Mobile Devices', 'woo-quickview' ),
				'help_title' => __( 'Show/Hide quick view button on iphone and android devices.', 'woo-quickview' ),
				'text_on'    => __( 'Show', 'woo-quickview' ),
				'text_off'   => __( 'Hide', 'woo-quickview' ),
				'text_width' => 75,
				'default'    => false,
			),
			array(
				'id'         => 'wqvpro_add_ipad',
				'type'       => 'switcher',
				'title'      => __( ' Quick View Button on iPad', 'woo-quickview' ),
				'help_title' => __( 'Show/Hide quick view button on ipad.', 'woo-quickview' ),
				'text_on'    => __( 'Show', 'woo-quickview' ),
				'text_off'   => __( 'Hide', 'woo-quickview' ),
				'text_width' => 75,
				'default'    => false,
			),
			array(
				'id'       => 'wqvpro_custom_css',
				'type'     => 'code_editor',
				'title'    => __( 'Custom CSS', 'woo-quickview' ),
				'settings' => array(
					'theme' => 'default',
					'mode'  => 'css',
				),
			),
			array(
				'id'       => 'wqvpro_custom_js',
				'type'     => 'code_editor',
				'title'    => __( 'Custom JS', 'woo-quickview' ),
				'settings' => array(
					'theme' => 'default',
					'mode'  => 'javascript',
				),
			),
		),
	)
);

// ----------------------------------------
// Help Settings                    -
// ----------------------------------------
SP_WQV_Framework::createSection(
	$prefix,
	array(
		'name'   => 'help',
		'title'  => __( 'Get Help', 'woo-quickview' ),
		'icon'   => 'fa fa-life-ring',
		'fields' => array(
			array(
				'id'   => 'help',
				'type' => 'wqv_help',
			),
		),
	)
);
