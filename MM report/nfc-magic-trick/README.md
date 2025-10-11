# 🎩 NFC Magic Trick System

A complete system for performing phone magic tricks using NFC technology. Tap your phone to a spectator's phone, and remotely control crazy visual effects on their screen!

## 🎯 How It Works

1. **Your Phone** (Android Controller App)
   - Send a URL to the spectator's phone via NFC
   - Control effects with buttons or auto-mode
   - Communicates with backend via WebSocket

2. **Spectator's Phone** (Any device with browser)
   - Receives NFC beam → opens URL automatically
   - Browser loads PWA in fullscreen
   - Displays visual effects controlled by you

3. **Backend Server** (Heroku)
   - Routes commands from your phone to spectator's browser
   - WebSocket server for real-time communication

## ✨ Available Effects

- **Glitch** - RGB distortion, scan lines, visual corruption
- **Crash** - Fake Android system crash dialog
- **Flicker** - Rapid black/white screen flashing
- **Shake** - Screen shake with vibration
- **Static** - TV static noise effect
- **Matrix** - Matrix-style falling code
- **Reset** - Return to normal screen

## 🚀 Deployment Instructions

### Deploy Backend to Heroku

1. **Install Heroku CLI**
   ```bash
   brew install heroku/brew/heroku
   ```

2. **Login to Heroku**
   ```bash
   heroku login
   ```

3. **Create Heroku App**
   ```bash
   cd backend
   heroku create your-magic-trick-app
   ```

4. **Deploy**
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git push heroku main
   ```

5. **Open Your App**
   ```bash
   heroku open
   ```

6. **Your app will be at:** `https://your-magic-trick-app.herokuapp.com`

### Alternative: Deploy to Railway/Render

**Railway:**
```bash
npm install -g @railway/cli
railway login
railway init
railway up
```

**Render:**
- Connect your GitHub repo
- Select "Web Service"
- Build command: `npm install`
- Start command: `npm start`

## 📱 Building the Android App

### Option 1: Android Studio

1. Open Android Studio
2. File → Open → Select `android-controller` folder
3. Wait for Gradle sync
4. Update the server URL in `MainActivity.kt`:
   ```kotlin
   private var serverUrl = "wss://your-magic-trick-app.herokuapp.com"
   ```
5. Build → Generate Signed Bundle/APK
6. Install APK on your Android phone

### Option 2: Command Line

```bash
cd android-controller
./gradlew assembleRelease
```

APK will be in: `app/build/outputs/apk/release/`

## 🎬 How to Perform the Trick

### Setup:
1. Deploy backend to Heroku (get your URL)
2. Build Android app with your Heroku URL
3. Install app on your phone
4. Open the app and connect to server

### Performance:
1. **Open your controller app**
2. **Tap "Connect to Server"** (verify green status)
3. **Approach spectator**: "Let me show you something crazy..."
4. **Tap phones together** (NFC) - Their browser opens automatically
5. **Wait for their PWA to connect** (you'll see notification)
6. **Trigger effects:**
   - Tap effect buttons manually, OR
   - Enable Auto Mode for random effects
7. **Perform magic:**
   - "Watch this..." → Tap "Glitch" → Their screen distorts
   - "Your phone is crashing..." → Tap "Crash" → Fake error appears
   - "Let me fix it..." → Tap "Reset" → Back to normal

### Pro Tips:
- Use **Auto Mode** for hands-free magic (effects trigger automatically)
- **Hide your phone** in pocket, use Bluetooth button (future enhancement)
- Practice the **timing** - let effects build suspense
- Use **Reset** to "fix" their phone at the end

## 🔧 Testing Locally

### Start Backend Server:
```bash
cd backend
npm install
npm start
```

Server runs on `http://localhost:3000`

### Test PWA:
Open browser: `http://localhost:3000/magic`

### Test Controller App:
1. Update server URL to `ws://YOUR_LOCAL_IP:3000` (not localhost!)
2. Find your IP: `ifconfig` (macOS/Linux) or `ipconfig` (Windows)
3. Example: `ws://192.168.1.100:3000`
4. Build and run on Android device

## 📊 System Architecture

```
┌─────────────────┐
│  Your Phone     │
│  (Controller)   │
│  NFC + Controls │
└────────┬────────┘
         │ WebSocket
         ▼
┌─────────────────┐
│  Heroku Server  │
│  WebSocket Hub  │
└────────┬────────┘
         │ WebSocket
         ▼
┌─────────────────┐
│ Spectator Phone │
│  PWA (Browser)  │
│ Visual Effects  │
└─────────────────┘
```

## 🛠 Technologies Used

- **Android App**: Kotlin, NFC API, WebSocket (OkHttp)
- **Backend**: Node.js, Express, WebSocket (ws)
- **PWA**: Vanilla JavaScript, Canvas API, Web Audio API
- **Deployment**: Heroku (or Railway/Render)

## 🔐 Security Notes

- Server uses WebSocket (not secure for production without WSS)
- For production, use `wss://` with SSL certificate (Heroku provides free SSL)
- No authentication - anyone can connect (good for magic tricks!)

## 🐛 Troubleshooting

**NFC not working?**
- Enable NFC in Android settings
- Hold phones back-to-back (where NFC antenna is)
- Make sure screen is unlocked

**WebSocket won't connect?**
- Check server URL (must be `ws://` or `wss://`)
- Verify server is running: visit `/health` endpoint
- Check firewall settings

**Effects not appearing?**
- Check browser console for errors
- Verify spectator's phone is connected to server
- Make sure PWA loaded successfully

**Android app won't build?**
- Sync Gradle files in Android Studio
- Check Android SDK is installed (API 34)
- Update dependencies if needed

## 📝 Future Enhancements

- [ ] Add Bluetooth hardware button support (trigger effects without touching phone)
- [ ] More effects (fire, water, particles, etc.)
- [ ] Sound effects library
- [ ] Save custom effect sequences
- [ ] Multiple spectator support (control many phones at once)
- [ ] QR code alternative to NFC
- [ ] iOS controller app (Swift)

## 📄 License

MIT License - Feel free to use for magic performances!

## 🎭 Credits

Built for magicians who want to add technology to their performances. Inspired by professional magic apps like CRASHED Pro.

---

**Enjoy performing! 🎩✨**
