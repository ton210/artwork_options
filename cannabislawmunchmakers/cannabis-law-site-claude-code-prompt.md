# Cannabis Law Website - Claude Code Build Prompt

## Project Overview

Build a SEO-optimized, interactive cannabis law resource website at `cannabislaw.munchmakers.com` that serves as an authoritative guide to US cannabis regulations while cross-promoting MunchMakers custom smoke accessories.

---

## 🎯 PROMPT FOR CLAUDE CODE

```
Build a complete cannabis law resource website with the following specifications:

## Tech Stack
- Framework: Next.js 14 (App Router) with TypeScript
- Styling: Tailwind CSS
- Database: PostgreSQL (for state law data - easy updates)
- ORM: Prisma
- Deployment: Heroku
- Map: React Simple Maps or react-usa-map with custom SVG

## Core Features

### 1. Interactive US Map (Homepage Hero)
- SVG-based clickable US map
- Color-coded states:
  - Green: Recreational + Medical legal
  - Light Green: Medical only
  - Yellow: CBD/Low-THC only
  - Gray: Fully illegal
- Hover effects showing state name + quick status
- Click navigates to /states/[state-slug]
- Mobile-responsive (list view fallback on small screens)

### 2. Individual State Pages (/states/[state-slug])
Create 50 state pages + DC with:
- State name, flag/outline graphic
- Legal Status Badge (Recreational/Medical/CBD Only/Illegal)
- Possession Limits (recreational & medical)
- Purchase Limits
- Home Cultivation rules
- Age Requirements
- Where to Buy (dispensary info)
- Penalties for violations
- Recent law changes/pending legislation
- Medical program details (qualifying conditions, how to apply)
- Last Updated date (important for trust)
- Sources/citations section

### 3. SEO Structure

#### URL Structure:
- / (homepage with map)
- /states/ (alphabetical state listing)
- /states/california/ (individual state)
- /recreational-states/ (list of rec-legal states)
- /medical-states/ (list of medical states)
- /guides/traveling-with-cannabis/
- /guides/medical-marijuana-card/
- /guides/cannabis-for-beginners/
- /news/ (optional blog for updates)

#### Meta Tags (per page):
- Unique title: "[State] Cannabis Laws 2025 - Recreational & Medical Marijuana Guide"
- Meta description: 150-160 chars, include state name, legal status, key limits
- Open Graph tags for social sharing
- Twitter Card meta tags
- Canonical URLs

#### Schema.org Structured Data:
- FAQPage schema on each state page
- BreadcrumbList schema
- Article schema for guides
- Organization schema (MunchMakers)
- WebSite schema with SearchAction

#### Technical SEO:
- Generate sitemap.xml dynamically (include all state pages, guides)
- robots.txt allowing all crawlers
- Fast Core Web Vitals (lazy load map, optimize images)
- Mobile-first responsive design
- Internal linking strategy (link between related states, guides)

### 4. Legal Disclaimer System

#### Persistent Footer Disclaimer:
"LEGAL DISCLAIMER: The information provided on this website is for general informational purposes only and does not constitute legal advice. We are not attorneys, and nothing on this site should be construed as legal counsel. Cannabis laws change frequently and vary by jurisdiction. Always verify current laws with official state resources or consult a licensed attorney before making decisions. MunchMakers LLC assumes no liability for actions taken based on this information."

#### State Page Disclaimer Banner:
Each state page should have a dismissible banner at top:
"⚠️ Laws change frequently. Information last verified [DATE]. Always confirm with official state sources before purchasing or traveling with cannabis."

#### Terms of Use Page (/terms):
Full legal terms including:
- No attorney-client relationship
- Information may be outdated
- User assumes all risk
- Links to official state resources
- Limitation of liability

### 5. MunchMakers Cross-Promotion (Subtle, Value-Added)

#### Contextual CTAs:
- On recreational state pages: "Elevate your experience with custom accessories from MunchMakers"
- On guides: "Need quality gear? MunchMakers offers custom grinders, rolling trays, and more for dispensaries and enthusiasts"
- Sidebar widget: "Custom Accessories for Cannabis Businesses" with link to munchmakers.com

#### Footer Integration:
- "A resource by MunchMakers - Custom Cannabis Accessories for Dispensaries & Brands"
- Links to munchmakers.com
- Social media links

#### Header:
- Small "Powered by MunchMakers" badge linking to main site

### 6. Database Schema (Prisma)

```prisma
model State {
  id                    String   @id @default(cuid())
  name                  String   @unique
  slug                  String   @unique
  abbreviation          String   @unique
  legalStatus           LegalStatus
  
  // Recreational
  recLegal              Boolean  @default(false)
  recPossessionLimit    String?
  recPurchaseLimit      String?
  recHomeCultivation    String?
  recMinAge             Int?
  recEffectiveDate      DateTime?
  
  // Medical
  medLegal              Boolean  @default(false)
  medPossessionLimit    String?
  medPurchaseLimit      String?
  medHomeCultivation    String?
  medMinAge             Int?
  medQualifyingConditions String?
  medHowToApply         String?
  
  // CBD/Other
  cbdOnlyProgram        Boolean  @default(false)
  cbdDetails            String?
  
  // Penalties
  penaltiesOverview     String?
  
  // Meta
  summaryText           String?  // For meta descriptions
  fullDescription       String?  // Rich content
  officialResourceUrl   String?
  lastVerified          DateTime @default(now())
  lastUpdated           DateTime @updatedAt
  
  faqs                  FAQ[]
  sources               Source[]
}

enum LegalStatus {
  RECREATIONAL
  MEDICAL
  CBD_ONLY
  DECRIMINALIZED
  ILLEGAL
}

model FAQ {
  id        String @id @default(cuid())
  stateId   String
  state     State  @relation(fields: [stateId], references: [id])
  question  String
  answer    String
  order     Int    @default(0)
}

model Source {
  id        String @id @default(cuid())
  stateId   String
  state     State  @relation(fields: [stateId], references: [id])
  title     String
  url       String
  accessDate DateTime @default(now())
}
```

### 7. Content Requirements

#### Homepage:
- H1: "Cannabis Laws by State - 2025 Guide to Marijuana Legalization"
- Brief intro paragraph (what the site offers)
- Interactive map
- Quick stats: "24 states recreational, 40+ medical, etc."
- Recent updates section
- Links to popular guides

#### Each State Page Content Sections:
1. Quick Facts Box (status, limits at a glance)
2. Detailed Overview
3. Recreational Laws (if applicable)
4. Medical Program Details (if applicable)
5. Penalties & Enforcement
6. Recent Changes & Pending Legislation
7. FAQ Section (3-5 common questions)
8. Official Resources Links
9. Related States (neighboring states links)

### 8. Design Direction

- Clean, professional, trustworthy aesthetic
- Color palette: Greens (cannabis association), with neutral grays/whites
- Typography: Clear, readable (not playful - this is legal info)
- Prominent disclaimers without being obnoxious
- Easy navigation
- Fast loading
- Accessible (WCAG 2.1 AA)

### 9. File Structure

```
cannabis-law-site/
├── app/
│   ├── layout.tsx
│   ├── page.tsx (homepage with map)
│   ├── states/
│   │   ├── page.tsx (state listing)
│   │   └── [slug]/
│   │       └── page.tsx (individual state)
│   ├── recreational-states/
│   │   └── page.tsx
│   ├── medical-states/
│   │   └── page.tsx
│   ├── guides/
│   │   ├── page.tsx
│   │   ├── traveling-with-cannabis/
│   │   ├── medical-marijuana-card/
│   │   └── cannabis-for-beginners/
│   ├── terms/
│   │   └── page.tsx
│   ├── privacy/
│   │   └── page.tsx
│   ├── sitemap.ts (dynamic sitemap generation)
│   └── robots.ts
├── components/
│   ├── USMap.tsx
│   ├── StateCard.tsx
│   ├── LegalStatusBadge.tsx
│   ├── DisclaimerBanner.tsx
│   ├── Footer.tsx
│   ├── Header.tsx
│   ├── MunchmakersCTA.tsx
│   ├── FAQSection.tsx
│   └── SourcesList.tsx
├── lib/
│   ├── prisma.ts
│   ├── stateData.ts (seed data)
│   └── seo.ts (helper functions)
├── prisma/
│   ├── schema.prisma
│   └── seed.ts
├── public/
│   ├── images/
│   │   └── state-flags/
│   └── og-image.png
├── styles/
│   └── globals.css
├── package.json
├── Procfile (for Heroku)
├── next.config.js
└── tsconfig.json
```

## Deployment to Heroku

1. Create Heroku app:
```bash
heroku create cannabislaw-munchmakers
```

2. Add PostgreSQL:
```bash
heroku addons:create heroku-postgresql:essential-0
```

3. Set environment variables:
```bash
heroku config:set DATABASE_URL=<auto-set-by-addon>
heroku config:set NEXT_PUBLIC_SITE_URL=https://cannabislaw.munchmakers.com
```

4. Add Procfile:
```
web: npm run start
release: npx prisma migrate deploy && npx prisma db seed
```

5. Deploy:
```bash
git push heroku main
```

6. Add custom domain:
```bash
heroku domains:add cannabislaw.munchmakers.com
```
This will give you a DNS target. Create a CNAME record pointing cannabislaw.munchmakers.com to that target.

7. Enable SSL:
```bash
heroku certs:auto:enable
```

## Data Population Strategy

Create a comprehensive seed file with current data for all 50 states + DC. Research and include:

### Recreational Legal States (24):
Alaska, Arizona, California, Colorado, Connecticut, Delaware, Illinois, Maine, Maryland, Massachusetts, Michigan, Minnesota, Missouri, Montana, Nevada, New Jersey, New Mexico, New York, Ohio, Oregon, Rhode Island, Vermont, Virginia, Washington + DC

### Medical Only States (16+):
Alabama, Arkansas, Florida, Hawaii, Kentucky, Louisiana, Mississippi, New Hampshire, North Dakota, Oklahoma, Pennsylvania, South Dakota, Utah, West Virginia, etc.

### CBD Only States:
Georgia, Indiana, Iowa, Texas, Wisconsin, Wyoming

### Fully Illegal:
Idaho, Kansas, Nebraska, South Carolina

For each state, research:
- Current possession limits
- Purchase limits  
- Home grow rules
- Medical qualifying conditions
- Official state cannabis authority website
- Recent law changes

Use authoritative sources:
- NCSL (ncsl.org)
- NORML (norml.org)
- State government websites
- MPP (Marijuana Policy Project)

## SEO Content Priorities

### High-Priority Pages (create detailed content):
1. California, Colorado, New York, Florida, Texas (highest search volume)
2. Recreational states category page
3. Medical marijuana card guide
4. Traveling with cannabis guide

### Long-Tail Keywords to Target:
- "[state] marijuana laws 2025"
- "is weed legal in [state]"
- "[state] cannabis possession limit"
- "how to get medical card in [state]"
- "[state] dispensary rules"
- "can I grow weed in [state]"
- "[state] cannabis penalties"

### Internal Linking Strategy:
- Link to neighboring states from each state page
- Link from recreational/medical category pages to individual states
- Link from guides to relevant state pages
- Cross-link between related guides

```

---

## 📋 Implementation Checklist

### Phase 1: Foundation
- [ ] Initialize Next.js project with TypeScript
- [ ] Set up Tailwind CSS
- [ ] Configure Prisma with PostgreSQL
- [ ] Create database schema
- [ ] Build basic layout (header, footer, navigation)

### Phase 2: Core Features
- [ ] Build interactive US map component
- [ ] Create state listing page
- [ ] Build individual state page template
- [ ] Implement dynamic routing for states
- [ ] Add legal status badges and color coding

### Phase 3: Content & Data
- [ ] Research and compile data for all 50 states + DC
- [ ] Create seed file with all state data
- [ ] Write FAQ content for each state
- [ ] Create guide content pages
- [ ] Add sources/citations

### Phase 4: SEO Implementation
- [ ] Add meta tags to all pages
- [ ] Implement structured data (FAQ, Breadcrumb, etc.)
- [ ] Create dynamic sitemap
- [ ] Configure robots.txt
- [ ] Optimize images and Core Web Vitals
- [ ] Add canonical URLs

### Phase 5: Legal & Cross-Promo
- [ ] Add disclaimer banner component
- [ ] Create terms of use page
- [ ] Create privacy policy page
- [ ] Add MunchMakers CTAs
- [ ] Implement footer with cross-promotion

### Phase 6: Deployment
- [ ] Create Heroku app
- [ ] Add PostgreSQL addon
- [ ] Configure environment variables
- [ ] Set up Procfile
- [ ] Deploy to Heroku
- [ ] Add custom domain
- [ ] Enable SSL
- [ ] Test all pages

### Phase 7: Launch
- [ ] Submit sitemap to Google Search Console
- [ ] Submit sitemap to Bing Webmaster Tools
- [ ] Set up Google Analytics
- [ ] Test all interactive elements
- [ ] Verify all disclaimers are visible
- [ ] Cross-browser testing

---

## 🔧 DNS Configuration

Once deployed to Heroku, you'll get a DNS target like:
`quiet-example-abc123def456.herokudns.com`

In your domain registrar (where munchmakers.com is registered), add:

```
Type: CNAME
Host: cannabislaw
Value: [your-heroku-dns-target]
TTL: 3600
```

---

## 📊 Ongoing Maintenance

This site will need regular updates as laws change. Consider:

1. **Monthly Reviews**: Check for law changes in key states
2. **News Monitoring**: Set up Google Alerts for "cannabis law" + state names
3. **Admin Panel** (future): Build a simple admin to update state data without code deploys
4. **Last Verified Date**: Always update this when reviewing a state's page

---

## 🎨 Design Mockup Notes

### Color Palette:
- Primary Green: #22C55E (recreational)
- Secondary Green: #86EFAC (medical)
- Warning Yellow: #FCD34D (CBD only)
- Neutral Gray: #6B7280 (illegal)
- Background: #F9FAFB
- Text: #111827

### Typography:
- Headings: Inter or DM Sans (clean, professional)
- Body: System font stack for speed

### Map Interaction:
- Default: Show all states with status colors
- Hover: Slight scale up, tooltip with state name + status
- Click: Navigate to state page
- Mobile: Stack as scrollable list with search/filter

---

## 💡 Future Enhancements

1. **State Comparison Tool**: Compare laws between 2-3 states
2. **Email Newsletter**: "Law change alerts"
3. **Business Section**: B2B info for dispensaries (ties to MunchMakers)
4. **Reciprocity Checker**: "Can I use my CA medical card in NV?"
5. **Interactive Quiz**: "Can I legally do X in my state?"

