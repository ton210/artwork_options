#!/usr/bin/env python3
"""
Comprehensive Sales Analysis for MunchMakers
Combines data from: sales.xlsx, WooCommerce, BigCommerce, and Klaviyo
"""

import requests
from requests.auth import HTTPBasicAuth
import pandas as pd
from datetime import datetime
import json
from collections import defaultdict
import openpyxl
from openpyxl.styles import Font, PatternFill, Alignment
from openpyxl.utils.dataframe import dataframe_to_rows

# API Credentials
bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
bc_base_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3'

klaviyo_api_key = "pk_4168739985882153c9855917afa491667a"
klaviyo_base_url = "https://a.klaviyo.com/api"

woo_website = "www.multidash.io"
woo_username = "info@munchmakers.com"
woo_password = "XnqV2oHQCeZDLsZmoEPUYQ7M"
woo_base_url = f"https://{woo_website}/wp-json/wc/v3"

print("=" * 80)
print("MUNCHMAKERS COMPREHENSIVE SALES ANALYSIS")
print("=" * 80)

# Step 1: Load existing sales.xlsx data
print("\n[1/5] Loading sales.xlsx data...")
try:
    df_existing = pd.read_excel('sales.xlsx')
    print(f"✓ Loaded {len(df_existing)} rows from sales.xlsx")
    print(f"  Columns: {list(df_existing.columns)}")
    if len(df_existing) > 0:
        print(f"  Date range: {df_existing.iloc[:, 0].min()} to {df_existing.iloc[:, 0].max() if len(df_existing) > 0 else 'N/A'}")
except Exception as e:
    print(f"✗ Error loading sales.xlsx: {e}")
    df_existing = pd.DataFrame()

# Step 2: Pull WooCommerce orders
print("\n[2/5] Fetching WooCommerce orders...")
woo_orders = []
try:
    page = 1
    while True:
        response = requests.get(
            f'{woo_base_url}/orders',
            auth=HTTPBasicAuth(woo_username, woo_password),
            params={'per_page': 100, 'page': page, 'status': 'completed'},
            timeout=30
        )
        if response.status_code == 200:
            orders = response.json()
            if not orders:
                break
            woo_orders.extend(orders)
            print(f"  Fetched page {page}: {len(orders)} orders")
            page += 1
            if len(orders) < 100:
                break
        else:
            print(f"✗ WooCommerce API error: {response.status_code}")
            break
    print(f"✓ Total WooCommerce orders: {len(woo_orders)}")
except Exception as e:
    print(f"✗ Error fetching WooCommerce orders: {e}")

# Step 3: Pull BigCommerce orders (using v2 API)
print("\n[3/5] Fetching BigCommerce orders...")
bc_orders = []
bc_v2_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v2'
try:
    page = 1
    while True:
        response = requests.get(
            f'{bc_v2_url}/orders',
            headers={
                'X-Auth-Token': bc_access_token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            params={'limit': 250, 'page': page, 'min_date_created': '2025-07-01'},
            timeout=30
        )
        if response.status_code == 200:
            orders = response.json()
            if not orders:
                break
            bc_orders.extend(orders)
            print(f"  Fetched page {page}: {len(orders)} orders")
            page += 1
            if len(orders) < 250:
                break
        else:
            print(f"✗ BigCommerce API error: {response.status_code}")
            break
    print(f"✓ Total BigCommerce orders: {len(bc_orders)}")
except Exception as e:
    print(f"✗ Error fetching BigCommerce orders: {e}")

# Step 4: Pull Klaviyo profiles with proper pagination
print("\n[4/5] Fetching Klaviyo customer data...")
klaviyo_profiles = []
try:
    next_url = f'{klaviyo_base_url}/profiles/'
    page_num = 1

    while next_url:
        # Use the full next URL if available, otherwise use base URL
        if page_num == 1:
            response = requests.get(
                next_url,
                headers={
                    'Authorization': f'Klaviyo-API-Key {klaviyo_api_key}',
                    'revision': '2024-10-15',
                    'Accept': 'application/json'
                },
                params={'page[size]': 100},
                timeout=30
            )
        else:
            # For subsequent pages, use the next URL directly
            response = requests.get(
                next_url,
                headers={
                    'Authorization': f'Klaviyo-API-Key {klaviyo_api_key}',
                    'revision': '2024-10-15',
                    'Accept': 'application/json'
                },
                timeout=30
            )

        if response.status_code == 200:
            data = response.json()
            profiles = data.get('data', [])

            if not profiles:
                break

            klaviyo_profiles.extend(profiles)
            print(f"  Fetched page {page_num}: {len(profiles)} profiles (Total: {len(klaviyo_profiles)})")

            # Get the next page URL from links
            links = data.get('links', {})
            next_url = links.get('next')

            if not next_url:
                break

            page_num += 1

            # Safety limit to prevent infinite loops
            if page_num > 100:
                print(f"  Warning: Reached page limit of 100")
                break
        else:
            print(f"✗ Klaviyo API error: {response.status_code} - {response.text[:200]}")
            break

    print(f"✓ Total Klaviyo profiles: {len(klaviyo_profiles)}")
except Exception as e:
    print(f"✗ Error fetching Klaviyo profiles: {e}")

# Step 5: Process and combine all data
print("\n[5/5] Processing and combining data...")

# Create a Klaviyo lookup dictionary for enriching customer data
klaviyo_lookup = {}
for profile in klaviyo_profiles:
    attrs = profile.get('attributes', {})
    email = attrs.get('email', '').lower().strip()
    if email:
        location = attrs.get('location', {})
        klaviyo_lookup[email] = {
            'first_name': attrs.get('first_name', ''),
            'last_name': attrs.get('last_name', ''),
            'state': location.get('region', '') if location else '',
            'country': location.get('country', '') if location else '',
            'city': location.get('city', '') if location else '',
        }

print(f"  Built Klaviyo lookup with {len(klaviyo_lookup)} profiles")

# Currency conversion rate
JPY_TO_USD = 0.0067  # Approximate conversion rate (1 JPY = 0.0067 USD, or ~150 JPY = 1 USD)
print(f"  Using JPY to USD conversion rate: {JPY_TO_USD} (approx. 150 JPY = 1 USD)")

# Process WooCommerce orders
woo_data = []
woo_jpy_converted = 0

for order in woo_orders:
    country = order.get('billing', {}).get('country', '')
    total = float(order.get('total', 0))
    currency = order.get('currency', 'USD')

    # Convert JPY to USD if needed
    if country == 'JP' or currency == 'JPY':
        total = total * JPY_TO_USD
        woo_jpy_converted += 1

    woo_data.append({
        'order_id': f"WOO-{order['id']}",
        'date': order['date_created'],
        'email': order.get('billing', {}).get('email', '').lower().strip(),
        'customer_name': f"{order.get('billing', {}).get('first_name', '')} {order.get('billing', {}).get('last_name', '')}".strip(),
        'company': order.get('billing', {}).get('company', ''),
        'total': total,
        'state': order.get('billing', {}).get('state', ''),
        'country': country,
        'source': 'WooCommerce'
    })

if woo_jpy_converted > 0:
    print(f"  Converted {woo_jpy_converted} WooCommerce orders from JPY to USD")

# Process BigCommerce orders - v2 API
bc_data = []
bc_jpy_converted = 0
print(f"  Processing {len(bc_orders)} BigCommerce orders...")
for i, order in enumerate(bc_orders):
    billing = order.get('billing_address', {})
    email = billing.get('email', '').lower().strip()
    state = billing.get('state', '')
    country_iso = billing.get('country_iso2', '')
    company = billing.get('company', '')
    customer_name = f"{billing.get('first_name', '')} {billing.get('last_name', '')}".strip()

    # If no email in billing, try to get from shipping address
    if not email and order.get('shipping_address_count', 0) > 0:
        try:
            addr_response = requests.get(
                f'{bc_v2_url}/orders/{order["id"]}/shipping_addresses',
                headers={'X-Auth-Token': bc_access_token, 'Accept': 'application/json'},
                timeout=5
            )
            if addr_response.status_code == 200:
                addresses = addr_response.json()
                if addresses and len(addresses) > 0:
                    addr = addresses[0]
                    email = addr.get('email', '').lower().strip()
                    if not state:
                        state = addr.get('state', '')
                    if not country_iso:
                        country_iso = addr.get('country_iso2', '')
                    if not company:
                        company = addr.get('company', '')
                    if not customer_name:
                        customer_name = f"{addr.get('first_name', '')} {addr.get('last_name', '')}".strip()
        except:
            pass

    # Get total and check currency
    total = float(order.get('total_inc_tax', 0))
    currency_code = order.get('currency_code', 'USD')

    # Convert JPY to USD if needed
    if country_iso == 'JP' or currency_code == 'JPY':
        total = total * JPY_TO_USD
        bc_jpy_converted += 1

    bc_data.append({
        'order_id': f"BC-{order['id']}",
        'date': order['date_created'],
        'email': email,
        'customer_name': customer_name,
        'company': company,
        'total': total,
        'state': state,
        'country': country_iso,
        'source': 'BigCommerce'
    })

    if (i + 1) % 50 == 0:
        print(f"    Processed {i + 1}/{len(bc_orders)} orders...")

print(f"  ✓ Processed {len(bc_data)} BigCommerce orders")
if bc_jpy_converted > 0:
    print(f"  Converted {bc_jpy_converted} BigCommerce orders from JPY to USD")

# Process sales.xlsx data - it has: billing_email, total_revenue, order_count
sales_xlsx_data = []
if not df_existing.empty and 'billing_email' in df_existing.columns:
    print(f"  Processing {len(df_existing)} rows from sales.xlsx...")
    for _, row in df_existing.iterrows():
        # This is aggregated data, so we'll create synthetic order entries
        # Each represents a customer's total from the historical data
        sales_xlsx_data.append({
            'order_id': f"HIST-{row['billing_email']}",
            'date': '2024-01-01',  # Historical placeholder
            'email': str(row['billing_email']).lower().strip() if pd.notna(row['billing_email']) else '',
            'customer_name': '',
            'company': '',
            'total': float(row['total_revenue']) if pd.notna(row['total_revenue']) else 0,
            'state': '',
            'country': '',
            'source': 'Historical (sales.xlsx)',
            'order_count': int(row['order_count']) if pd.notna(row['order_count']) else 1
        })

# Combine all order data
all_orders = pd.DataFrame(woo_data + bc_data + sales_xlsx_data)
print(f"\n✓ Combined dataset: {len(all_orders)} orders")
print(f"  - WooCommerce: {len(woo_data)} orders")
print(f"  - BigCommerce: {len(bc_data)} orders")
print(f"  - Historical (sales.xlsx): {len(sales_xlsx_data)} customers")

# Deduplicate by email + total amount + date (similar orders)
print("\n" + "=" * 80)
print("DEDUPLICATION & AGGREGATION")
print("=" * 80)

# Handle order_count column for historical data
if 'order_count' not in all_orders.columns:
    all_orders['order_count'] = 1

# Remove duplicates based on email, date, and total (but not for historical data)
all_orders['date_clean'] = pd.to_datetime(all_orders['date'], errors='coerce').dt.date
non_hist = all_orders[all_orders['source'] != 'Historical (sales.xlsx)']
hist = all_orders[all_orders['source'] == 'Historical (sales.xlsx)']

# Deduplicate non-historical orders
non_hist_dedup = non_hist.drop_duplicates(subset=['email', 'date_clean', 'total'], keep='first')
print(f"Deduplicated non-historical orders: {len(non_hist)} → {len(non_hist_dedup)}")
print(f"Removed {len(non_hist) - len(non_hist_dedup)} duplicate orders")

# Combine with historical
all_orders_dedup = pd.concat([non_hist_dedup, hist], ignore_index=True)
print(f"Total unique records: {len(all_orders_dedup)}")

# Aggregate by customer (email) - sum revenue and count orders properly
agg_dict = {
    'total': 'sum',
    'order_count': 'sum',
    'customer_name': 'first',
    'company': 'first',
    'state': 'first',
    'country': 'first'
}

customer_agg = all_orders_dedup.groupby('email').agg(agg_dict).reset_index()
customer_agg.columns = ['email', 'total_revenue', 'total_orders', 'name', 'company', 'state', 'country']

# Enrich with Klaviyo data for missing information
enriched_count = 0
for idx, row in customer_agg.iterrows():
    email = row['email']
    if email in klaviyo_lookup:
        klaviyo_data = klaviyo_lookup[email]

        # Fill in missing name
        if pd.isna(row['name']) or not str(row['name']).strip():
            name = f"{klaviyo_data['first_name']} {klaviyo_data['last_name']}".strip()
            if name:
                customer_agg.at[idx, 'name'] = name
                enriched_count += 1

        # Fill in missing state
        if pd.isna(row['state']) or not str(row['state']).strip():
            if klaviyo_data['state']:
                customer_agg.at[idx, 'state'] = klaviyo_data['state']
                enriched_count += 1

        # Fill in missing country
        if pd.isna(row['country']) or not str(row['country']).strip():
            if klaviyo_data['country']:
                customer_agg.at[idx, 'country'] = klaviyo_data['country']
                enriched_count += 1

print(f"Enriched {enriched_count} data points from Klaviyo profiles")

# Filter customers with >$300
major_buyers = customer_agg[customer_agg['total_revenue'] >= 300].copy()
major_buyers = major_buyers.sort_values('total_revenue', ascending=False)

print(f"\nMajor buyers (>$300): {len(major_buyers)}")
print(f"Total revenue from major buyers: ${major_buyers['total_revenue'].sum():,.2f}")

# State/Country analysis
print("\n" + "=" * 80)
print("GEOGRAPHIC ANALYSIS")
print("=" * 80)

# By State
state_analysis = major_buyers.groupby('state').agg({
    'total_revenue': 'sum',
    'total_orders': 'sum',
    'email': 'count'
}).reset_index()
state_analysis.columns = ['state', 'revenue', 'total_orders', 'customer_count']
state_analysis = state_analysis.sort_values('revenue', ascending=False)
state_analysis['avg_order_value'] = state_analysis['revenue'] / state_analysis['total_orders']

print("\nTop 10 States by Revenue:")
for idx, row in state_analysis.head(10).iterrows():
    print(f"  {row['state']:20s} ${row['revenue']:>12,.2f}  |  {row['customer_count']:>3} customers  |  {row['total_orders']:>4} orders")

# By Country
country_analysis = major_buyers.groupby('country').agg({
    'total_revenue': 'sum',
    'total_orders': 'sum',
    'email': 'count'
}).reset_index()
country_analysis.columns = ['country', 'revenue', 'total_orders', 'customer_count']
country_analysis = country_analysis.sort_values('revenue', ascending=False)
country_analysis['avg_order_value'] = country_analysis['revenue'] / country_analysis['total_orders']

print("\nTop Countries by Revenue:")
for idx, row in country_analysis.head(10).iterrows():
    print(f"  {row['country']:20s} ${row['revenue']:>12,.2f}  |  {row['customer_count']:>3} customers  |  {row['total_orders']:>4} orders")

# Export to Excel
print("\n" + "=" * 80)
print("GENERATING REPORTS")
print("=" * 80)

output_file = 'MunchMakers_Sales_Analysis.xlsx'
with pd.ExcelWriter(output_file, engine='openpyxl') as writer:
    # Sheet 1: Major Buyers (>$300)
    major_buyers.to_excel(writer, sheet_name='Major Buyers >$300', index=False)

    # Sheet 2: By State
    state_analysis.to_excel(writer, sheet_name='Analysis by State', index=False)

    # Sheet 3: By Country
    country_analysis.to_excel(writer, sheet_name='Analysis by Country', index=False)

    # Sheet 4: Buyers by State (detailed)
    for state in state_analysis.head(20)['state']:
        state_buyers = major_buyers[major_buyers['state'] == state].sort_values('total_revenue', ascending=False)
        sheet_name = f'State-{state[:20]}'
        state_buyers.to_excel(writer, sheet_name=sheet_name, index=False)

    # Sheet 5: All orders (deduplicated)
    all_orders_dedup.to_excel(writer, sheet_name='All Orders', index=False)

print(f"\n✓ Excel report saved: {output_file}")

# Summary statistics
print("\n" + "=" * 80)
print("SUMMARY STATISTICS")
print("=" * 80)
print(f"Total unique customers: {len(customer_agg)}")
print(f"Major buyers (>$300): {len(major_buyers)}")
print(f"Total revenue (all): ${customer_agg['total_revenue'].sum():,.2f}")
print(f"Total revenue (major buyers): ${major_buyers['total_revenue'].sum():,.2f}")
print(f"Average order value: ${all_orders_dedup['total'].mean():,.2f}")
print(f"Top customer revenue: ${major_buyers['total_revenue'].max():,.2f}")
print("\n" + "=" * 80)
print("ANALYSIS COMPLETE")
print("=" * 80)
