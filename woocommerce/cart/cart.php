<?php

/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart'); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
    <?php do_action('woocommerce_before_cart_table'); ?>

    <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
        <thead>
            <tr>
                <th class="product-thumbnail">&nbsp;</th>
                <th class="product-name"><?php esc_html_e('Product', 'martfury'); ?></th>
                <th class="product-price"><?php esc_html_e('Price', 'martfury'); ?></th>
                <th class="product-quantity"><?php esc_html_e('Quantity', 'martfury'); ?></th>
                <th class="product-subtotal"><?php esc_html_e('Total', 'martfury'); ?></th>
                <th class="product-remove">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php do_action('woocommerce_before_cart_contents'); ?>

            <?php
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                /**
                 * Filter the product name.
                 *
                 * @since 2.1.0
                 * @param string $product_name Name of the product in the cart.
                 * @param array $cart_item The product in the cart.
                 * @param string $cart_item_key Key for the product in the cart.
                 */
                $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);

                if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                    $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
            ?>
                    <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">

                        <td class="product-thumbnail">
                            <?php
                            $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('thumbnail'), $cart_item, $cart_item_key);

                            if (! $product_permalink) {
                                echo wp_kses_post($thumbnail);
                            } else {
                                printf('<a href="%s">%s</a>', esc_url($product_permalink), wp_kses_post($thumbnail));
                            }
                            ?>
                        </td>

                        <td class="product-name" data-title="<?php esc_attr_e('Product', 'martfury'); ?>">
                            <?php
                            if (! $product_permalink) {
                                echo wp_kses_post($product_name . '&nbsp;');
                            } else {
                                /**
                                 * This filter is documented above.
                                 *
                                 * @since 2.1.0
                                 */
                                echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $product_name), $cart_item, $cart_item_key));
                            }

                            do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);

                            // Meta data.
                            echo wc_get_formatted_cart_item_data($cart_item); // PHPCS: XSS ok.

                            // Backorder notification.
                            if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
                                echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'martfury') . '</p>', $product_id));
                            }
                            ?>
                        </td>

                        <td class="product-price" data-title="<?php esc_attr_e('Price', 'martfury'); ?>">
                            <?php
                            echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
                            ?>
                        </td>

                        <td class="product-quantity">
                            <?php
                            if ($_product->is_sold_individually()) {
                                $min_quantity = 1;
                                $max_quantity = 1;
                            } else {
                                $min_quantity = 0;
                                $max_quantity = $_product->get_max_purchase_quantity();
                            }

                            $product_quantity = woocommerce_quantity_input(
                                array(
                                    'input_name'   => "cart[{$cart_item_key}][qty]",
                                    'input_value'  => $cart_item['quantity'],
                                    'max_value'    => $max_quantity,
                                    'min_value'    => $min_quantity,
                                    'product_name' => $product_name,
                                ),
                                $_product,
                                false
                            );


                            echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item);
                            ?>
                            <div class="product-remove">
                                <?php
                                echo apply_filters(
                                    'woocommerce_cart_item_remove_link',
                                    sprintf(
                                        '<a href="%s" class="mf-remove" aria-label="%s" data-product_id="%s" data-product_sku="%s"><i class="icon-cross2"></i></a>',
                                        esc_url(wc_get_cart_remove_url($cart_item_key)),
                                        esc_html__('Remove this item', 'martfury'),
                                        esc_attr($product_id),
                                        esc_attr($_product->get_sku())
                                    ),
                                    $cart_item_key
                                );
                                ?>
                            </div>
                        </td>

                        <td class="product-subtotal" data-title="<?php esc_attr_e('Total', 'martfury'); ?>">
                            <?php
                            echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key);
                            ?>
                        </td>
                        <td class="product-remove">
                            <?php
                            echo apply_filters(
                                'woocommerce_cart_item_remove_link',
                                sprintf(
                                    '<a href="%s" class="mf-remove" aria-label="%s" data-product_id="%s" data-product_sku="%s"><i class="icon-cross2"></i></a>',
                                    esc_url(wc_get_cart_remove_url($cart_item_key)),
                                    esc_html__('Remove', 'martfury'),
                                    esc_attr($product_id),
                                    esc_attr($_product->get_sku())
                                ),
                                $cart_item_key
                            );
                            ?>
                        </td>
                    </tr>
            <?php
                }
            }
            ?>

            <?php do_action('woocommerce_cart_contents'); ?>

            <tr>
                <td colspan="6" class="actions">

                    <a href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', get_permalink(get_option('woocommerce_shop_page_id')))); ?>"
                        class="btn-shop"><i class="icon-arrow-left"></i> <?php esc_html_e('Back To Shop', 'martfury'); ?>
                    </a>

                    <?php
                    if (function_exists("martfury_get_option")) {
                        $hidden = '';
                        if (intval(martfury_get_option('quantity_ajax'))) {
                            $hidden = 'hidden';
                        }
                    }
                    ?>

                    <button type="submit" class="button btn-update <?php echo esc_attr($hidden) ?>" name="update_cart"
                        value="<?php esc_attr_e('Update cart', 'martfury'); ?>"><?php esc_html_e('Update cart', 'martfury'); ?></button>

                    <?php do_action('woocommerce_cart_actions'); ?>

                    <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
                </td>
            </tr>

            <?php do_action('woocommerce_after_cart_contents'); ?>
        </tbody>
    </table>
    <?php do_action('woocommerce_after_cart_table'); ?>
</form>
<?php do_action('woocommerce_before_cart_collaterals'); ?>
<div class="cart-collaterals">
    <div class="row">
        <?php if (wc_coupons_enabled()) { ?>
            <div class="col-md-4 col-sm-12 col-coupon">
                <form class="woocommerce-cart-form-coupon_code" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
                    <div class="coupon">
                        <label for="coupon_code"><?php esc_html_e('Coupon Discount', 'martfury'); ?></label>
                        <input type="text" name="coupon_code" class="input-text" id="coupon_code" value=""
                            placeholder="<?php esc_attr_e('Coupon code', 'martfury'); ?>" />
                        <input type="submit" class="button" name="apply_coupon"
                            value="<?php esc_attr_e('Apply coupon', 'martfury'); ?>" />
                        <?php do_action('woocommerce_cart_coupon'); ?>
                    </div>
                </form>
            </div>
        <?php } ?>

        <?php
        $cart_class = 'col-md-8 col-sm-12 col-colla';
        if ('yes' === get_option('woocommerce_enable_shipping_calc') && WC()->cart->needs_shipping()) {
            $cart_class = 'col-md-4 col-sm-12 col-colla';
        ?>
            <div class="col-md-4 col-sm-12 col-colla">
                <?php
                woocommerce_shipping_calculator();
                ?>
            </div>
        <?php } ?>
        <div class="<?php echo esc_attr($cart_class); ?>">
            <?php do_action('woocommerce_cart_collaterals'); ?>
        </div>
    </div>

</div>

<?php do_action('woocommerce_after_cart'); ?>

<script>
    const onLoadCart = () => {
        document.getElementById("calc_shipping_postcode").placeholder = "Codigo Postal"
    }
    document.addEventListener('DOMContentLoaded', onLoadCart)
    window.addEventListener('DOMContentLoaded', onLoadCart)
    document.addEventListener('load', onLoadCart)
    window.addEventListener('load', onLoadCart)
</script>
<style type="text/css">
    .woocommerce-cart {}

    .woocommerce-cart-form__cart-item p,
    .woocommerce-cart-form__cart-item br {
        display: none;
    }

    .product-quantity p,
    .product-quantity br {
        display: none;
    }

    .woocommerce-cart-form__cart-item td {
        vertical-align: middle !important;
    }

    .woocommerce-cart .coupon br {
        display: none;
    }

    .woocommerce-shipping-calculator.woocommerce-shipping-calculator.woocommerce-shipping-calculator label {
        display: none !important;
    }

    .woocommerce-shipping-calculator br {
        display: none;
    }

    .checkout-button br {
        display: none;
    }

    .woocommerce form .form-row .select2-container .select2-selection--single .select2-selection__arrow {
        top: 0px !important;
    }

    #shipping_method label {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
    }

    .button br {
        display: none;
    }

    .woocommerce-info {
        border-top-color: var(--mf-background-primary-color) !important;
    }

    .woocommerce-info::before {
        color: var(--mf-background-primary-color) !important;
    }

    .woocommerce-cart .woocommerce .cart-collaterals .woocommerce-shipping-calculator .shipping-calculator-button {
        margin-bottom: 40px !important;
    }

    .city_select {
        border: 1px solid #ccc !important;
        padding: 10px 16px !important;
        height: auto;
        border-radius: 0 !important;
        min-height: 50px;
        color: var(--wc-form-color-text, #444) !important;
    }
</style>