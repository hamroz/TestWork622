<?php

/*
Template Name: All Products
Author: Hamroz Gavharov
*/

get_header(); ?>

<style>
    /* Custom styles for the All Products page */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        grid-gap: 20px;
        padding: 20px;
    }

    .products-grid .product {
        border: 1px solid #eee;
        padding: 15px;
        text-align: center;
        transition: transform 0.3s ease;
        list-style-type: none; /* Ensure no bullets */
    }

    .products-grid .product:hover {
        transform: translateY(-5px);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .products-grid img {
        max-width: 100%;
        height: auto;
        margin-bottom: 10px;
    }
    
    .button.product_type_simple.add_to_cart_button.ajax_add_to_cart {
    margin-left: 30px;
    margin-bottom: 20px;
}

    
    }
</style>


<div class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1 // Retrieve all products
        );
        $loop = new WP_Query($args);

        if ($loop->have_posts()) {
            echo '<div class="products-grid">'; // Grid container

            while ($loop->have_posts()) : $loop->the_post();
                wc_get_template_part('content', 'product');
            endwhile;

            echo '</div>'; // End grid container
        } else {
            echo '<p>' . __('No products found') . '</p>';
        }

        wp_reset_postdata();
        ?>
    </main>
</div>

<?php
get_footer();
?>
