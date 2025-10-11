# 🌐 Custom Domain Setup for cannaverdict.com

Your app is now live at: **https://cannaverdict-magic-cb77a3f96ceb.herokuapp.com/**

## Quick Test URLs

- **Admin Panel**: https://cannaverdict-magic-cb77a3f96ceb.herokuapp.com/admin
- **Spectator Page**: https://cannaverdict-magic-cb77a3f96ceb.herokuapp.com/spec

## Setting Up Custom Domain (cannaverdict.com)

### Option 1: Use Heroku Custom Domain Feature

1. **Add domain to Heroku**:
   ```bash
   cd backend
   heroku domains:add cannaverdict.com
   heroku domains:add www.cannaverdict.com
   ```

2. **Get DNS target from Heroku**:
   ```bash
   heroku domains
   ```

   You'll see output like:
   ```
   === cannaverdict-magic Custom Domains
   Domain Name           DNS Record Type  DNS Target
   ────────────────────  ───────────────  ────────────────────────────────────
   cannaverdict.com      ALIAS or ANAME   cannaverdict-magic-cb77a3f96ceb.herokudns.com
   www.cannaverdict.com  CNAME            cannaverdict-magic-cb77a3f96ceb.herokudns.com
   ```

3. **Update your DNS settings** (at your domain registrar):

   Add these DNS records:

   **For root domain (cannaverdict.com)**:
   - Type: `ALIAS` or `ANAME` (or `CNAME` if using Cloudflare)
   - Name: `@` or leave blank
   - Target: `cannaverdict-magic-cb77a3f96ceb.herokudns.com`

   **For www subdomain**:
   - Type: `CNAME`
   - Name: `www`
   - Target: `cannaverdict-magic-cb77a3f96ceb.herokudns.com`

4. **Wait for DNS propagation** (5 minutes - 48 hours)

5. **Enable Automatic SSL** (free from Heroku):
   ```bash
   heroku certs:auto:enable
   ```

### Option 2: Use Cloudflare (Recommended - Easier)

1. **Add your site to Cloudflare** (free plan is fine)
   - Go to https://dash.cloudflare.com/
   - Click "Add a Site"
   - Enter `cannaverdict.com`

2. **Update your domain's nameservers** to Cloudflare's nameservers

3. **Add DNS records** in Cloudflare:

   ```
   Type: CNAME
   Name: @
   Target: cannaverdict-magic-cb77a3f96ceb.herokuapp.com
   Proxy status: Proxied (orange cloud)
   ```

   ```
   Type: CNAME
   Name: www
   Target: cannaverdict-magic-cb77a3f96ceb.herokuapp.com
   Proxy status: Proxied (orange cloud)
   ```

4. **Enable SSL** in Cloudflare:
   - Go to SSL/TLS → Overview
   - Set to "Full" or "Full (strict)"

5. **Done!** Cloudflare handles everything automatically.

## DNS Records Summary

After setup, your URLs will be:

| URL | Purpose | Used By |
|-----|---------|---------|
| `https://cannaverdict.com/admin` | Control Panel | You (magician) |
| `https://cannaverdict.com/spec` | Spectator Page | Spectator's phone |
| `https://cannaverdict.com/` | Redirects to /admin | - |

## CNAME Record for Your Registrar

**What you need to tell your DNS provider:**

```
Add CNAME record:
  Host/Name: @ (or cannaverdict.com)
  Points to: cannaverdict-magic-cb77a3f96ceb.herokudns.com
  TTL: 3600 (or automatic)

Add CNAME record:
  Host/Name: www
  Points to: cannaverdict-magic-cb77a3f96ceb.herokudns.com
  TTL: 3600 (or automatic)
```

## Common DNS Providers

### GoDaddy
1. Log in → My Products → DNS
2. Add Record → CNAME
3. Name: `@`, Value: `cannaverdict-magic-cb77a3f96ceb.herokudns.com`

### Namecheap
1. Advanced DNS → Add New Record
2. Type: CNAME, Host: `@`, Value: `cannaverdict-magic-cb77a3f96ceb.herokudns.com`

### Google Domains
1. DNS → Custom resource records
2. Name: `@`, Type: CNAME, Data: `cannaverdict-magic-cb77a3f96ceb.herokudns.com`

### Cloudflare
1. DNS → Add record
2. Type: CNAME, Name: `@`, Target: `cannaverdict-magic-cb77a3f96ceb.herokuapp.com`
3. **Important**: Turn ON the orange cloud (Proxied)

## Verifying Setup

After DNS propagation, test with:

```bash
# Check DNS
nslookup cannaverdict.com

# Or
dig cannaverdict.com

# Should point to Heroku
```

Then visit:
- https://cannaverdict.com/admin (your control panel)
- https://cannaverdict.com/spec (spectator page)

## Troubleshooting

**"Application Error" on Heroku?**
```bash
heroku logs --tail
```

**DNS not working?**
- Wait 10-30 minutes for propagation
- Clear your browser cache
- Try incognito mode
- Check DNS with: https://dnschecker.org/

**SSL Certificate errors?**
```bash
heroku certs:auto:enable
heroku certs:auto:refresh
```

## Update URLs in Android App (After Domain Setup)

Edit `MainActivity.kt`:

```kotlin
// Change from:
private var serverUrl = "wss://cannaverdict-magic-cb77a3f96ceb.herokuapp.com"

// To:
private var serverUrl = "wss://cannaverdict.com"
```

```kotlin
// Change NFC URL from:
val url = "https://cannaverdict-magic-cb77a3f96ceb.herokuapp.com/spec"

// To:
val url = "https://cannaverdict.com/spec"
```

Then rebuild the APK.

---

## Current Working URLs (Before Domain Setup)

**Use these RIGHT NOW:**

- Admin: https://cannaverdict-magic-cb77a3f96ceb.herokuapp.com/admin
- Spectator: https://cannaverdict-magic-cb77a3f96ceb.herokuapp.com/spec

These work immediately! Set up the custom domain at your leisure.
