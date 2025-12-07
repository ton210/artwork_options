# Quick Deployment Reference - cannabislaw.munchmakers.com

## 🚀 Heroku Deployment Commands

### 1. Initialize & Deploy

```bash
# Create Heroku app
heroku create cannabislaw-munchmakers --region us

# Add PostgreSQL
heroku addons:create heroku-postgresql:essential-0

# Set environment variables
heroku config:set NEXT_PUBLIC_SITE_URL=https://cannabislaw.munchmakers.com
heroku config:set NODE_ENV=production

# Deploy
git push heroku main
```

### 2. Custom Domain Setup

```bash
# Add domain to Heroku
heroku domains:add cannabislaw.munchmakers.com

# Get DNS target (will output something like:)
# cannabislaw.munchmakers.com ⬅ quiet-example-abc123.herokudns.com
heroku domains
```

### 3. DNS Configuration (in your domain registrar)

| Type | Host | Value | TTL |
|------|------|-------|-----|
| CNAME | cannabislaw | [heroku-dns-target].herokudns.com | 3600 |

### 4. Enable SSL

```bash
# Heroku provides free SSL via Let's Encrypt
heroku certs:auto:enable

# Verify SSL status
heroku certs:auto
```

### 5. Database Setup

```bash
# Run migrations
heroku run npx prisma migrate deploy

# Seed database
heroku run npx prisma db seed

# Open Prisma Studio (optional - for debugging)
heroku run npx prisma studio
```

---

## 📁 Required Files for Heroku

### Procfile
```
web: npm run start
release: npx prisma migrate deploy
```

### package.json scripts
```json
{
  "scripts": {
    "dev": "next dev",
    "build": "prisma generate && next build",
    "start": "next start",
    "postinstall": "prisma generate"
  },
  "prisma": {
    "seed": "ts-node --compiler-options {\"module\":\"CommonJS\"} prisma/seed.ts"
  }
}
```

### next.config.js
```javascript
/** @type {import('next').NextConfig} */
const nextConfig = {
  output: 'standalone', // Important for Heroku
}

module.exports = nextConfig
```

---

## 🔍 Useful Commands

```bash
# View logs
heroku logs --tail

# Open app in browser
heroku open

# Check app status
heroku ps

# Run one-off command
heroku run bash

# View database info
heroku pg:info

# Connect to database
heroku pg:psql
```

---

## ✅ Post-Deployment Checklist

- [ ] Verify site loads at cannabislaw.munchmakers.com
- [ ] SSL certificate active (https working)
- [ ] All state pages loading correctly
- [ ] Interactive map functioning
- [ ] Disclaimers visible on all pages
- [ ] Mobile responsive design working
- [ ] Submit sitemap to Google Search Console
- [ ] Submit sitemap to Bing Webmaster Tools
- [ ] Set up Google Analytics
- [ ] Test all internal links
- [ ] Verify MunchMakers cross-promotion links work

---

## 🔗 Important URLs

- **Production Site**: https://cannabislaw.munchmakers.com
- **Sitemap**: https://cannabislaw.munchmakers.com/sitemap.xml
- **Robots.txt**: https://cannabislaw.munchmakers.com/robots.txt
- **Google Search Console**: https://search.google.com/search-console
- **Bing Webmaster**: https://www.bing.com/webmasters

---

## 💰 Heroku Costs (Estimated)

| Resource | Plan | Monthly Cost |
|----------|------|--------------|
| Dyno | Eco | ~$5 |
| PostgreSQL | Essential-0 | ~$5 |
| SSL | Auto (Let's Encrypt) | Free |
| **Total** | | **~$10/month** |

For more traffic, upgrade to Basic dyno ($7) or Standard-1X ($25).
