# Dispensary Rankings Web Application

**Top Dispensaries 2026** - A comprehensive dispensary discovery and ranking platform for dispensaries.munchmakers.com

## Overview

This application provides SEO-optimized dispensary rankings for states and counties where cannabis is legal for both recreational and medicinal use. The platform integrates with Google APIs to gather dispensary data and uses a sophisticated ranking algorithm based on reviews, ratings, user votes, and engagement metrics.

## Features

- 📊 **Smart Rankings**: Composite scoring algorithm based on Google ratings, reviews, votes, and engagement
- 🗳️ **User Voting**: Rankly-style upvote/downvote system with rate limiting
- 📍 **Location-Based**: State and county-level rankings for 24+ legal states
- 🔍 **SEO Optimized**: Server-side rendering, structured data, dynamic sitemaps
- 📱 **Responsive Design**: Mobile-first design with Tailwind CSS
- 📈 **Analytics Tracking**: Page views, clicks, and engagement metrics
- 🛠️ **Admin Dashboard**: Manage listings, trigger scrapes, view analytics
- 🎯 **MunchMakers Integration**: Strategic product placement and lead capture

## Tech Stack

- **Backend**: Node.js + Express
- **Database**: PostgreSQL (Heroku Postgres)
- **Cache**: Redis (Heroku Redis)
- **Views**: EJS templates (server-rendered)
- **CSS**: Tailwind CSS
- **APIs**: Google Places, Custom Search, Geocoding
- **Jobs**: Bull queue system with Heroku Scheduler
- **Deployment**: Heroku

## Quick Start

### Prerequisites

- Node.js 18.x or higher
- PostgreSQL 14+
- Redis 7+
- Google Cloud Platform account with Places API enabled

### Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd dispensary-rankings
```

2. Install dependencies:
```bash
npm install
```

3. Set up environment variables:
```bash
cp .env.example .env
# Edit .env with your actual credentials
```

4. Run database migrations:
```bash
npm run migrate
```

5. Seed initial data (states and counties):
```bash
npm run seed
```

6. Start the development server:
```bash
npm run dev
```

Visit http://localhost:3000

### Initial Data Collection

Run the scraper for a specific state:
```bash
npm run scrape:state -- --state=california
```

Or scrape all legal states:
```bash
npm run scrape:all
```

Calculate rankings:
```bash
npm run rankings:calculate
```

## Deployment to Heroku

### Setup

1. Create a new Heroku app:
```bash
heroku create your-app-name
```

2. Add PostgreSQL and Redis:
```bash
heroku addons:create heroku-postgresql:mini
heroku addons:create heroku-redis:mini
```

3. Set environment variables:
```bash
heroku config:set GOOGLE_PLACES_API_KEY=your_key
heroku config:set GOOGLE_CUSTOM_SEARCH_API_KEY=your_key
heroku config:set GOOGLE_SEARCH_ENGINE_ID=your_id
heroku config:set SESSION_SECRET=$(openssl rand -hex 32)
heroku config:set ADMIN_USERNAME=admin
heroku config:set ADMIN_PASSWORD=your_secure_password
heroku config:set NODE_ENV=production
heroku config:set MUNCHMAKERS_SITE_URL=https://munchmakers.com
```

4. Deploy:
```bash
git push heroku main
```

5. Run migrations:
```bash
heroku run npm run migrate
heroku run npm run seed
```

6. Scale dynos:
```bash
heroku ps:scale web=1 worker=1
```

### Heroku Scheduler Setup

Add the following jobs via Heroku Scheduler:

- **Hourly**: `npm run rankings:calculate`
- **Daily at 2am**: `node src/jobs/refreshRatings.js`
- **Daily at 3am**: `npm run sitemap:generate`
- **Weekly (Sunday 1am)**: `npm run scrape:all`

## Project Structure

```
/dispensary-rankings
├── Procfile                 # Heroku process definitions
├── package.json
├── README.md
├── .env.example
├── /src
│   ├── server.js           # Main Express application
│   ├── worker.js           # Bull queue worker
│   ├── /config             # Database, Redis, API configs
│   ├── /routes             # Express routes
│   ├── /controllers        # Business logic
│   ├── /models             # Database models
│   ├── /services           # External service integrations
│   ├── /jobs               # Background job processors
│   ├── /middleware         # Custom middleware
│   ├── /views              # EJS templates
│   ├── /public             # Static assets
│   ├── /db                 # Migrations and seeds
│   └── /scripts            # CLI scripts
└── /data                   # Static data files
```

## API Endpoints

### Public Routes
- `GET /` - Homepage
- `GET /dispensaries/:state` - State rankings
- `GET /dispensaries/:state/:county` - County rankings
- `GET /dispensary/:slug` - Individual listing

### API Routes
- `POST /api/vote` - Submit vote
- `POST /api/track/view` - Track page view
- `POST /api/track/click` - Track click event
- `POST /api/leads` - Submit lead form

### Admin Routes (Protected)
- `GET /admin` - Dashboard
- `GET /admin/dispensaries` - Manage listings
- `POST /admin/scrape` - Trigger scrape job
- `GET /admin/leads` - View submitted leads

## Ranking Algorithm

Dispensaries are scored 0-100 based on:

| Factor | Weight | Source |
|--------|--------|--------|
| Google rating | 25% | Places API |
| Review volume | 15% | Places API |
| External listings | 10% | Custom Search |
| User votes | 20% | Internal |
| Page views | 10% | Internal |
| Data completeness | 10% | Internal |
| Engagement | 10% | Internal |

Rankings are recalculated hourly to reflect new votes and engagement.

## Legal States Coverage

The application covers 24 states plus D.C. where cannabis is legal for both recreational and medicinal use:

Alaska, Arizona, California, Colorado, Connecticut, Delaware, Illinois, Maine, Maryland, Massachusetts, Michigan, Minnesota, Missouri, Montana, Nevada, New Jersey, New Mexico, New York, Ohio, Oregon, Rhode Island, Vermont, Virginia, Washington, Washington D.C.

## Environment Variables

See `.env.example` for all required environment variables.

## Contributing

This is a proprietary project for MunchMakers. Contact the development team for contribution guidelines.

## License

Proprietary - MunchMakers © 2026

## Support

For issues or questions, contact: support@munchmakers.com
