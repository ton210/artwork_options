# MunchMakers Comprehensive Sales Analysis

## Analysis Summary

This analysis combines sales data from multiple sources to provide a comprehensive view of MunchMakers' customer base and revenue performance.

### Data Sources Analyzed
- **Historical Data (sales.xlsx)**: 6,960 customer records (pre-April 2025)
- **WooCommerce (multidash.io)**: 2,426 orders (completed orders)
- **BigCommerce (munchmakers.com)**: 264 orders (July 2025 onwards)
- **Klaviyo**: 100 customer profiles (email/marketing data)

### Key Findings

#### Overall Statistics
- **Total Unique Customers**: 7,091
- **Major Buyers (>$300)**: 943 customers
- **Total Revenue (All)**: $2,853,014.86
- **Total Revenue (Major Buyers)**: $2,473,042.74
- **Average Order Value**: $300.25
- **Top Customer Revenue**: $217,731.51

#### Geographic Distribution

**Top 5 States by Revenue:**
1. **Unknown/Empty** - $1,162,474.67 (656 customers, 1,101 orders)
2. **JP13 (Japan)** - $221,483.88 (2 customers, 5 orders)
3. **NY (New York)** - $115,956.09 (24 customers, 67 orders)
4. **CA (California)** - $99,887.12 (27 customers, 77 orders)
5. **NJ (New Jersey)** - $87,474.38 (16 customers, 39 orders)

**Top 5 Countries by Revenue:**
1. **Unknown/Empty** - $1,103,921.83 (647 customers, 1,079 orders)
2. **US (United States)** - $992,318.91 (250 customers, 716 orders)
3. **JP (Japan)** - $222,444.13 (3 customers, 6 orders)
4. **DE (Germany)** - $66,266.94 (6 customers, 10 orders)
5. **AU (Australia)** - $30,235.66 (3 customers, 7 orders)

### Notable Insights

1. **High-Value International Customers**: Japan represents significant revenue with only 3-5 customers, indicating very high order values
2. **US Market Dominance**: United States is the primary market with 250+ major buyers
3. **Data Quality Note**: A significant portion of records (~$1.1M) have missing geographic data, likely from the historical sales.xlsx file
4. **Major Buyer Concentration**: 943 customers (13.3% of total) account for 86.7% of total revenue ($2.47M/$2.85M)

### Generated Reports

1. **MunchMakers_Sales_Analysis.xlsx** (648 KB)
   - Sheet: "Major Buyers >$300" - Complete list of 943 customers with revenue >$300
   - Sheet: "Analysis by State" - Revenue breakdown by US state
   - Sheet: "Analysis by Country" - Revenue breakdown by country
   - Individual state sheets for top 20 states with detailed customer lists
   - Sheet: "All Orders" - Complete deduplicated order history

2. **MunchMakers_Sales_Report.pdf** (391 KB)
   - Executive Summary
   - Top States by Revenue (table + visualization)
   - Top Countries by Revenue (table + pie chart)
   - Top 20 Customers by Revenue
   - Revenue vs Order Count scatter analysis

### Data Deduplication Methodology

- Removed 148 duplicate orders from WooCommerce and BigCommerce based on email + date + total amount
- Historical data from sales.xlsx was pre-aggregated and treated as unique customer records
- Final dataset: 9,502 unique records (2,542 individual orders + 6,960 historical customer records)

### Recommendations for Data Quality

1. **Complete Missing Geographic Data**: ~40% of revenue has missing state/country information
2. **Cross-reference with Klaviyo**: Only 100 profiles were retrieved - consider pulling complete customer database
3. **Standardize State Names**: Found variations like "Missouri" vs "MO", "New York" vs "NY"
4. **BigCommerce Email Collection**: Some BC orders missing email addresses - ensure email is required at checkout

---

*Analysis generated on October 4, 2025*
*Data sources: sales.xlsx, WooCommerce API, BigCommerce API, Klaviyo API*
