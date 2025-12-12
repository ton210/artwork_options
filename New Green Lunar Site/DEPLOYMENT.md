# Deployment Guide

## Quick Start

Your Green Lunar website is ready to deploy! Here are the recommended deployment options:

### Option 1: Vercel (Recommended)

Vercel is optimized for Next.js and offers the best performance.

#### Deploy via CLI:
```bash
# Install Vercel CLI
npm install -g vercel

# Deploy
cd "/Users/tomernahumi/Documents/Plugins/New Green Lunar Site"
vercel
```

#### Deploy via Git:
1. Push your code to GitHub
2. Visit [vercel.com](https://vercel.com)
3. Click "New Project"
4. Import your GitHub repository
5. Vercel will auto-detect Next.js and deploy!

### Option 2: Heroku

```bash
# Create a new Heroku app
heroku create green-lunar-website

# Push to Heroku
git push heroku main

# Open the app
heroku open
```

## Local Testing

Test the production build locally before deploying:

```bash
npm run build
npm start
```

Visit http://localhost:3000

## Environment Variables

If you add environment variables (e.g., for contact form integration), add them to:

- **Vercel:** Project Settings → Environment Variables
- **Heroku:** `heroku config:set VARIABLE_NAME=value`

## Custom Domain

### Vercel:
1. Go to Project Settings → Domains
2. Add your domain (e.g., www.greenlunar.com)
3. Follow DNS configuration instructions

### Heroku:
```bash
heroku domains:add www.greenlunar.com
```

## Post-Deployment Checklist

- [ ] Verify all pages load correctly
- [ ] Test navigation and links
- [ ] Test contact form submission
- [ ] Check mobile responsiveness
- [ ] Verify logo and images display
- [ ] Test on different browsers
- [ ] Submit sitemap to Google Search Console
- [ ] Set up Google Analytics (optional)

## Next Steps

### To Add Team Photos:
1. Add photos to `public/images/team/`
   - tomer-nahumi.jpg
   - giann-nathaniel.jpg
   - pamela-mamales.jpg
2. Update TeamPreview.tsx and team/page.tsx to use Image component instead of initials

### To Add App Screenshots:
1. Add screenshots to `public/images/apps/`
   - algoboost-hero.png
   - celebration-hero.png
2. Images will automatically appear in the apps sections

### To Enable Contact Form Submissions:
Currently, the form logs to console. To enable real submissions:
1. Use a service like Formspree, SendGrid, or create an API route
2. Update `components/contact/ContactForm.tsx` with your implementation

## Monitoring

After deployment, monitor:
- Page load speeds (aim for <2 seconds)
- Error logs in Vercel/Heroku dashboard
- Analytics and user behavior

---

Your website is built and ready to launch! 🚀
