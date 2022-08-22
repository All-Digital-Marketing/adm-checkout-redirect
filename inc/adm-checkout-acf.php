<?php
add_action('acf/init', 'adm_acf_add_local_field_groups');
function adm_acf_add_local_field_groups() {
  
  if (function_exists('acf_add_local_field_group')):
    
    acf_add_local_field_group(array(
      'key'                   => 'group_62fef2df64fa9',
      'title'                 => 'Product category fields',
      'fields'                => array(
        array(
          'key'               => 'field_62fef3076a1ae',
          'label'             => 'Order received redirect',
          'name'              => 'custom_thank_you_page',
          'type'              => 'page_link',
          'instructions'      => 'Custom "thank you" / "order received" page. This page must already exist and be publicly accessible.',
          'required'          => 0,
          'conditional_logic' => 0,
          'wrapper'           => array(
            'width' => '',
            'class' => '',
            'id'    => '',
          ),
          'post_type'         => '',
          'taxonomy'          => '',
          'allow_null'        => 1,
          'allow_archives'    => 0,
          'multiple'          => 0,
        ),
      ),
      'location'              => array(
        array(
          array(
            'param'    => 'taxonomy',
            'operator' => '==',
            'value'    => 'product_cat',
          ),
        ),
      ),
      'menu_order'            => 0,
      'position'              => 'normal',
      'style'                 => 'default',
      'label_placement'       => 'top',
      'instruction_placement' => 'label',
      'hide_on_screen'        => '',
      'active'                => true,
      'description'           => '',
      'show_in_rest'          => 0,
    ));
  
  endif;
}