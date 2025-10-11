#!/usr/bin/env python3
import pandas as pd
from sklearn.preprocessing import MinMaxScaler

# Load the data
major_buyers = pd.read_excel('MunchMakers_Sales_Analysis.xlsx', sheet_name='Major Buyers >$300')

# Filter for US only
us_buyers = major_buyers[major_buyers['country'] == 'US'].copy()

# Calculate metrics by state
state_metrics = us_buyers.groupby('state').agg({
    'total_revenue': 'sum',
    'total_orders': 'sum',
    'email': 'count'
}).reset_index()

state_metrics.columns = ['state', 'total_revenue', 'total_orders', 'customer_count']
state_metrics['avg_order_value'] = state_metrics['total_revenue'] / state_metrics['total_orders']
state_metrics['orders_per_customer'] = state_metrics['total_orders'] / state_metrics['customer_count']
state_metrics['revenue_per_customer'] = state_metrics['total_revenue'] / state_metrics['customer_count']

# Sort by total revenue
state_metrics = state_metrics.sort_values('total_revenue', ascending=False)

print("=" * 100)
print("TOP 10 US STATES - COMPREHENSIVE ANALYSIS")
print("=" * 100)
print()

# Top 10 by Revenue
print("📊 TOP 10 STATES BY TOTAL REVENUE")
print("-" * 100)
print(f"{'Rank':<5} {'State':<8} {'Revenue':<15} {'Customers':<12} {'Orders':<10} {'AOV':<12} {'Orders/Cust':<12}")
print("-" * 100)

for idx, row in state_metrics.head(10).iterrows():
    rank = list(state_metrics.index).index(idx) + 1
    print(f"{rank:<5} {row['state']:<8} ${row['total_revenue']:>12,.2f}  {int(row['customer_count']):>10}  {int(row['total_orders']):>8}  ${row['avg_order_value']:>9,.2f}  {row['orders_per_customer']:>10.2f}")

print()
print("=" * 100)
print("📈 TOP 10 STATES BY AVERAGE ORDER VALUE (AOV)")
print("-" * 100)
print(f"{'Rank':<5} {'State':<8} {'AOV':<15} {'Revenue':<15} {'Customers':<12} {'Orders':<10}")
print("-" * 100)

state_by_aov = state_metrics[state_metrics['total_orders'] >= 5].sort_values('avg_order_value', ascending=False)
for idx, (i, row) in enumerate(state_by_aov.head(10).iterrows(), 1):
    print(f"{idx:<5} {row['state']:<8} ${row['avg_order_value']:>12,.2f}  ${row['total_revenue']:>12,.2f}  {int(row['customer_count']):>10}  {int(row['total_orders']):>8}")

print()
print("=" * 100)
print("🔄 TOP 10 STATES BY REPEAT ORDERS (Orders per Customer)")
print("-" * 100)
print(f"{'Rank':<5} {'State':<8} {'Repeat Rate':<15} {'Revenue':<15} {'Customers':<12} {'Orders':<10}")
print("-" * 100)

state_by_repeat = state_metrics[state_metrics['customer_count'] >= 3].sort_values('orders_per_customer', ascending=False)
for idx, (i, row) in enumerate(state_by_repeat.head(10).iterrows(), 1):
    print(f"{idx:<5} {row['state']:<8} {row['orders_per_customer']:>13.2f}x  ${row['total_revenue']:>12,.2f}  {int(row['customer_count']):>10}  {int(row['total_orders']):>8}")

print()
print("=" * 100)
print("💰 TOP 10 STATES BY REVENUE PER CUSTOMER (Customer Lifetime Value)")
print("-" * 100)
print(f"{'Rank':<5} {'State':<8} {'Rev/Customer':<15} {'Total Revenue':<15} {'Customers':<12} {'Orders':<10}")
print("-" * 100)

state_by_ltv = state_metrics[state_metrics['customer_count'] >= 3].sort_values('revenue_per_customer', ascending=False)
for idx, (i, row) in enumerate(state_by_ltv.head(10).iterrows(), 1):
    print(f"{idx:<5} {row['state']:<8} ${row['revenue_per_customer']:>12,.2f}  ${row['total_revenue']:>12,.2f}  {int(row['customer_count']):>10}  {int(row['total_orders']):>8}")

print()
print("=" * 100)
print("🎯 BEST STATES TO TARGET - COMPOSITE SCORE")
print("   (Weighted: 30% Revenue, 25% AOV, 25% Repeat Rate, 20% LTV)")
print("-" * 100)

# Calculate composite score (normalized metrics)
# Filter states with meaningful data (at least 5 customers)
significant_states = state_metrics[state_metrics['customer_count'] >= 5].copy()

scaler = MinMaxScaler()
significant_states['revenue_score'] = scaler.fit_transform(significant_states[['total_revenue']])
significant_states['aov_score'] = scaler.fit_transform(significant_states[['avg_order_value']])
significant_states['repeat_score'] = scaler.fit_transform(significant_states[['orders_per_customer']])
significant_states['ltv_score'] = scaler.fit_transform(significant_states[['revenue_per_customer']])

# Composite score: 30% revenue, 25% AOV, 25% repeat rate, 20% LTV
significant_states['composite_score'] = (
    significant_states['revenue_score'] * 0.30 +
    significant_states['aov_score'] * 0.25 +
    significant_states['repeat_score'] * 0.25 +
    significant_states['ltv_score'] * 0.20
)

significant_states = significant_states.sort_values('composite_score', ascending=False)

print(f"{'Rank':<5} {'State':<8} {'Score':<10} {'Revenue':<15} {'AOV':<12} {'Repeat':<10} {'Customers':<10}")
print("-" * 100)

for idx, (i, row) in enumerate(significant_states.head(10).iterrows(), 1):
    print(f"{idx:<5} {row['state']:<8} {row['composite_score']:>8.3f}  ${row['total_revenue']:>12,.2f}  ${row['avg_order_value']:>9,.2f}  {row['orders_per_customer']:>8.2f}x  {int(row['customer_count']):>8}")

print()
print("=" * 100)
print("📝 SUMMARY & RECOMMENDATIONS")
print("=" * 100)

top_5 = significant_states.head(5)
print(f"\n✅ TOP 5 STATES TO TARGET:")
for idx, (i, row) in enumerate(top_5.iterrows(), 1):
    print(f"\n{idx}. {row['state']}")
    print(f"   • Revenue: ${row['total_revenue']:,.2f}")
    print(f"   • Customers: {int(row['customer_count'])}")
    print(f"   • Average Order Value: ${row['avg_order_value']:,.2f}")
    print(f"   • Repeat Rate: {row['orders_per_customer']:.2f}x orders per customer")
    print(f"   • LTV: ${row['revenue_per_customer']:,.2f} per customer")
    print(f"   • Composite Score: {row['composite_score']:.3f}/1.000")
