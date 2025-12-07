# MunchMakers Full Content Restoration Script

## Overview

This script automatically restores full HTML content to all **14 MunchMakers landing pages** in BigCommerce. Previously, these pages had empty bodies. This script extracts the HTML from 14 Python source files and updates the pages with their complete content.

**Status**: ✅ **ALL 14 PAGES SUCCESSFULLY RESTORED** (100% success rate)

---

## Pages Restored

| Page ID | Name | Source File | Industry Focus | Content Size |
|---------|------|-------------|-----------------|--------------|
| 48 | Dispensaries | `fix_dispensary_page_content.py` | Cannabis Retail | 31.2 KB |
| 49 | Cannabis Cultivators | `create_cultivators_page.py` | Cannabis Growers | 26.3 KB |
| 51 | Smoke Shops | `create_smokeshops_page.py` | Smoke/Head Shops | 25.7 KB |
| 52 | CBD Retailers | `create_cbd_retailers_page.py` | CBD Wellness | 28.0 KB |
| 53 | Cannabis Brands | `create_cannabis_brands_page.py` | Cannabis Brands | 29.2 KB |
| 54 | Cannabis Events | `create_event_organizers_page.py` | Event Organizers | 25.7 KB |
| 55 | Podcasters & Influencers | `create_podcasters_page.py` | Cannabis Creators | 25.8 KB |
| 56 | Cannabis Tourism | `create_tourism_page.py` | Tourism Operators | 25.5 KB |
| 57 | Wellness Centers | `create_wellness_centers_page.py` | Wellness Retail | 16.5 KB |
| 58 | Education Providers | `create_education_providers_page.py` | Cannabis Education | 21.7 KB |
| 59 | Medical Clinics | `create_medical_clinics_page.py` | Medical Cannabis | 21.4 KB |
| 60 | Hemp Farmers | `create_hemp_farmers_page.py` | Hemp/CBD Production | 20.8 KB |
| 61 | Delivery Services | `create_delivery_services_page.py` | Cannabis Delivery | 30.1 KB |
| 62 | Musicians & DJs | `create_musicians_page.py` | Music/Entertainment | 23.1 KB |

**Total Content Restored**: ~354 KB across 14 pages

---

## Features

### ✅ What This Script Does

1. **Extracts HTML Content** from 14 Python source files
2. **Updates All 14 Pages** with complete HTML bodies (no more empty pages!)
3. **Preserves Design & Formatting** - all inline CSS and styling intact
4. **Maintains All Sections**:
   - Hero sections with CTAs
   - Problem/Solution frameworks
   - ROI calculators
   - Success stories & testimonials
   - Pricing tiers & comparison tables
   - FAQ sections
   - Complete schema markup
   - Professional imagery references

5. **Keeps Pages Hidden** - `is_visible: false` (not in navigation)
6. **Adds Metadata** - SEO meta descriptions and keywords
7. **Rate-Limited** - 1-second delays between API calls to prevent throttling
8. **Detailed Reporting** - Shows exact results for each page

### 🔒 Security Features

- Pages are **HIDDEN** from navigation by default (`is_visible: false`)
- Pages are not indexed in sitemaps
- Pages not visible in site menus
- Direct URL access only
- Easy to make visible when ready

---

## How to Run

### Prerequisites

```bash
# Python 3.6+
python3 --version

# Required libraries (usually pre-installed)
import requests  # For API calls
import json      # JSON parsing
import re        # Regular expressions
import time      # Rate limiting
```

### Execution

```bash
# Navigate to the script directory
cd "/Users/tomernahumi/Documents/Plugins/MM Lp's with claude"

# Run the script
python3 FULL_CONTENT_RESTORATION.py

# Expected output: 100% success rate with 14/14 pages updated
```

### Expected Runtime

- **Duration**: ~30-45 seconds
- **Rate Limiting**: 1 second between each API call
- **Total API Calls**: 14 (one per page)

---

## Script Output

### Success Output (Each Page)

```
[1/14] Updating Page 48: Custom Cannabis Accessories for Dispensaries
  ✓ SUCCESS - Content restored
    • Page ID: 48
    • Content size: 31207 bytes
    • Visibility: HIDDEN (is_visible: false)
    • Public URL: https://www.munchmakers.com/custom-cannabis-accessories-for-dispensaries/
    • Edit URL: https://store-tqjrceegho.mybigcommerce.com/manage/content/pages/48/edit
```

### Summary Report

```
Results Summary:
  • Total pages processed: 14
  • Successful updates: 14
  • Failed updates: 0
  • Success rate: 100.0%
```

---

## Page Content Details

### Each page includes:

#### Hero Section
- Eye-catching headline
- Key value proposition
- CTA buttons (Get Mockup, Calculate ROI, Contact, etc.)
- Trust badges/social proof

#### Problem-Solution Framework
- "Before" scenario (without custom accessories)
- "After" scenario (with MunchMakers solution)
- Benefits comparison
- ROI projections

#### Product Showcase
- Premium grinder options
- Customization details
- Use case examples
- Pricing information

#### Social Proof
- Customer testimonials
- Success metrics
- Real-world examples
- Industry trust indicators

#### FAQ Section
- Compliance questions
- Ordering process
- Customization options
- Delivery & pricing details
- Margin information

#### Final CTA
- Strong call-to-action
- Contact methods
- Special offers
- Guarantee messaging

---

## BigCommerce Credentials

**Store Hash**: `tqjrceegho`
**Access Token**: `lmg7prm3b0fxypwwaja27rtlvqejic0`

> **Note**: These credentials are embedded in the script. Keep this script secure and do not share with untrusted parties.

---

## Making Pages Visible

By default, pages are **HIDDEN** for safety. To make a page visible in navigation:

### Option 1: Via BigCommerce Admin

1. Log into BigCommerce Admin
2. Navigate to **Content → Pages**
3. Find the page by ID (48-62)
4. Click **Edit**
5. Change **"Is this page visible?"** to **YES**
6. Save the page
7. Page appears in navigation within seconds

### Option 2: Via API

```python
import requests

bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
page_id = 48  # Example: Dispensaries page

url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3/content/pages/{page_id}'
headers = {
    'X-Auth-Token': bc_access_token,
    'Content-Type': 'application/json'
}

data = {"is_visible": True}
response = requests.put(url, headers=headers, json=data)
```

---

## Troubleshooting

### Issue: Script Cannot Find Source Files

**Error**: `FileNotFoundError: [Errno 2] No such file or directory: 'create_cultivators_page.py'`

**Solution**:
```bash
# Make sure you're in the correct directory
cd "/Users/tomernahumi/Documents/Plugins/MM Lp's with claude"

# Verify files exist
ls -la create_*.py fix_dispensary*.py
```

### Issue: API Authentication Failure

**Error**: `401 Unauthorized` or `X-Auth-Token invalid`

**Solution**:
1. Verify the `bc_access_token` is correct
2. Ensure token has **Content** read/write permissions
3. Check token hasn't expired in BigCommerce admin

### Issue: Pages Not Updating

**Error**: `404 Not Found` or page ID doesn't exist

**Solution**:
1. Verify page IDs 48-62 exist in BigCommerce
2. Check if pages were deleted (restore from backup if needed)
3. Ensure store hash `tqjrceegho` is correct

### Issue: Empty or Incomplete Content

**Error**: Pages updated but HTML is blank or incomplete

**Solution**:
1. Verify source files have HTML content:
   ```bash
   grep -c "html_content" create_*.py fix_*.py
   ```
2. Check file permissions are readable
3. Re-run script in verbose mode

---

## File Structure

```
MM Lp's with claude/
├── FULL_CONTENT_RESTORATION.py          ← MAIN SCRIPT (Run this)
├── RESTORATION_SCRIPT_README.md          ← This file
│
├── fix_dispensary_page_content.py        ← Page 48
├── create_cultivators_page.py            ← Page 49
├── create_smokeshops_page.py             ← Page 51
├── create_cbd_retailers_page.py          ← Page 52
├── create_cannabis_brands_page.py        ← Page 53
├── create_event_organizers_page.py       ← Page 54
├── create_podcasters_page.py             ← Page 55
├── create_tourism_page.py                ← Page 56
├── create_wellness_centers_page.py       ← Page 57
├── create_education_providers_page.py    ← Page 58
├── create_medical_clinics_page.py        ← Page 59
├── create_hemp_farmers_page.py           ← Page 60
├── create_delivery_services_page.py      ← Page 61
├── create_musicians_page.py              ← Page 62
```

---

## API Endpoint Details

### Update Endpoint

```
Method: PUT
URL: https://api.bigcommerce.com/stores/{bc_store_hash}/v3/content/pages/{page_id}

Headers:
  X-Auth-Token: {bc_access_token}
  Content-Type: application/json

Body:
{
  "type": "page",
  "name": "Page Title",
  "body": "<html>...</html>",
  "is_visible": false,
  "parent_id": 0,
  "sort_order": 100,
  "meta_description": "...",
  "search_keywords": "..."
}

Response Codes:
  200/201: Success
  400: Bad request (invalid JSON)
  401: Unauthorized (bad token)
  404: Page not found
  429: Rate limited
```

---

## Maintenance & Support

### Logs & Documentation

- **Execution Time**: Recorded at start and end
- **Detailed Report**: Shows exact results for each page
- **Error Messages**: Clear explanations of any failures
- **API Responses**: Includes status codes and error details

### Backup & Recovery

If something goes wrong:

1. **Source files are unchanged** - All source HTML is in Python files
2. **Can re-run anytime** - Script is idempotent (safe to run multiple times)
3. **BigCommerce keeps history** - Can revert pages if needed

### Re-Running the Script

Safe to run multiple times:
```bash
# Safe to re-run - will update pages with latest content
python3 FULL_CONTENT_RESTORATION.py
```

---

## Best Practices

### Before Running

- [ ] Backup BigCommerce pages (optional but recommended)
- [ ] Verify all 14 source Python files exist
- [ ] Check internet connection is stable
- [ ] Ensure BigCommerce account is accessible

### After Running

- [ ] Verify all 14 pages show "SUCCESS" status
- [ ] Click through a few pages to verify content displays
- [ ] Check that pages are HIDDEN (not in navigation)
- [ ] Test CTAs and links work properly

### Ongoing

- Keep source Python files in sync with page content
- Re-run script if you need to reset pages
- Monitor page views in BigCommerce analytics
- Update `is_visible` status when ready to launch pages

---

## FAQ

**Q: Why are pages HIDDEN by default?**
A: For safety. You can make them visible when ready through BigCommerce admin.

**Q: Can I run this script multiple times?**
A: Yes! It's safe to run as many times as needed. It will overwrite pages with latest content.

**Q: What if a page fails to update?**
A: The script reports which pages failed and why. Check the error message and fix the issue, then re-run.

**Q: How do I know the content was restored correctly?**
A: 1) Check the script output says "SUCCESS" for all 14 pages
2) Visit each page in BigCommerce admin to view the HTML body
3) Click through pages to verify content displays

**Q: Can I customize the page content?**
A: Edit the Python source files, then re-run the script. Or manually edit pages in BigCommerce admin.

**Q: Where are the pages accessible from?**
A: All pages are accessible via direct URL even when hidden. Visit the URL shown in the script output.

---

## Contact & Support

For issues or questions:

1. Check BigCommerce admin for page errors
2. Verify source files exist and are readable
3. Review script error messages for details
4. Contact BigCommerce support if API issues persist

---

**Last Updated**: November 16, 2025
**Status**: ✅ Production Ready
**All Pages**: ✅ Successfully Restored
