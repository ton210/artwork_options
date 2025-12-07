#!/usr/bin/env python3
"""
Analyze pricing discrepancies across the site
Find products where base price doesn't match bulk pricing rules
"""

import requests
import json

# BigCommerce Store Configuration
BC_STORE_HASH = 'tqjrceegho'
BC_ACCESS_TOKEN = 'lmg7prm3b0fxypwwaja27rtlvqejic0'

# API Configuration
BASE_URL = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v3'
HEADERS = {
    'X-Auth-Token': BC_ACCESS_TOKEN,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

def get_all_products():
    """Get all products with pagination"""
    products = []
    page = 1
    limit = 50

    while True:
        url = f'{BASE_URL}/catalog/products'
        params = {
            'page': page,
            'limit': limit,
            'include_fields': 'id,name,price,sale_price'
        }

        try:
            response = requests.get(url, headers=HEADERS, params=params)
            response.raise_for_status()
            data = response.json()

            batch = data.get('data', [])
            if not batch:
                break

            products.extend(batch)
            page += 1

            # Safety limit
            if len(products) > 200:
                break

        except requests.exceptions.RequestException as e:
            print(f"Error fetching products page {page}: {e}")
            break

    return products

def get_product_bulk_pricing(product_id):
    """Get bulk pricing rules for a product"""
    url = f'{BASE_URL}/catalog/products/{product_id}/bulk-pricing-rules'
    try:
        response = requests.get(url, headers=HEADERS)
        response.raise_for_status()
        data = response.json()
        return data.get('data', [])
    except requests.exceptions.RequestException as e:
        return []

def analyze_pricing_discrepancy(product):
    """Analyze if a product has pricing discrepancies"""
    product_id = product['id']
    product_name = product['name']
    base_price = float(product.get('price', 0))
    sale_price = product.get('sale_price')

    # Use sale price if available, otherwise base price
    current_price = float(sale_price) if sale_price else base_price

    # Get bulk pricing rules
    bulk_rules = get_product_bulk_pricing(product_id)

    discrepancies = []

    for rule in bulk_rules:
        rule_id = rule.get('id')
        min_qty = rule.get('quantity_min', 0)
        max_qty = rule.get('quantity_max', 'unlimited')
        amount = float(rule.get('amount', 0))

        # Check for discrepancies
        # For low quantities, bulk price should match or be close to base price
        if min_qty <= 10 and amount > 0:
            price_diff = abs(current_price - amount)

            # If difference is significant (more than $1), it's likely a discrepancy
            if price_diff > 1.00:
                discrepancies.append({
                    'rule_id': rule_id,
                    'qty_range': f"{min_qty}-{max_qty}",
                    'bulk_price': amount,
                    'expected_price': current_price,
                    'difference': price_diff
                })

    return discrepancies

def main():
    print("🔍 ANALYZING PRICING DISCREPANCIES SITEWIDE")
    print("=" * 60)
    print("Looking for products where base price ≠ bulk pricing...")
    print()

    # Get all products
    print("📥 Fetching all products...")
    products = get_all_products()
    print(f"Found {len(products)} products to analyze")
    print()

    # Analyze each product
    total_discrepancies = 0
    products_with_issues = []

    for i, product in enumerate(products):
        if i % 20 == 0:
            print(f"📊 Analyzed {i}/{len(products)} products...")

        discrepancies = analyze_pricing_discrepancy(product)

        if discrepancies:
            total_discrepancies += len(discrepancies)
            products_with_issues.append({
                'product': product,
                'discrepancies': discrepancies
            })

    print(f"✅ Analysis complete!")
    print()

    # Report findings
    print("📋 FINDINGS:")
    print("-" * 40)
    print(f"Products analyzed: {len(products)}")
    print(f"Products with discrepancies: {len(products_with_issues)}")
    print(f"Total pricing discrepancies: {total_discrepancies}")
    print()

    if products_with_issues:
        print("🚨 PRODUCTS WITH PRICING DISCREPANCIES:")
        print("-" * 60)

        # Show first 10 for analysis
        for item in products_with_issues[:10]:
            product = item['product']
            discrepancies = item['discrepancies']

            current_price = float(product.get('sale_price', 0)) if product.get('sale_price') else float(product.get('price', 0))

            print(f"Product {product['id']}: {product['name']}")
            print(f"  Current Price: ${current_price}")

            for disc in discrepancies:
                print(f"  ❌ Rule {disc['rule_id']} ({disc['qty_range']} qty): ${disc['bulk_price']} (should be ${disc['expected_price']})")
            print()

        if len(products_with_issues) > 10:
            print(f"... and {len(products_with_issues) - 10} more products with issues")

    # Pattern analysis
    print("\n🔍 PATTERN ANALYSIS:")
    print("-" * 40)

    # Look for common price ratios
    price_ratios = []
    for item in products_with_issues[:20]:  # Analyze first 20
        product = item['product']
        current_price = float(product.get('sale_price', 0)) if product.get('sale_price') else float(product.get('price', 0))

        for disc in item['discrepancies']:
            if disc['bulk_price'] > 0:
                ratio = current_price / disc['bulk_price']
                price_ratios.append(ratio)

    if price_ratios:
        avg_ratio = sum(price_ratios) / len(price_ratios)
        print(f"Average price ratio (current/bulk): {avg_ratio:.3f}")

        # Common ratios
        ratio_counts = {}
        for ratio in price_ratios:
            rounded_ratio = round(ratio, 2)
            ratio_counts[rounded_ratio] = ratio_counts.get(rounded_ratio, 0) + 1

        print("Most common ratios:")
        for ratio, count in sorted(ratio_counts.items(), key=lambda x: x[1], reverse=True)[:5]:
            print(f"  {ratio:.2f}x: {count} occurrences")

    print(f"\n💡 LIKELY CAUSE:")
    if products_with_issues:
        print("Bulk pricing rules were not updated when base prices were changed")
        print("This suggests the bulk pricing update script either:")
        print("1. Missed some products")
        print("2. Was run before final price adjustments")
        print("3. Had errors during execution")

        print(f"\n🔧 SOLUTION:")
        print("Re-run bulk pricing update for all affected products")

if __name__ == "__main__":
    main()