# Google API Setup Guide

## Current Configuration

Your Google API credentials have been configured:

- **Service Account Email**: tomer-914@scrapingjanuary2024.iam.gserviceaccount.com
- **Project ID**: scrapingjanuary2024
- **Credentials File**: `google-credentials.json`
- **API Key**: Configured in `.env`

## Required APIs to Enable

Go to [Google Cloud Console](https://console.cloud.google.com/) and ensure these APIs are enabled for your project `scrapingjanuary2024`:

### 1. Places API (New)
- **API Name**: Places API (New)
- **URL**: https://console.cloud.google.com/marketplace/product/google/places-backend.googleapis.com
- **Used For**: Searching for dispensaries, getting place details, photos
- **Cost**: $0.017 per text search, $0.017 per place details request

### 2. Geocoding API
- **API Name**: Geocoding API
- **URL**: https://console.cloud.google.com/marketplace/product/google/geocoding-backend.googleapis.com
- **Used For**: Normalizing addresses, getting accurate county information
- **Cost**: $0.005 per request

### 3. Custom Search JSON API
- **API Name**: Custom Search JSON API
- **URL**: https://console.cloud.google.com/marketplace/product/google/customsearch.googleapis.com
- **Used For**: Finding external listings (Leafly, Weedmaps)
- **Cost**: Free for first 100 queries/day, then $5 per 1000 queries

## Setting Up Custom Search Engine

You need to create a Google Custom Search Engine to use the Custom Search API:

### Steps:

1. **Go to Programmable Search Engine**
   - Visit: https://programmablesearchengine.google.com/
   - Sign in with your Google account

2. **Create New Search Engine**
   - Click "Add"
   - **Search engine name**: "Dispensary External Listings"
   - **What to search**: Search the entire web
   - **Search settings**: Turn ON "Search the entire web"
   - Click "Create"

3. **Get Your Search Engine ID**
   - After creating, you'll see your Search Engine ID (cx parameter)
   - It looks like: `017576662512468239146:omuauf_lfve`
   - Copy this ID

4. **Update Configuration**
   - Edit `.env` file
   - Replace `GOOGLE_SEARCH_ENGINE_ID=your_search_engine_id_here`
   - With your actual Search Engine ID

5. **Enable Custom Search API**
   - Go to: https://console.cloud.google.com/apis/library/customsearch.googleapis.com
   - Click "Enable"

## API Key Restrictions (Security)

For production, restrict your API key:

1. **Go to Credentials Page**
   - https://console.cloud.google.com/apis/credentials

2. **Edit API Key**
   - Click on your API key
   - Under "Application restrictions":
     - For development: None
     - For production: HTTP referrers (add your domain)

3. **Restrict API Key Usage**
   - Under "API restrictions"
   - Select "Restrict key"
   - Choose:
     - Places API (New)
     - Geocoding API
     - Custom Search JSON API

## Service Account Permissions

Your service account needs these permissions:

1. **Go to IAM & Admin**
   - https://console.cloud.google.com/iam-admin/iam

2. **Find Service Account**
   - Look for: tomer-914@scrapingjanuary2024.iam.gserviceaccount.com

3. **Required Roles**
   - Service Account User
   - Custom Search API User (if available)

## Testing Your Setup

### Test Places API:

```bash
curl "https://maps.googleapis.com/maps/api/place/textsearch/json?query=dispensary+los+angeles&key=YOUR_API_KEY"
```

### Test Geocoding API:

```bash
curl "https://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&key=YOUR_API_KEY"
```

### Test Custom Search API:

```bash
curl "https://www.googleapis.com/customsearch/v1?key=YOUR_API_KEY&cx=YOUR_SEARCH_ENGINE_ID&q=dispensary"
```

## Cost Estimates

Based on scraping all 24 states + D.C.:

### Initial Full Scrape
- **Estimated Dispensaries**: ~5,000
- **Text Searches**: ~500 (for all counties)
- **Place Details**: ~5,000
- **Photos**: ~25,000 (5 per dispensary)
- **Custom Searches**: ~15,000 (3 per dispensary)

**Estimated Cost**: $200-$300 for initial full scrape

### Monthly Maintenance
- **Rating Refresh**: ~500 dispensaries/week = ~2,000/month
- **Place Details**: ~2,000/month
- **Custom Searches**: ~100/day (free tier)

**Estimated Monthly Cost**: $35-$50

## API Quotas

Default quotas (can request increases):

- **Places API**:
  - Queries per day: Unlimited (billed per request)
  - Queries per 100 seconds: 1,000

- **Geocoding API**:
  - Requests per day: Unlimited (billed per request)
  - Requests per second: 50

- **Custom Search API**:
  - Free: 100 queries/day
  - Paid: Up to 10,000 queries/day

## Monitoring Usage

Monitor your API usage at:
https://console.cloud.google.com/apis/dashboard?project=scrapingjanuary2024

### Set Up Billing Alerts

1. Go to: https://console.cloud.google.com/billing
2. Click "Budgets & alerts"
3. Create budget alerts at:
   - $50 (warning)
   - $100 (warning)
   - $200 (alert)

## Troubleshooting

### "API key not valid" Error
- Check that all required APIs are enabled
- Verify API key restrictions aren't too strict
- Make sure you're using the correct API key

### "Quota exceeded" Error
- Check your daily quota usage
- Wait for quota reset (midnight Pacific Time)
- Request quota increase if needed

### "Invalid authentication credentials"
- Verify `google-credentials.json` exists
- Check service account has proper permissions
- Ensure GOOGLE_APPLICATION_CREDENTIALS points to correct file

## Next Steps

1. ✅ Enable all required APIs in Google Cloud Console
2. ⏳ Create Custom Search Engine and get Search Engine ID
3. ⏳ Update `.env` with Search Engine ID
4. ⏳ Test API connections (see Testing section above)
5. ⏳ Start with small test scrape (1 county)
6. ⏳ Monitor costs and adjust as needed

## Support

- **Google Cloud Support**: https://cloud.google.com/support
- **Places API Docs**: https://developers.google.com/maps/documentation/places/web-service
- **Custom Search Docs**: https://developers.google.com/custom-search/v1/overview
