# Next Session Implementation Plan

## Current Status (As of Dec 6, 2025)

### ✅ COMPLETE & DEPLOYED:
- **Site**: https://bestdispensaries.munchmakers.com
- **Database**: 633 dispensaries live (AZ, MO, IL, NJ) at 98.5% success rate
- **Features**: Rankings, voting, brand pages, Google Maps, complete SEO
- **Background**: 20 states importing (2,310 more dispensaries) via `./run-all-imports.sh`

### 🔄 IN PROGRESS:
- Batch import running in background (check `batch-import-all-states.log`)
- Expected: ~2,800 total dispensaries when complete

---

## PRIORITY FEATURES TO IMPLEMENT

### 1. User Authentication & Accounts
**Database:**
- Create `users` table (id, email, password_hash, name, created_at, verified_email, role)
- Add `user_id` FK to reviews, votes tables

**Features:**
- Registration/login (bcrypt password hashing)
- Email verification
- User profiles with review history
- Session management (already using Redis)

**Files:**
- `src/models/User.js`
- `src/routes/auth.js`
- `src/views/auth/register.ejs`, `login.ejs`, `profile.ejs`
- `src/middleware/requireAuth.js`

---

### 2. User Reviews System
**Database:**
- `reviews` table (id, dispensary_id, user_id, rating 1-5, text, helpful_count, created_at)
- `review_helpfulness` table (review_id, user_id, helpful BOOLEAN)

**Features:**
- Submit reviews (requires login)
- Star rating selector
- Helpful/Not Helpful voting
- Admin moderation
- Add 15% weight to ranking algorithm

**Legal Protection:**
- Auto-moderation for spam/profanity
- User agreement checkbox before submission
- "User-generated content" disclaimer
- Report review functionality

**Files:**
- `src/models/Review.js`
- `src/routes/reviews.js`
- `src/views/partials/reviewForm.ejs`, `reviewList.ejs`
- `src/public/js/reviews.js`

---

### 3. Business Claim System with Email Verification
**Database:**
- `business_claims` table (id, dispensary_id, user_id, business_email, verification_token, status, verified_at)
- Add `claimed_by_user_id`, `is_claimed`, `claim_verified_at` to dispensaries table

**Email Domain Verification:**
```javascript
// Auto-approve if claim email domain matches dispensary website domain
const claimEmailDomain = claimEmail.split('@')[1];
const websiteDomain = new URL(dispensary.website).hostname.replace('www.', '');

if (claimEmailDomain === websiteDomain) {
  // Auto-approve claim
  autoApproveClaim(claimId);
}
```

**Features:**
- Claim listing form
- Send verification email with unique token
- Auto-approve if email domain matches website
- Manual review for non-matching domains
- Claimed badge on listings
- Business dashboard to update info

**Files:**
- `src/models/BusinessClaim.js`
- `src/routes/claim.js`
- `src/services/emailService.js` (use SendGrid or Mailgun)
- `src/views/claim/submit.ejs`, `verify.ejs`, `dashboard.ejs`

---

### 4. Legal Protection & Disclaimers

**CRITICAL - Legal Safety Requirements:**

**A. Update Terms of Service** (`src/views/pages/terms.ejs`):
Add sections on:
- User-generated content disclaimer
- No medical/legal advice
- Information accuracy limitations
- Third-party data sources (Google, Leafly, Weedmaps)
- Age restriction (21+)
- State law compliance responsibility

**B. Update Privacy Policy** (`src/views/pages/privacy.ejs`):
- CCPA/GDPR compliance sections
- Cookie policy
- Data collection transparency
- User rights (delete account, export data)

**C. Create "How Rankings Work" Page** (`src/views/pages/how-rankings-work.ejs`):
```
Our rankings combine multiple factors to provide the most accurate
dispensary recommendations:

• Google Reviews & Ratings - Verified customer experiences
• User Community Votes - Real-time community feedback
• External Listings - Presence on Leafly, Weedmaps, etc.
• Data Completeness - Verified business information
• User Engagement - Page views and interactions

[Link to methodology page with more details but not exact algorithm]
```

**D. Add Rating Tooltip** (All ranking pages):
```html
<span class="inline-flex items-center">
  Composite Score: 8.7/10
  <button class="ml-1 text-gray-400 hover:text-gray-600"
          onclick="showRatingInfo()">
    <svg><!-- info icon --></svg>
  </button>
</span>
```

**E. Prominent Disclaimers on Every Page:**

Add to footer or bottom of rankings:
```
⚠️ DISCLAIMER: Rankings and reviews are opinions based on publicly
available information. Information may be outdated or inaccurate.
Always verify dispensary details directly. We do not sell, distribute,
or facilitate cannabis sales. Must be 21+ and comply with state laws.
```

**F. Data Accuracy Notice** (On all dispensary pages):
```
📋 Information last updated: [DATE]
See something incorrect? [Claim this listing] or [Report an error]
```

**Files to Update:**
- ✏️ `src/views/pages/terms.ejs` - Comprehensive legal terms
- ✏️ `src/views/pages/privacy.ejs` - CCPA/GDPR compliance
- ✏️ Create `src/views/pages/how-rankings-work.ejs`
- ✏️ Create `src/views/pages/methodology.ejs` (detailed but not exact)
- ✏️ `src/views/partials/footer.ejs` - Add disclaimer
- ✏️ All ranking pages - Add rating tooltip
- ✏️ `src/views/dispensary.ejs` - Data accuracy notice

---

### 5. Professional UI Polish (Leafly/Weedmaps Style)

**Design Improvements:**

**A. Enterprise Color Scheme:**
- Primary: #16a34a (cannabis green) - ✅ already using
- Secondary: #0891b2 (trustworthy blue)
- Accent: #f59e0b (call-to-action orange)
- Neutral: Modern grays

**B. Enhanced Typography:**
- Headings: Bolder, more hierarchy
- Body: Increase line-height for readability
- CTA buttons: Larger, more prominent

**C. Card Design:**
- Subtle shadows with hover effects - ✅ partially done
- Rounded corners consistent
- Better spacing/padding
- Photo thumbnails on all cards

**D. Professional Icons:**
- Replace text icons with SVG icons
- Verified badges for claimed listings
- Trust indicators (years in business, etc.)

**E. Loading States:**
- Skeleton loaders instead of blank screens
- Smooth transitions
- Progress indicators

---

### 6. PWA Implementation

**Files Needed:**
- `src/public/manifest.json`
- `src/public/service-worker.js`
- `src/public/icons/` (192x192, 512x512)
- Update `src/views/layouts/main.ejs` with PWA meta tags

---

## LEGAL CHECKLIST

Before going live with reviews/claims:

- [ ] Terms of Service - comprehensive, lawyer-reviewed recommended
- [ ] Privacy Policy - CCPA/GDPR compliant
- [ ] Age gate - 21+ verification
- [ ] Data accuracy disclaimer on every page
- [ ] "Information may be incorrect" notice
- [ ] "Opinions, not facts" for rankings/reviews
- [ ] No medical advice disclaimer
- [ ] Third-party data attribution (Google, Leafly, Weedmaps)
- [ ] User content moderation system
- [ ] Report inappropriate content feature
- [ ] DMCA takedown process
- [ ] Contact information for legal requests

---

## RECOMMENDED NEXT SESSION TASKS:

**Priority Order:**
1. Add legal disclaimers & update terms (CRITICAL for protection)
2. Create "How Rankings Work" page with tooltip
3. Implement user authentication
4. Build review system with moderation
5. Add business claim with email verification
6. Professional UI polish
7. PWA features
8. Calculate final rankings for all imported states

**Commands to Run After Import Completes:**
```bash
# Check import completion
tail -100 batch-import-all-states.log

# Calculate rankings for all dispensaries
heroku run "npm run rankings:calculate" -a bestdispensaries-munchmakers

# Check total count
heroku run "node -e 'const db=require(\"./src/config/database\");(async()=>{const r=await db.query(\"SELECT COUNT(*) FROM dispensaries WHERE is_active=true\");console.log(\"Total:\",r.rows[0].count);await db.pool.end();})();'" -a bestdispensaries-munchmakers
```

---

## KEY FILES REFERENCE:

**Current Structure:**
- Models: `src/models/` (Dispensary, State, County, Brand, Vote, Ranking)
- Routes: `src/routes/` (api, dispensaries, brands, pages, admin)
- Views: `src/views/` (EJS templates)
- Services: `src/services/` (googlePlaces, googleSearch, googleMaps, rankingCalculator)
- Public: `src/public/` (js, css, icons)

**Google API Key:** `AIzaSyCd_kX94LwunGDgupq3eIj9p-RJ3YVi4Tw`
**Search Engine ID:** `f6d3f9a90488a4c0b`

---

This platform is on track to be THE comprehensive cannabis dispensary directory for the entire United States! 🚀
