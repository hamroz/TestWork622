<?php

/*
Template Name: Create Product
Author: Hamroz Gavharov
*/

get_header();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['action']) && $_POST['action'] == 'create_product') {
    // Security check - Nonce Field
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'create_product_nonce')) {
        echo 'Sorry, your nonce did not verify.';
        exit;
    }
    
    // Get Product Frequency value
    $product_frequency = isset($_POST['product_frequency']) ? sanitize_text_field($_POST['product_frequency']) : 'none';


    // Validate and sanitize input data
    $product_name = sanitize_text_field($_POST['product_name']);
    $product_description = sanitize_textarea_field($_POST['product_description']);
    $product_price = isset($_POST['product_price']) ? floatval($_POST['product_price']) : 0;

    // Create product
    $new_product = new WC_Product_Simple();
    $new_product->set_name($product_name);
    $new_product->set_description($product_description);
    $new_product->set_regular_price($product_price);
    $new_product->set_status('publish'); // or 'draft'

    // Handle product image if set
    if (isset($_FILES['product_image']) && !empty($_FILES['product_image']['name'])) {
        $upload = wp_upload_bits($_FILES['product_image']['name'], null, file_get_contents($_FILES['product_image']['tmp_name']));
        if (!$upload['error']) {
            $wp_filetype = wp_check_filetype($upload['file'], null);
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($upload['file']),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attachment_id = wp_insert_attachment($attachment, $upload['file']);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            $new_product->set_image_id($attachment_id);
        }
    }
    
     // Save the Product Frequency as custom meta
    if ($product_id) {
        update_post_meta($product_id, '_custom_product_dropdown', $product_frequency);
    }


    $product_id = $new_product->save();
    echo '<p>Product created successfully! Product ID: ' . $product_id . '</p>';
}
?>

<form action="" method="post" enctype="multipart/form-data">
    <?php wp_nonce_field('create_product_nonce'); ?>
    <p>
        <label for="product_name">Product Name</label>
        <input type="text" id="product_name" name="product_name" required>
    </p>
    <p>
        <label for="product_description">Product Description</label>
        <textarea id="product_description" name="product_description"></textarea>
    </p>
    <p>
        <label for="product_price">Product Price</label>
        <input type="number" step="0.01" id="product_price" name="product_price">
    </p>
    <p>
        <label for="product_image">Product Image</label>
        <input type="file" id="product_image" name="product_image">
    </p>
    
     <p>
        <label for="product_frequency">Product Frequency</label>
        <select id="product_frequency" name="product_frequency">
            <option value="none"><?php echo __('None', 'woocommerce'); ?></option>
            <option value="rare"><?php echo __('Rare', 'woocommerce'); ?></option>
            <option value="frequent"><?php echo __('Frequent', 'woocommerce'); ?></option>
            <option value="unusual"><?php echo __('Unusual', 'woocommerce'); ?></option>
        </select>
    </p>
    <input type="hidden" name="action" value="create_product">
    <input type="submit" value="Create Product">
</form>

<?php
get_footer();
?>
