# Quick Start Guide

## 🎉 Your Application is Ready!

The dispensary rankings application has been fully built with your Google credentials configured.

## ✅ What's Already Done

- ✅ Complete application built
- ✅ Google service account credentials configured
- ✅ API key set in environment variables
- ✅ Database schema created
- ✅ All features implemented

## 📋 What You Need to Do

### 1. Set Up Custom Search Engine (~5 minutes)

You need a Custom Search Engine ID for finding external listings.

1. Go to https://programmablesearchengine.google.com/
2. Click "Add" to create a new search engine
3. Name: "Dispensary External Listings"
4. Search the entire web: **Enable**
5. Click "Create"
6. Copy your Search Engine ID (looks like: `017576662512468239146:omuauf_lfve`)

7. Edit `.env` file and update this line:
   ```
   GOOGLE_SEARCH_ENGINE_ID=paste_your_search_engine_id_here
   ```

📖 Detailed instructions: See `GOOGLE_API_SETUP.md`

### 2. Install Dependencies (~2 minutes)

```bash
cd /Users/tomernahumi/Documents/Plugins/DispensariesMunchmakers
npm install
```

### 3. Set Up Local Database (~5 minutes)

#### Option A: Use Local PostgreSQL

Install PostgreSQL if you don't have it:
```bash
brew install postgresql@14
brew services start postgresql@14
```

Create database:
```bash
createdb dispensary_rankings
```

Update `.env` with your database URL:
```
DATABASE_URL=postgresql://YOUR_USERNAME@localhost:5432/dispensary_rankings
```

#### Option B: Use Heroku Postgres (Recommended for Quick Start)

Skip local database and deploy directly to Heroku (see step 5).

### 4. Run Migrations and Seed Data (~1 minute)

```bash
npm run migrate
npm run seed
```

This creates all tables and loads 25 legal states with hundreds of counties.

### 5. Start the Application (~30 seconds)

```bash
npm run dev
```

Visit: **http://localhost:3000**

Admin panel: **http://localhost:3000/admin**
- Username: `admin`
- Password: `munchmakers2026`

### 6. Test Scraping (Optional - ~5 minutes)

Start with a small state to test:

```bash
# Scrape Alaska (small state, good for testing)
npm run scrape:state -- alaska

# Calculate rankings
npm run rankings:calculate
```

Then visit http://localhost:3000/dispensaries/alaska to see results!

## 🚀 Deploy to Heroku (Production)

### Prerequisites
- Heroku CLI installed: `brew install heroku`
- Heroku account created

### Deploy Steps

```bash
# Login to Heroku
heroku login

# Create app
heroku create dispensary-rankings-munchmakers

# Add Postgres and Redis
heroku addons:create heroku-postgresql:mini
heroku addons:create heroku-redis:mini

# Set environment variables
heroku config:set GOOGLE_PLACES_API_KEY=AQ.Ab8RN6KCplH4yy6tuE2ulMGTcnrBOEUUOwX1OnStyEcoxmqJXw
heroku config:set GOOGLE_CUSTOM_SEARCH_API_KEY=AQ.Ab8RN6KCplH4yy6tuE2ulMGTcnrBOEUUOwX1OnStyEcoxmqJXw
heroku config:set GOOGLE_SEARCH_ENGINE_ID=YOUR_SEARCH_ENGINE_ID
heroku config:set SESSION_SECRET=$(openssl rand -hex 32)
heroku config:set ADMIN_USERNAME=admin
heroku config:set ADMIN_PASSWORD=munchmakers2026
heroku config:set NODE_ENV=production
heroku config:set MUNCHMAKERS_SITE_URL=https://munchmakers.com

# Deploy
git add .
git commit -m "Initial deployment"
git push heroku main

# Set up database
heroku run npm run migrate
heroku run npm run seed

# Scale dynos
heroku ps:scale web=1 worker=1

# Open app
heroku open
```

📖 Full deployment guide: See `DEPLOYMENT.md`

## 🗂️ Project Structure

```
/DispensariesMunchmakers
├── Procfile                 # Heroku config
├── package.json             # Dependencies
├── .env                     # Your API credentials ✅
├── google-credentials.json  # Service account ✅
│
├── /src
│   ├── server.js           # Main app
│   ├── worker.js           # Background jobs
│   ├── /routes             # URL routes
│   ├── /models             # Database models
│   ├── /services           # Google APIs, scraping
│   ├── /views              # HTML templates
│   └── /public             # CSS, JS, images
│
├── QUICK_START.md          # ← You are here!
├── GOOGLE_API_SETUP.md     # Detailed API setup
├── DEPLOYMENT.md           # Full Heroku deployment
└── PROJECT_SUMMARY.md      # Complete project overview
```

## 📊 Features Available

### Public Features
- ✅ Homepage with state listings
- ✅ State ranking pages (Top 10)
- ✅ County ranking pages (All dispensaries)
- ✅ Individual dispensary pages
- ✅ Upvote/downvote system
- ✅ Lead capture forms
- ✅ MunchMakers branding throughout
- ✅ Mobile responsive design
- ✅ SEO optimized

### Admin Features
- ✅ Dashboard with stats
- ✅ Manage dispensaries
- ✅ Trigger scraping jobs
- ✅ Calculate rankings
- ✅ View leads
- ✅ Monitor scrape logs

## 🎯 Next Steps After Setup

1. **Test Local Setup**
   - Browse http://localhost:3000
   - Log in to admin panel
   - Scrape one small state (Alaska or Delaware)
   - Check that rankings calculate correctly

2. **Enable Google APIs**
   - Go to Google Cloud Console
   - Enable Places API (New)
   - Enable Geocoding API
   - Enable Custom Search JSON API
   - See `GOOGLE_API_SETUP.md` for details

3. **Start Small**
   - Scrape 2-3 states initially
   - Monitor API costs
   - Verify data quality

4. **Deploy to Production**
   - Follow Heroku deployment steps above
   - Set up custom domain: dispensaries.munchmakers.com
   - Configure Heroku Scheduler for automated jobs

5. **Full Data Collection**
   - Scrape all 24 states + D.C.
   - Set up daily rating refreshes
   - Monitor and optimize

## 💰 Expected Costs

### Google APIs (After free tier)
- **Initial full scrape**: $200-$300 one-time
- **Monthly maintenance**: $35-$50/month

### Heroku Hosting
- **Hobby tier**: $14/month (web + worker dynos)
- **Postgres Mini**: $5/month
- **Redis Mini**: $3/month
- **Total**: ~$22/month

See `GOOGLE_API_SETUP.md` for detailed cost breakdown.

## 🆘 Troubleshooting

### "Cannot find module" errors
```bash
npm install
```

### Database connection errors
Check your `DATABASE_URL` in `.env`

### API errors
- Make sure APIs are enabled in Google Cloud Console
- Check API key is correct in `.env`
- See `GOOGLE_API_SETUP.md`

### Port already in use
```bash
# Change PORT in .env to different number
PORT=3001
```

## 📞 Support Files

- **API Setup**: `GOOGLE_API_SETUP.md` - Detailed Google Cloud setup
- **Deployment**: `DEPLOYMENT.md` - Full Heroku deployment guide
- **Project Info**: `PROJECT_SUMMARY.md` - Complete feature list
- **Code Docs**: `README.md` - Technical documentation

## ✨ You're All Set!

Your dispensary rankings application is fully built and configured. Just:

1. Get your Custom Search Engine ID
2. Run `npm install`
3. Run `npm run migrate && npm run seed`
4. Run `npm run dev`

Then start scraping dispensaries and building the #1 dispensary directory! 🚀

---

**Built for MunchMakers**
Questions? Check the documentation files above or review the code comments.
