# Google API Keys Setup Guide

## Issue Found
The API key you provided (`AQ.Ab8RN6KCplH4yy6tuE2ulMGTcnrBOEUUOwX1OnStyEcoxmqJXw`) is **NOT a valid Google Cloud API key**.

Valid Google Cloud API keys:
- Start with `AIzaSy`
- Are exactly 39 characters long
- Look like: `AIzaSyDaGmWKa4JsXZ-HjGw7ISLn_3namBGewQe`

## Step-by-Step Setup

### 1. Create/Select Google Cloud Project
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click on the project dropdown at the top
3. Click "New Project" or select an existing project
4. Give it a name like "Dispensaries MunchMakers"

### 2. Enable Required APIs
Go to [APIs & Services > Library](https://console.cloud.google.com/apis/library)

Enable these APIs:
- **Places API (New)** - For finding dispensaries
- **Geocoding API** - For address lookups
- **Custom Search API** - For finding external listings

### 3. Create API Key
1. Go to [APIs & Services > Credentials](https://console.cloud.google.com/apis/credentials)
2. Click "+ CREATE CREDENTIALS" at the top
3. Select "API key"
4. Copy the generated API key (starts with `AIzaSy...`)
5. Click "Restrict Key" (recommended)

### 4. Restrict API Key (Recommended)
**Application restrictions:**
- Choose "HTTP referrers (websites)" for web app
- Add your domains:
  - `bestdispensaries.munchmakers.com/*`
  - `*.herokuapp.com/*` (for testing)

**API restrictions:**
- Select "Restrict key"
- Choose these APIs:
  - Places API (New)
  - Geocoding API
  - Custom Search API

### 5. Create Custom Search Engine
1. Go to [Programmable Search Engine](https://programmablesearchengine.google.com/)
2. Click "Add" or "Create a custom search engine"
3. Configure:
   - **Sites to search:**
     - `leafly.com/*`
     - `weedmaps.com/*`
     - Or select "Search the entire web"
   - **Name:** Dispensary Search
4. Click "Create"
5. Go to "Control Panel" → "Basics"
6. Copy the **Search engine ID** (looks like: `f6d3f9a90488a4c0b`)

### 6. Update Environment Variables

**Local (.env file):**
```bash
GOOGLE_PLACES_API_KEY=AIzaSy...your_actual_key_here
GOOGLE_CUSTOM_SEARCH_API_KEY=AIzaSy...your_actual_key_here
GOOGLE_SEARCH_ENGINE_ID=f6d3f9a90488a4c0b
```

**Heroku:**
```bash
heroku config:set \
  GOOGLE_PLACES_API_KEY="AIzaSy...your_actual_key_here" \
  GOOGLE_CUSTOM_SEARCH_API_KEY="AIzaSy...your_actual_key_here" \
  GOOGLE_SEARCH_ENGINE_ID="f6d3f9a90488a4c0b" \
  -a bestdispensaries-munchmakers
```

### 7. Verify API Keys
Run the test script:
```bash
node test-api.js
```

You should see:
```
✅ Google Places API is working!
✅ Google Custom Search API is working!
```

## API Quotas & Costs

### Places API (New)
- **Free tier:** $200 credit/month
- **Cost:** ~$0.032 per Text Search request
- **Limit:** ~6,250 free searches/month

### Geocoding API
- **Free tier:** $200 credit/month
- **Cost:** $0.005 per request
- **Limit:** ~40,000 free requests/month

### Custom Search API
- **Free tier:** 100 queries/day
- **Paid:** $5 per 1,000 queries (after free tier)

## Troubleshooting

### "API key is invalid"
- Verify the key starts with `AIzaSy`
- Check that the API is enabled in Cloud Console
- Wait a few minutes after creating the key

### "API keys are not supported"
- This means you're using OAuth credentials instead of an API key
- Make sure you created an "API key" not "OAuth 2.0 Client ID"

### "REQUEST_DENIED"
- Check API restrictions on the key
- Verify the API is enabled for your project
- Check your billing is set up (required even for free tier)

## Next Steps
Once you have valid API keys:
1. Update your .env file
2. Update Heroku config vars
3. Run `node test-api.js` to verify
4. Start scraping dispensaries!

## Resources
- [Custom Search JSON API Documentation](https://developers.google.com/custom-search/v1/introduction)
- [API Key Management](https://docs.cloud.google.com/docs/authentication/api-keys)
- [Places API Documentation](https://developers.google.com/maps/documentation/places/web-service/overview)
