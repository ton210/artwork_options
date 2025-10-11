# 🚀 Quick Start Guide

## Test Locally (5 minutes)

### 1. Start the Backend Server

```bash
cd backend
npm install
npm start
```

You should see:
```
🎩 Magic Trick Server Starting...
🚀 Server running on port 3000
📱 PWA available at: http://localhost:3000/magic
```

### 2. Test the PWA in Your Browser

Open your browser to: **http://localhost:3000/magic**

You should see a purple gradient screen with "Phone is ready..."

Open the browser console (F12) to see connection logs.

### 3. Test Effects Manually

Open the browser console and run:

```javascript
// Trigger different effects
triggerEffect('glitch')    // RGB distortion
triggerEffect('crash')     // Fake system crash
triggerEffect('flicker')   // Screen flashing
triggerEffect('shake')     // Screen shake
triggerEffect('static')    // TV noise
triggerEffect('matrix')    // Matrix code rain
triggerEffect('reset')     // Back to normal
```

### 4. Build Android App (Optional for now)

You can test the WebSocket connection with any WebSocket client first.

Use this WebSocket test tool: https://www.websocket.org/echo.html

Connect to: `ws://YOUR_LOCAL_IP:3000` (e.g., `ws://192.168.1.100:3000`)

Send this message to register as controller:
```json
{"type":"register","role":"controller"}
```

Then send effect commands:
```json
{"type":"effect","effect":"glitch"}
```

## Deploy to Heroku (10 minutes)

### 1. Install Heroku CLI

```bash
# macOS
brew install heroku/brew/heroku

# or download from https://devcenter.heroku.com/articles/heroku-cli
```

### 2. Login and Create App

```bash
cd backend
heroku login
heroku create your-magic-trick
```

### 3. Initialize Git and Deploy

```bash
git init
git add .
git commit -m "Initial magic trick deployment"
git push heroku main
```

### 4. Open Your App

```bash
heroku open
```

Your app will be at: `https://your-magic-trick.herokuapp.com`

### 5. Test the Deployed PWA

Visit: `https://your-magic-trick.herokuapp.com/magic`

### 6. Update Android App

Edit `android-controller/app/src/main/java/com/magictrick/nfccontroller/MainActivity.kt`:

Change line 32:
```kotlin
private var serverUrl = "wss://your-magic-trick.herokuapp.com"
```

Also update line 75:
```kotlin
val url = "https://your-magic-trick.herokuapp.com/magic"
```

## Build Android APK

### Option 1: Android Studio (Recommended)

1. Install Android Studio: https://developer.android.com/studio
2. Open Android Studio
3. File → Open → Select `android-controller` folder
4. Wait for Gradle sync (may take a few minutes)
5. Build → Build Bundle(s) / APK(s) → Build APK(s)
6. Find APK in: `app/build/outputs/apk/debug/app-debug.apk`
7. Transfer APK to your Android phone and install

### Option 2: Command Line

```bash
cd android-controller

# First time setup
chmod +x gradlew

# Build
./gradlew assembleDebug

# APK location
# app/build/outputs/apk/debug/app-debug.apk
```

## Test the Complete System

1. **Install** Android app on your phone
2. **Open** the app
3. **Enter** your Heroku URL: `wss://your-magic-trick.herokuapp.com`
4. **Tap** "Connect to Server" → Should show "🟢 Connected"
5. **Open** `https://your-magic-trick.herokuapp.com/magic` on another phone/browser
6. **Tap** effect buttons on your controller app
7. **Watch** effects appear on the other device!

## NFC Testing

1. Open controller app on your Android phone
2. Open `https://your-magic-trick.herokuapp.com/magic` on spectator's phone
3. Hold phones **back to back** (where NFC antennas are)
4. Both phones should be **unlocked** with **screens on**
5. You should see "✅ URL sent via NFC!" toast on your phone
6. Spectator's browser should open the URL automatically

**Note**: NFC might not work on all devices/browsers. Alternative: Share the URL via QR code or messaging.

## Troubleshooting

### "Cannot connect to server"
- Make sure backend is running
- Check URL format: `ws://` for local, `wss://` for Heroku
- For local testing, use your computer's IP (not localhost)
  - Find IP: Run `ipconfig getifaddr en0` (macOS) or `ipconfig` (Windows)

### "NFC not working"
- Enable NFC in Settings → Connected Devices → Connection Preferences
- Try different phone positions (NFC antenna locations vary)
- Screen must be on and unlocked
- Not all Android phones have NFC

### "Effects not showing"
- Check browser console for errors (F12)
- Verify spectator's browser connected to WebSocket
- Try refreshing the PWA page
- Make sure you clicked "Connect to Server" first

### "Android app won't build"
- Make sure you have Android Studio installed
- Check you have Android SDK API level 34
- Try: Build → Clean Project, then Build → Rebuild Project
- Run `./gradlew clean` then `./gradlew assembleDebug`

## Next Steps

Once everything works:

1. **Practice** the performance flow
2. **Customize** effects (edit `pwa/effects.js`)
3. **Add more** effects
4. **Create sequences** (chains of effects)
5. **Add sound effects** (update `effects.js` playBeep functions)

---

**Need help?** Check the main README.md for detailed documentation.
