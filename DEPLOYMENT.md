# Deployment Guide for Dispensary Rankings Application

## Prerequisites

- Node.js 18.x or higher
- PostgreSQL 14+
- Redis 7+
- Heroku CLI installed
- Google Cloud Platform account with APIs enabled

## Local Development Setup

### 1. Install Dependencies

```bash
npm install
```

### 2. Set Up Environment Variables

Copy the example environment file:

```bash
cp .env.example .env
```

Edit `.env` and add your credentials:

```env
DATABASE_URL=postgresql://user:password@localhost:5432/dispensary_rankings
REDIS_URL=redis://localhost:6379
GOOGLE_PLACES_API_KEY=your_key_here
GOOGLE_CUSTOM_SEARCH_API_KEY=your_key_here
GOOGLE_SEARCH_ENGINE_ID=your_id_here
SESSION_SECRET=your_random_secret_here
ADMIN_USERNAME=admin
ADMIN_PASSWORD=your_secure_password
```

### 3. Set Up Database

Run migrations:

```bash
npm run migrate
```

Seed initial data (states and counties):

```bash
npm run seed
```

### 4. Start Development Server

```bash
npm run dev
```

Visit http://localhost:3000

## Heroku Deployment

### 1. Create Heroku App

```bash
heroku create dispensary-rankings-app
```

### 2. Add Required Add-ons

```bash
# PostgreSQL
heroku addons:create heroku-postgresql:mini

# Redis
heroku addons:create heroku-redis:mini
```

### 3. Set Environment Variables

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

### 4. Deploy to Heroku

```bash
git push heroku main
```

### 5. Run Database Migrations

```bash
heroku run npm run migrate
heroku run npm run seed
```

### 6. Scale Dynos

```bash
# Web dyno
heroku ps:scale web=1

# Worker dyno (for background jobs)
heroku ps:scale worker=1
```

### 7. Set Up Heroku Scheduler

Install the scheduler add-on:

```bash
heroku addons:create scheduler:standard
```

Open the scheduler dashboard:

```bash
heroku addons:open scheduler
```

Add the following jobs:

**Hourly:**
```
npm run rankings:calculate
```

**Daily at 2:00 AM UTC:**
```
node src/jobs/refreshRatings.js
```

**Daily at 3:00 AM UTC:**
```
npm run sitemap:generate
```

**Weekly (Sunday at 1:00 AM UTC):**
```
npm run scrape:all
```

### 8. Configure Custom Domain (Optional)

```bash
heroku domains:add dispensaries.munchmakers.com
```

Follow Heroku's instructions to configure your DNS.

## Initial Data Collection

### Scrape a Single State

```bash
heroku run node src/scripts/scrapeState.js california
```

### Scrape All States (Takes a long time!)

```bash
heroku run node src/scripts/scrapeAll.js
```

### Calculate Rankings

```bash
heroku run npm run rankings:calculate
```

## Monitoring

### View Logs

```bash
heroku logs --tail
```

### View App Info

```bash
heroku info
```

### Check Dyno Status

```bash
heroku ps
```

## Troubleshooting

### Database Connection Issues

Check database URL:
```bash
heroku config:get DATABASE_URL
```

Test database connection:
```bash
heroku pg:info
```

### Redis Connection Issues

Check Redis URL:
```bash
heroku config:get REDIS_URL
```

Test Redis connection:
```bash
heroku redis:info
```

### Application Errors

View detailed logs:
```bash
heroku logs --tail --source app
```

### Worker Not Processing Jobs

Check worker status:
```bash
heroku ps:scale worker=1
heroku logs --tail --dyno worker
```

## Performance Optimization

### Database Indexes

All necessary indexes are created during migration. To verify:

```bash
heroku pg:psql
\d+ dispensaries
```

### Redis Caching

The application uses Redis for:
- Session storage
- Rate limiting
- Job queues (Bull)

### CDN for Static Assets

For production, consider using a CDN for:
- CSS/JS files
- Images
- Fonts

## Security Checklist

- [ ] Strong `ADMIN_PASSWORD` set
- [ ] `SESSION_SECRET` is random and secure
- [ ] Google API keys restricted by referrer/IP
- [ ] SSL/HTTPS enabled (automatic on Heroku)
- [ ] Rate limiting configured
- [ ] Input validation on all forms
- [ ] SQL injection prevention (parameterized queries)
- [ ] XSS prevention (EJS auto-escaping)

## Backup and Recovery

### Database Backups

Heroku automatically creates daily backups with the Postgres add-on.

Manual backup:
```bash
heroku pg:backups:capture
heroku pg:backups:download
```

### Restore from Backup

```bash
heroku pg:backups:restore b001 DATABASE_URL
```

## Scaling

### Vertical Scaling (Larger Dynos)

```bash
heroku ps:scale web=1:standard-2x
```

### Horizontal Scaling (More Dynos)

```bash
heroku ps:scale web=2 worker=2
```

### Database Scaling

Upgrade database plan:
```bash
heroku addons:upgrade heroku-postgresql:standard-0
```

## Support

For issues or questions:
- Check application logs: `heroku logs --tail`
- Review Heroku status: https://status.heroku.com
- Contact development team

## Maintenance

### Regular Tasks

- Monitor API usage quotas (Google Places)
- Review and respond to leads weekly
- Check scrape logs for errors
- Update dependencies monthly
- Review analytics and rankings weekly

### Monthly Checklist

- [ ] Review Google API costs
- [ ] Check database size and cleanup old logs
- [ ] Review and archive old leads
- [ ] Update rankings algorithm if needed
- [ ] Check for security updates
