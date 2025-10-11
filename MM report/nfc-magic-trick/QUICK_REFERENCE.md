# 🎩 Quick Reference Guide

## URLs

### Your Live App:
- **Admin Panel**: https://cannaverdict-magic-cb77a3f96ceb.herokuapp.com/admin
- **Spectator Page**: https://cannaverdict-magic-cb77a3f96ceb.herokuapp.com/spec
- **Health Check**: https://cannaverdict-magic-cb77a3f96ceb.herokuapp.com/health

### After Domain Setup (cannaverdict.com):
- **Admin**: https://cannaverdict.com/admin
- **Spectator**: https://cannaverdict.com/spec

## DNS Setup for cannaverdict.com

### What to add at your DNS provider:

```
Type: CNAME
Name: @ (or cannaverdict.com)
Points to: cannaverdict-magic-cb77a3f96ceb.herokudns.com
TTL: 3600
```

```
Type: CNAME
Name: www
Points to: cannaverdict-magic-cb77a3f96ceb.herokudns.com
TTL: 3600
```

**Or if using Cloudflare:**
```
Type: CNAME
Name: @
Target: cannaverdict-magic-cb77a3f96ceb.herokuapp.com
Proxy: ON (orange cloud)
```

## Quick Performance

### 1. Open Admin Panel
https://cannaverdict-magic-cb77a3f96ceb.herokuapp.com/admin

### 2. Send Spectator Link
Share this with your victim:
```
https://cannaverdict-magic-cb77a3f96ceb.herokuapp.com/spec
```

Or show them the QR code on admin panel.

### 3. Trigger Effects
- Click buttons on admin panel
- Or enable **Auto Mode** for hands-free

## Effects List

| Button | Effect | Description |
|--------|--------|-------------|
| 📺 Glitch | Visual glitch | RGB distortion, scan lines |
| 💥 Crash | System error | Fake "System UI stopped" dialog |
| ⚡ Flicker | Screen flash | Rapid black/white flashing |
| 📳 Shake | Screen shake | Shaking animation + vibration |
| 📡 Static | TV noise | White noise visual + audio |
| 🟢 Matrix | Code rain | Green falling characters |
| 🖼️ Gallery | Photo scroll | Simulated photo gallery scrolling |
| 📱 Apps | Random apps | Apps appear to open randomly |
| 🔄 Reset | Normal | Return to normal screen |

## Keyboard Shortcuts (Admin Panel)

| Key | Action |
|-----|--------|
| 1 | Glitch |
| 2 | Crash |
| 3 | Flicker |
| 4 | Shake |
| 5 | Static |
| 6 | Matrix |
| 7 | Gallery |
| 8 | Apps |
| 0 | Reset |
| Space | Toggle Auto Mode |

## Auto Mode Settings

**Default:** 2-5 seconds between effects

**To change:**
1. Go to admin panel
2. Find "Auto Mode" section
3. Adjust "Min" and "Max" delay inputs
4. Click "Start Auto Mode"

## Heroku Commands

```bash
# View logs
heroku logs --tail -a cannaverdict-magic

# Check status
heroku ps -a cannaverdict-magic

# Restart app
heroku restart -a cannaverdict-magic

# Open app
heroku open -a cannaverdict-magic

# Add custom domain
heroku domains:add cannaverdict.com -a cannaverdict-magic

# View domains
heroku domains -a cannaverdict-magic

# Enable SSL
heroku certs:auto:enable -a cannaverdict-magic
```

## File Locations

```
nfc-magic-trick/
├── backend/
│   ├── server.js           # WebSocket server
│   ├── package.json        # Dependencies
│   └── Procfile           # Heroku config
│
├── pwa/
│   ├── admin.html         # Admin control panel
│   ├── admin.js           # Admin logic
│   ├── spectator.html     # Spectator view
│   ├── app.js             # Spectator logic
│   ├── effects.js         # Visual effects
│   └── styles.css         # All styles
│
└── android-controller/     # Android NFC app (optional)
```

## Common Issues

| Problem | Solution |
|---------|----------|
| Can't connect | Check Heroku app is running |
| Effects not working | Refresh spectator page |
| DNS not resolving | Wait 10-30 min, clear cache |
| App sleeping | Upgrade to Hobby dyno or use UptimeRobot |

## Performance Flow

```
1. Open admin panel on your device
2. Share spectator link with victim
3. Wait for "Spectator connected" notification
4. Enable Auto Mode OR click effect buttons
5. Watch their phone go crazy
6. Click Reset to restore normal
```

## Customization Quick Edits

**Add new effect:**
1. Edit `pwa/effects.js` - add method
2. Edit `pwa/app.js` - add case statement
3. Edit `pwa/admin.html` - add button

**Change colors:**
- Edit `pwa/styles.css`

**Change auto mode timing:**
- Edit `pwa/admin.js` line 128

## Support

- Full docs: `README.md`
- Domain setup: `DOMAIN_SETUP.md`
- Deployment info: `DEPLOYMENT_COMPLETE.md`
- Quick start: `QUICKSTART.md`

---

## Ready to Go! 🚀

**Admin Panel**: https://cannaverdict-magic-cb77a3f96ceb.herokuapp.com/admin

**Share This**: https://cannaverdict-magic-cb77a3f96ceb.herokuapp.com/spec
