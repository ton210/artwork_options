<?php
/**
 * Pricing Class
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MunchMakers_Pricing {

    public function get_effective_moq( $product_id, $variation_id = 0 ) {
        $moq = $variation_id ? get_post_meta( $variation_id, '_tiered_price_minimum_qty', true ) : null;
        
        if ( empty( $moq ) || ! is_numeric( $moq ) ) {
            $moq = get_post_meta( $product_id, '_tiered_price_minimum_qty', true );
        }
        
        return max( 1, (int) $moq );
    }

    public function get_quantity_interval( $product_id ) {
        $interval = get_post_meta( $product_id, 'quantity_interval', true );
        return max( 1, (int) $interval );
    }

    public function get_product_pricing_rules( $product_id, $variation_id = 0 ) {
        // Use cache to improve performance
        $cache_key = "munchmakers_rules_{$product_id}_{$variation_id}";
        $cached_rules = wp_cache_get( $cache_key, 'munchmakers_pricing' );
        if ( false !== $cached_rules ) {
            return 'none' === $cached_rules ? null : $cached_rules;
        }

        $rules_meta = $variation_id ? get_post_meta( $variation_id, '_fixed_price_rules', true ) : null;
        
        if ( empty( $rules_meta ) ) {
            $rules_meta = get_post_meta( $product_id, '_fixed_price_rules', true );
        }
        
        $rules_raw = is_array( $rules_meta ) ? $rules_meta : maybe_unserialize( $rules_meta );
        
        if ( ! empty( $rules_raw ) && is_array( $rules_raw ) ) {
            $validated_rules = $this->validate_and_format_rules( $rules_raw );
            if ( ! empty( $validated_rules ) ) {
                ksort( $validated_rules, SORT_NUMERIC );
                wp_cache_set( $cache_key, $validated_rules, 'munchmakers_pricing', 300 );
                return $validated_rules;
            }
        }
        
        wp_cache_set( $cache_key, 'none', 'munchmakers_pricing', 300 );
        return null;
    }

    private function validate_and_format_rules( $rules ) {
        $validated_rules = array();
        
        foreach ( $rules as $qty => $price ) {
            if ( is_numeric( $qty ) && $qty > 0 && is_numeric( $price ) && $price >= 0 ) {
                $validated_rules[ (int) $qty ] = wc_format_decimal( $price );
            }
        }
        
        return $validated_rules;
    }

    public function get_base_price( $product_id, $variation_id = 0 ) {
        $product = wc_get_product( $variation_id ?: $product_id );
        if ( ! $product ) {
            return null;
        }

        // Get the display price (which includes currency conversion)
        $price = wc_get_price_to_display( $product );
        return $price !== '' && ! is_null( $price ) ? (float) $price : null;
    }

    /**
     * Get converted price through currency converter
     */
    private function get_converted_price( $price, $product_context ) {
        if ( is_null( $price ) ) {
            return null;
        }

        // Handle Aelia Currency Switcher
        if ( class_exists( 'WC_Aelia_CurrencySwitcher' ) ) {
            $base_currency = get_option( 'woocommerce_currency' );
            $selected_currency = get_woocommerce_currency();
            
            if ( $selected_currency && $base_currency && $selected_currency !== $base_currency ) {
                try {
                    $aelia_cs = WC_Aelia_CurrencySwitcher::instance();
                    $converted_price = $aelia_cs->convert( $price, $base_currency, $selected_currency );
                    if ( is_numeric( $converted_price ) ) {
                        return (float) $converted_price;
                    }
                } catch ( Exception $e ) {
                    // Log error but continue with fallback
                    error_log( 'MunchMakers Currency Conversion Error: ' . $e->getMessage() );
                }
            }
        }

        // Apply WooCommerce price filters as fallback
        return (float) apply_filters( 'woocommerce_product_get_price', $price, $product_context );
    }

    /**
     * Get pricing rules with currency conversion applied
     */
    public function get_filtered_product_pricing_rules( $product_id, $variation_id = 0 ) {
        $raw_rules = $this->get_product_pricing_rules( $product_id, $variation_id );
        if ( is_null( $raw_rules ) ) {
            return null;
        }

        $product_context = wc_get_product( $variation_id ?: $product_id );
        if ( ! $product_context ) {
            return $raw_rules;
        }

        $filtered_rules = array();
        foreach ( $raw_rules as $qty => $price ) {
            $filtered_price = $this->get_converted_price( (float) $price, $product_context );
            if ( ! is_null( $filtered_price ) ) {
                $filtered_rules[ $qty ] = $filtered_price;
            }
        }

        return $filtered_rules;
    }

    public function calculate_price_for_quantity( $quantity, $product_id, $variation_id = 0 ) {
        // Use filtered rules that have currency conversion applied
        $rules = $this->get_filtered_product_pricing_rules( $product_id, $variation_id );
        $base_price = $this->get_base_price( $product_id, $variation_id );
        
        if ( $base_price === null ) {
            return 0;
        }
        
        if ( ! $rules ) {
            return $base_price;
        }

        // Find the first rule tier
        $first_tier_qty = min( array_keys( $rules ) );
        
        // If quantity is below first tier, use base price
        if ( $quantity < $first_tier_qty ) {
            return $base_price;
        }
        
        $matched_price = $base_price;
        
        foreach ( $rules as $rule_qty => $rule_price ) {
            if ( $quantity >= $rule_qty ) {
                $matched_price = $rule_price;
            } else {
                break; // Rules are sorted, no need to check further
            }
        }
        
        return $matched_price;
    }

    public function get_initial_pricing_data( $product_id, $variation_id = 0 ) {
        $moq = $this->get_effective_moq( $product_id, $variation_id );
        $interval = $this->get_quantity_interval( $product_id );
        $rules = $this->get_filtered_product_pricing_rules( $product_id, $variation_id ); // Use filtered rules
        $base_price = $this->get_base_price( $product_id, $variation_id );
        $max_qty = $rules ? max( array_keys( $rules ) ) * 2 : 1000;

        // Get raw price for savings calculation
        $product = wc_get_product( $variation_id ?: $product_id );
        $regular_price_raw = null;
        if ( $product ) {
            $regular_price = $product->get_regular_price( 'edit' );
            if ( $regular_price === '' || is_null( $regular_price ) ) {
                $regular_price = $product->get_price( 'edit' );
            }
            if ( $regular_price !== '' && ! is_null( $regular_price ) ) {
                $regular_price_raw = (float) $regular_price;
            }
        }

        $savings_base_price = $this->get_converted_price( $regular_price_raw, $product );
        $savings_base_price_display = is_null( $savings_base_price ) ? null : wc_get_price_to_display( $product, array( 'price' => $savings_base_price ) );
        
        return array(
            'moq'                         => $moq,
            'interval'                    => $interval,
            'rules'                       => $rules, // Already converted
            'base_price'                  => $base_price,
            'base_unit_price_for_savings' => $savings_base_price_display ?? $base_price,
            'min_rule_qty'                => ! empty( $rules ) ? min( array_keys( $rules ) ) : null,
            'last_rule_qty'               => ! empty( $rules ) ? max( array_keys( $rules ) ) : null,
            'max_qty'                     => (int) $max_qty,
        );
    }
}