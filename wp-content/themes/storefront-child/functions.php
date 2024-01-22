<?php

/*
Author: Hamroz Gavharov
*/

add_action( 'wp_enqueue_scripts', 'my_child_theme_enqueue_styles' );
function my_child_theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));
}

// Add a new custom product data panel
add_action('woocommerce_product_data_panels', 'add_custom_product_data_panel');
function add_custom_product_data_panel() {
    global $post;

    echo '<div id="custom_product_data" class="panel woocommerce_options_panel hidden">';
    woocommerce_product_custom_fields();
    
    // Apply inline styles for buttons for spacing and alignment
    echo '<div style="margin-top: 15px; margin-left: 15px">';
    echo '<button type="button" class="button button-secondary" id="reset_custom_fields" style="margin-right: 10px;">' . __('Reset Custom Fields', 'woocommerce') . '</button>';
    $button_text = $post->ID ? __('Update', 'woocommerce') : __('Submit', 'woocommerce');
    echo '<button type="button" class="button button-primary" id="custom_submit_update">' . $button_text . '</button>';
    echo '</div>';

    echo '</div>';
}

// Add a new tab to the product data metabox
add_filter('woocommerce_product_data_tabs', 'reorder_product_data_tabs', 10, 1);
function reorder_product_data_tabs($tabs) {
    // Adding the custom tab as the first tab
    $new_tabs = array(
        'custom_product_data_tab' => array(
            'label'  => __('Custom Fields', 'woocommerce'),
            'target' => 'custom_product_data',
            'class'  => array(),
        )
    );

    // Merge with existing tabs
    return array_merge($new_tabs, $tabs);
}

function woocommerce_product_custom_fields() {
    global $post;

    echo '<div class="product_custom_field">';
    
    // Custom Field: Product Image
    $product = wc_get_product($post->ID);
    $image_id = $product->get_image_id();
    $image_url = wp_get_attachment_url($image_id);
    
    if ($image_url) {
        echo '<p class="form-field _custom_product_image_field"><label>' . __('Product Image:', 'woocommerce') . '</label>';
        echo '<img src="' . esc_url($image_url) . '" alt="' . __('Product Image', 'woocommerce') . '" style="max-width:100px;max-height:100px; display: inline-block; vertical-align: middle;" />';
        echo '<button type="button" class="button remove_custom_image_button" style="margin-left: 20px; vertical-align: middle;">' . __('Remove image', 'woocommerce') . '</button>';
        echo '</p>';
    } else {
        echo '<p class="form-field _custom_product_image_field"><label>' . __('Product Image:', 'woocommerce') . '</label>';
        echo '<button type="button" class="button remove_custom_image_button" style="margin:30px;">' . __('Remove image', 'woocommerce') . '</button>';
        echo '</p>';
    }
    echo '<input type="hidden" id="_custom_product_image_removed" name="_custom_product_image_removed" value="0" />';


    // Custom Field: Product Creation Date
    $creation_date = get_the_date('Y-m-d', $post->ID);
    woocommerce_wp_text_input(
        array(
            'id' => '_custom_product_date',
            'label' => __('Creation Date', 'woocommerce'),
            'value' => $creation_date,
            'custom_attributes' => array('readonly' => 'readonly'),
            'type' => 'date',
        )
    );

    // Custom Field: Product Frequency Dropdown
    woocommerce_wp_select(
    array(
        'id' => '_custom_product_dropdown',
        'label' => __('Product Frequency', 'woocommerce'),
        'options' => array(
            'none' => __('None', 'woocommerce'),
            'rare' => __('Rare', 'woocommerce'),
            'frequent' => __('Frequent', 'woocommerce'),
            'unusual' => __('Unusual', 'woocommerce')
        ),
        'description' => __('Select product frequency.', 'woocommerce'),
        'desc_tip' => true,
    )
);

    echo '</div>';
}

// Enqueue JavaScript
add_action('admin_footer', 'custom_admin_product_js');
function custom_admin_product_js() {
    echo '<script type="text/javascript">
            jQuery(document).ready(function($) {
                $(".remove_custom_image_button").click(function() {
                    $(".remove_custom_image_button").prev("img").remove();
                    $("#_custom_product_image_removed").val("1");
                });
            });
          </script>';
          
    // JavaScript for custom Submit/Update button
    echo '<script type="text/javascript">
            jQuery(document).ready(function($) {
                // Trigger original Publish/Update button click
                $("#custom_submit_update").click(function() {
                    $("#publish").click();
                });
            });
          </script>';
}

// Enqueue JavaScript for Reset Button
add_action('admin_footer', 'custom_admin_product_js_for_reset');
function custom_admin_product_js_for_reset() {
    echo '<script type="text/javascript">
            jQuery(document).ready(function($) {
                $("#reset_custom_fields").click(function() {
                    $("#_custom_product_dropdown").val("none").trigger("change");

                    // Manually trigger the WooCommerce change event to ensure changes are saved
                    $("#woocommerce-product-data").trigger("woocommerce_variations_loaded");
                });
            });
          </script>';
}


// Function to display custom fields in the frontend
function display_custom_product_fields() {
    global $product;

    // Get the custom fields values
    $product_id = $product->get_id();
    $creation_date = get_the_date('Y-m-d', $product_id);
    $product_frequency = get_post_meta($product_id, '_custom_product_dropdown', true);

    // Display the custom fields
    if (!empty($creation_date)) {
        echo '<p><strong>' . __('Creation Date:', 'woocommerce') . '</strong> ' . esc_html($creation_date) . '</p>';
    }
    if (!empty($product_frequency)) {
        echo '<p><strong>' . __('Product Frequency:', 'woocommerce') . '</strong> ' . esc_html($product_frequency) . '</p>';
    }
}

add_action('woocommerce_single_product_summary', 'display_custom_product_fields', 20);


// Save Custom Fields
add_action('woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save');
function woocommerce_product_custom_fields_save($post_id) {
    
    if (isset($_POST['_custom_product_image_removed']) && $_POST['_custom_product_image_removed'] == '1') {
        update_post_meta($post_id, '_thumbnail_id', '');
    }

    
    // Save Dropdown Selection
    $woocommerce_custom_product_dropdown = $_POST['_custom_product_dropdown'];
    if ($woocommerce_custom_product_dropdown === 'none') {
        // Handle the 'None' selection
        delete_post_meta($post_id, '_custom_product_dropdown');
    } else {
        // Save the selected option
        update_post_meta($post_id, '_custom_product_dropdown', esc_attr($woocommerce_custom_product_dropdown));
    }
        
        
}


?>
