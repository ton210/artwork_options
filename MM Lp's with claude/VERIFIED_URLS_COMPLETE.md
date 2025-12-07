# MunchMakers Landing Pages - VERIFIED Complete URLs

## ⚠️ IMPORTANT NOTES:
1. **All pages are currently HIDDEN** (is_visible: false) - they won't be publicly accessible until made visible
2. **Page ID 50 has a mismatch** - Title says "Delivery Service" but URL is for "Musicians"
3. **You're getting 404 errors because the pages are hidden** - you need to be logged into BigCommerce admin to view them

## VERIFIED WORKING URLs (14 Landing Pages)

### 1. Cannabis Dispensaries
- **Page ID:** 48
- **Complete URL:** https://www.munchmakers.com/custom-cannabis-accessories-for-dispensaries/
- **Status:** ⚠️ Hidden (needs to be made visible)

### 2. Cannabis Cultivators
- **Page ID:** 49
- **Complete URL:** https://www.munchmakers.com/custom-accessories-for-cannabis-cultivators-growers/
- **Status:** ⚠️ Hidden (needs to be made visible)

### 3. Smoke Shops
- **Page ID:** 51
- **Complete URL:** https://www.munchmakers.com/wholesale-grinders-for-smoke-shops-65-margins/
- **Status:** ⚠️ Hidden (needs to be made visible)

### 4. CBD Retailers
- **Page ID:** 52
- **Complete URL:** https://www.munchmakers.com/premium-cbd-accessories-wellness-bundles-that-sell/
- **Status:** ⚠️ Hidden (needs to be made visible)

### 5. Cannabis Brands
- **Page ID:** 53
- **Complete URL:** https://www.munchmakers.com/custom-grinders-for-cannabis-brands-build-loyalty/
- **Status:** ⚠️ Hidden (needs to be made visible)

### 6. Event Organizers
- **Page ID:** 54
- **Complete URL:** https://www.munchmakers.com/custom-event-grinders-swag-that-doesn-t-suck/
- **Status:** ⚠️ Hidden (needs to be made visible)

### 7. Podcasters & Influencers
- **Page ID:** 55
- **Complete URL:** https://www.munchmakers.com/custom-cannabis-merch-for-podcasters-influencers/
- **Status:** ⚠️ Hidden (needs to be made visible)

### 8. Cannabis Tourism
- **Page ID:** 56
- **Complete URL:** https://www.munchmakers.com/cannabis-tourism-souvenirs-premium-custom-grinders/
- **Status:** ⚠️ Hidden (needs to be made visible)

### 9. Wellness Centers
- **Page ID:** 57
- **Complete URL:** https://www.munchmakers.com/mindfulness-grinders-for-wellness-centers-yoga-studios/
- **Status:** ⚠️ Hidden (needs to be made visible)

### 10. Education Providers
- **Page ID:** 58
- **Complete URL:** https://www.munchmakers.com/cannabis-education-training-tools-student-grinder-kits/
- **Status:** ⚠️ Hidden (needs to be made visible)

### 11. Medical Clinics
- **Page ID:** 59
- **Complete URL:** https://www.munchmakers.com/medical-cannabis-patient-tools-clinical-grade-grinders/
- **Status:** ⚠️ Hidden (needs to be made visible)

### 12. Hemp Farmers
- **Page ID:** 60
- **Complete URL:** https://www.munchmakers.com/hemp-farm-branding-custom-grinders-build-premium-brands/
- **Status:** ⚠️ Hidden (needs to be made visible)

### 13. Cannabis Delivery Services
- **Page ID:** 61
- **Complete URL:** https://www.munchmakers.com/custom-cannabis-accessories-for-delivery-services/
- **Status:** ⚠️ Hidden (needs to be made visible)

### 14. Musicians & Artists
- **Page ID:** 62
- **Complete URL:** https://www.munchmakers.com/custom-grinders-for-musicians-artists-tour-merch-m7wu/
- **Status:** ⚠️ Hidden (needs to be made visible)

---

## ❌ PROBLEMATIC PAGE

### Page ID 50 (Needs Fix)
- **Title Says:** Delivery Service Retention Tools | MunchMakers
- **URL Actually Is:** https://www.munchmakers.com/custom-grinders-for-musicians-artists-tour-merch/
- **Issue:** This page has conflicting content - title doesn't match URL
- **Action Needed:** Delete this page or fix the content

---

## HOW TO MAKE PAGES VISIBLE (Fix 404 Errors)

To make these pages publicly accessible, you need to update each page's visibility:

1. Log into BigCommerce Admin
2. Go to Storefront > Web Pages
3. For each page, click Edit
4. Change "Page Visibility" from "Hidden" to "Visible"
5. Save

Or use this API script to make all pages visible:

```python
import requests

bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'

page_ids = [48, 49, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62]

for page_id in page_ids:
    url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3/content/pages/{page_id}'
    headers = {
        'X-Auth-Token': bc_access_token,
        'Content-Type': 'application/json'
    }

    # Make page visible
    update_data = {"is_visible": True}
    response = requests.put(url, headers=headers, json=update_data)

    if response.status_code == 200:
        print(f"✅ Page {page_id} is now visible")
    else:
        print(f"❌ Failed to update Page {page_id}")
```

---

## SUMMARY

✅ **14 unique landing pages created** (IDs: 48, 49, 51-62)
❌ **1 problematic page** (ID 50 - has mismatched title/URL)
⚠️ **All pages are currently HIDDEN** - this is why you're getting 404 errors

**To fix 404 errors:** Make the pages visible in BigCommerce admin or run the script above.

---

*Last Updated: November 2024*