#!/usr/bin/env python3
"""
Generate PDF Report with Visualizations for MunchMakers Sales Analysis
"""

import pandas as pd
import matplotlib.pyplot as plt
import matplotlib
matplotlib.use('Agg')  # Use non-GUI backend
from reportlab.lib import colors
from reportlab.lib.pagesizes import letter, A4
from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer, PageBreak, Image
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_RIGHT
from datetime import datetime
import os

print("Generating PDF Report with Visualizations...")

# Load the analysis data
excel_file = 'MunchMakers_Sales_Analysis.xlsx'
major_buyers = pd.read_excel(excel_file, sheet_name='Major Buyers >$300')
state_analysis = pd.read_excel(excel_file, sheet_name='Analysis by State')
country_analysis = pd.read_excel(excel_file, sheet_name='Analysis by Country')

# Create visualizations
print("Creating visualizations...")

# 1. Top 15 States by Revenue - Bar Chart
fig, ax = plt.subplots(figsize=(12, 6))
top_states = state_analysis.head(15).sort_values('revenue', ascending=True)
colors_bar = plt.cm.viridis(range(len(top_states)))
ax.barh(top_states['state'].astype(str), top_states['revenue'], color=colors_bar)
ax.set_xlabel('Revenue ($)', fontsize=12)
ax.set_title('Top 15 States by Revenue', fontsize=16, fontweight='bold')
ax.xaxis.set_major_formatter(plt.FuncFormatter(lambda x, p: f'${x/1000:.0f}K'))
plt.tight_layout()
plt.savefig('chart_states.png', dpi=150, bbox_inches='tight')
plt.close()

# 2. Top 10 Countries by Revenue - Pie Chart
fig, ax = plt.subplots(figsize=(10, 8))
top_countries = country_analysis.head(10)
# Replace empty country with "Unknown"
country_names = top_countries['country'].astype(str).replace('nan', 'Unknown').replace('', 'Unknown')
explode = [0.1 if i == 0 else 0 for i in range(len(top_countries))]
ax.pie(top_countries['revenue'], labels=country_names, autopct='%1.1f%%',
       explode=explode, startangle=90)
ax.set_title('Revenue Distribution by Country (Top 10)', fontsize=16, fontweight='bold')
plt.tight_layout()
plt.savefig('chart_countries.png', dpi=150, bbox_inches='tight')
plt.close()

# 3. Top 20 Customers by Revenue - Horizontal Bar
fig, ax = plt.subplots(figsize=(12, 10))
top_customers = major_buyers.head(20).sort_values('total_revenue', ascending=True)
customer_labels = [f"{row['email'][:30]}..." if len(str(row['email'])) > 30 else str(row['email'])
                   for _, row in top_customers.iterrows()]
colors_cust = plt.cm.plasma(range(len(top_customers)))
ax.barh(customer_labels, top_customers['total_revenue'], color=colors_cust)
ax.set_xlabel('Total Revenue ($)', fontsize=12)
ax.set_title('Top 20 Customers by Revenue', fontsize=16, fontweight='bold')
ax.xaxis.set_major_formatter(plt.FuncFormatter(lambda x, p: f'${x/1000:.0f}K'))
plt.tight_layout()
plt.savefig('chart_top_customers.png', dpi=150, bbox_inches='tight')
plt.close()

# 4. Revenue vs Order Count Scatter
fig, ax = plt.subplots(figsize=(12, 8))
scatter = ax.scatter(major_buyers['total_orders'], major_buyers['total_revenue'],
                     alpha=0.6, c=major_buyers['total_revenue'], cmap='coolwarm', s=100)
ax.set_xlabel('Total Orders', fontsize=12)
ax.set_ylabel('Total Revenue ($)', fontsize=12)
ax.set_title('Revenue vs Order Count (Major Buyers)', fontsize=16, fontweight='bold')
ax.yaxis.set_major_formatter(plt.FuncFormatter(lambda y, p: f'${y/1000:.0f}K'))
plt.colorbar(scatter, label='Revenue ($)')
plt.tight_layout()
plt.savefig('chart_scatter.png', dpi=150, bbox_inches='tight')
plt.close()

print("✓ Created 4 visualizations")

# Generate PDF
print("Generating PDF report...")
pdf_file = 'MunchMakers_Sales_Report.pdf'
doc = SimpleDocTemplate(pdf_file, pagesize=letter,
                        rightMargin=50, leftMargin=50,
                        topMargin=50, bottomMargin=50)

# Container for PDF elements
elements = []
styles = getSampleStyleSheet()

# Custom styles
title_style = ParagraphStyle(
    'CustomTitle',
    parent=styles['Heading1'],
    fontSize=24,
    textColor=colors.HexColor('#1f4788'),
    spaceAfter=30,
    alignment=TA_CENTER
)

heading_style = ParagraphStyle(
    'CustomHeading',
    parent=styles['Heading2'],
    fontSize=16,
    textColor=colors.HexColor('#2e5090'),
    spaceAfter=12,
    spaceBefore=12
)

# Title Page
elements.append(Spacer(1, 2*inch))
elements.append(Paragraph("MUNCHMAKERS", title_style))
elements.append(Paragraph("Comprehensive Sales Analysis Report", styles['Heading2']))
elements.append(Spacer(1, 0.5*inch))
elements.append(Paragraph(f"Generated: {datetime.now().strftime('%B %d, %Y')}", styles['Normal']))
elements.append(PageBreak())

# Executive Summary
elements.append(Paragraph("Executive Summary", heading_style))
elements.append(Spacer(1, 12))

summary_data = [
    ["Metric", "Value"],
    ["Total Unique Customers", f"{len(major_buyers):,}"],
    ["Major Buyers (>$300)", f"{len(major_buyers):,}"],
    ["Total Revenue (Major Buyers)", f"${major_buyers['total_revenue'].sum():,.2f}"],
    ["Average Customer Revenue", f"${major_buyers['total_revenue'].mean():,.2f}"],
    ["Top Customer Revenue", f"${major_buyers['total_revenue'].max():,.2f}"],
    ["Total Orders", f"{int(major_buyers['total_orders'].sum()):,}"],
]

summary_table = Table(summary_data, colWidths=[3.5*inch, 2*inch])
summary_table.setStyle(TableStyle([
    ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#2e5090')),
    ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
    ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
    ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
    ('FONTSIZE', (0, 0), (-1, 0), 12),
    ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
    ('BACKGROUND', (0, 1), (-1, -1), colors.beige),
    ('GRID', (0, 0), (-1, -1), 1, colors.black),
]))
elements.append(summary_table)
elements.append(PageBreak())

# Geographic Analysis - States
elements.append(Paragraph("Top States by Revenue", heading_style))
elements.append(Spacer(1, 12))

state_data = [["State", "Revenue", "Customers", "Orders", "Avg Order Value"]]
for _, row in state_analysis.head(15).iterrows():
    state_name = str(row['state']) if str(row['state']) != 'nan' and str(row['state']) != '' else 'Unknown'
    state_data.append([
        state_name,
        f"${row['revenue']:,.2f}",
        f"{int(row['customer_count']):,}",
        f"{int(row['total_orders']):,}",
        f"${row['avg_order_value']:,.2f}"
    ])

state_table = Table(state_data, colWidths=[1.2*inch, 1.5*inch, 1.2*inch, 1.2*inch, 1.5*inch])
state_table.setStyle(TableStyle([
    ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#2e5090')),
    ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
    ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
    ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
    ('FONTSIZE', (0, 0), (-1, 0), 10),
    ('FONTSIZE', (0, 1), (-1, -1), 8),
    ('BOTTOMPADDING', (0, 0), (-1, 0), 10),
    ('GRID', (0, 0), (-1, -1), 1, colors.black),
    ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.lightgrey]),
]))
elements.append(state_table)
elements.append(Spacer(1, 20))
elements.append(Image('chart_states.png', width=6*inch, height=3*inch))
elements.append(PageBreak())

# Geographic Analysis - Countries
elements.append(Paragraph("Top Countries by Revenue", heading_style))
elements.append(Spacer(1, 12))

country_data = [["Country", "Revenue", "Customers", "Orders", "Avg Order Value"]]
for _, row in country_analysis.head(10).iterrows():
    country_name = str(row['country']) if str(row['country']) != 'nan' and str(row['country']) != '' else 'Unknown'
    country_data.append([
        country_name,
        f"${row['revenue']:,.2f}",
        f"{int(row['customer_count']):,}",
        f"{int(row['total_orders']):,}",
        f"${row['avg_order_value']:,.2f}"
    ])

country_table = Table(country_data, colWidths=[1.2*inch, 1.5*inch, 1.2*inch, 1.2*inch, 1.5*inch])
country_table.setStyle(TableStyle([
    ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#2e5090')),
    ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
    ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
    ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
    ('FONTSIZE', (0, 0), (-1, 0), 10),
    ('FONTSIZE', (0, 1), (-1, -1), 8),
    ('BOTTOMPADDING', (0, 0), (-1, 0), 10),
    ('GRID', (0, 0), (-1, -1), 1, colors.black),
    ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.lightgrey]),
]))
elements.append(country_table)
elements.append(Spacer(1, 20))
elements.append(Image('chart_countries.png', width=5*inch, height=4*inch))
elements.append(PageBreak())

# Top Customers
elements.append(Paragraph("Top 20 Customers by Revenue", heading_style))
elements.append(Spacer(1, 12))

cust_data = [["Email/Company", "Revenue", "Orders"]]
for _, row in major_buyers.head(20).iterrows():
    display_name = str(row['company']) if pd.notna(row['company']) and str(row['company']).strip() else str(row['email'])[:40]
    cust_data.append([
        display_name,
        f"${row['total_revenue']:,.2f}",
        f"{int(row['total_orders']):,}"
    ])

cust_table = Table(cust_data, colWidths=[4*inch, 1.5*inch, 1*inch])
cust_table.setStyle(TableStyle([
    ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#2e5090')),
    ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
    ('ALIGN', (0, 0), (0, -1), 'LEFT'),
    ('ALIGN', (1, 0), (-1, -1), 'CENTER'),
    ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
    ('FONTSIZE', (0, 0), (-1, 0), 10),
    ('FONTSIZE', (0, 1), (-1, -1), 8),
    ('BOTTOMPADDING', (0, 0), (-1, 0), 10),
    ('GRID', (0, 0), (-1, -1), 1, colors.black),
    ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.lightgrey]),
]))
elements.append(cust_table)
elements.append(PageBreak())

# Visualizations Page
elements.append(Paragraph("Top Customers Visualization", heading_style))
elements.append(Spacer(1, 12))
elements.append(Image('chart_top_customers.png', width=6.5*inch, height=5*inch))
elements.append(PageBreak())

elements.append(Paragraph("Revenue vs Order Count Analysis", heading_style))
elements.append(Spacer(1, 12))
elements.append(Image('chart_scatter.png', width=6.5*inch, height=4.5*inch))

# Build PDF
doc.build(elements)
print(f"✓ PDF report saved: {pdf_file}")

# Clean up chart images
for chart_file in ['chart_states.png', 'chart_countries.png', 'chart_top_customers.png', 'chart_scatter.png']:
    if os.path.exists(chart_file):
        os.remove(chart_file)

print("\n" + "=" * 80)
print("PDF REPORT GENERATION COMPLETE")
print("=" * 80)
print(f"Files created:")
print(f"  - {excel_file}")
print(f"  - {pdf_file}")
