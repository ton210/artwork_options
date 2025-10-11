# 🎩 Magic Trick Spectator App - REAL Android Device Access

This is the Android APK that gets installed on the **spectator's phone** (the victim). It has **FULL device access** with crazy realistic effects.

## 🔥 What This App Does:

### REAL Effects (Actual Device Access):
1. **📷 Camera** - Opens REAL front camera, shows their face
2. **📸 Gallery** - Shows their ACTUAL photos from device storage
3. **📍 Location** - Gets their REAL GPS coordinates
4. **🎤 Microphone** - Records REAL audio for 5 seconds
5. **📇 Contacts** - Reads their ACTUAL contact list
6. **🔋 Battery** - Shows REAL battery level and status
7. **📳 Vibrate** - Makes phone vibrate with patterns
8. **🔦 Flashlight** - Flashes the LED 5 times

### Visual Effects:
9. **📺 Glitch** - Screen flashes random colors
10. **💥 Crash** - Fake "System UI has stopped" screen
11. **📡 Bluetooth** - Shows fake Bluetooth devices
12. **📞 Call/SMS** - Simulates phone call or text message

## 🎯 How It Works:

### **Installation Process:**

1. **Victim unlocks their phone and hands it to you**
2. **You transfer the APK via:**
   - Bluetooth file transfer (5 seconds)
   - NFC beam (tap phones together)
   - Or manually via USB/ADB

3. **They tap "Install"** (3-5 seconds)
4. **App opens automatically** and requests permissions
5. **They grant permissions** (thinking it's a legitimate scanner app)
6. **You control it from YOUR phone via Bluetooth**

### **Control From Your Phone:**

Once installed, you connect to the app via Bluetooth from your phone and send text commands:

- Send `"camera"` → Opens their camera
- Send `"gallery"` → Shows their photos
- Send `"location"` → Gets GPS coordinates
- Send `"contacts"` → Reads contact list
- Send `"microphone"` → Records audio
- Send `"vibrate"` → Vibrates phone
- Send `"glitch"` → Screen glitches
- Send `"crash"` → Fake crash screen
- Send `"reset"` → Back to normal

## 📦 Building the APK:

### Prerequisites:
- Android Studio (latest version)
- JDK 17
- Android SDK 34

### Build Steps:

1. **Open project in Android Studio:**
   ```bash
   cd /Users/tomernahumi/Documents/Plugins/MM\ report/nfc-magic-trick/android-spectator
   ```
   - Open Android Studio
   - File → Open → Select this folder

2. **Sync Gradle:**
   - Wait for Gradle sync to complete
   - Install any missing SDK components if prompted

3. **Build APK:**
   - Build → Build Bundle(s) / APK(s) → Build APK(s)
   - Or run command: `./gradlew assembleDebug`

4. **Find APK:**
   ```
   app/build/outputs/apk/debug/app-debug.apk
   ```

5. **Install on your phone:**
   ```bash
   adb install app/build/outputs/apk/debug/app-debug.apk
   ```

## 🚀 Usage in Magic Trick:

### **Scenario 1: Bluetooth Transfer**
```
1. Build APK
2. Transfer APK to your phone
3. Pair your phone with victim's phone via Bluetooth
4. Send APK file via Bluetooth
5. Victim taps to install (5 seconds)
6. App opens and requests permissions
7. They grant permissions
8. You connect to app via Bluetooth from your phone
9. Send commands to trigger effects
```

### **Scenario 2: Quick Install Script**
```
1. Victim gives you unlocked phone
2. Enable "Install from Unknown Sources" (Settings → Security)
3. Transfer APK via Bluetooth/USB
4. Install (3-5 seconds)
5. Open app
6. Grant all permissions
7. Control remotely!
```

### **Social Engineering Tips:**
- Name the app "Device Scanner" or "System Check"
- Say: "Let me scan your device for viruses"
- Or: "This app checks if your phone has been hacked"
- Once installed, it looks legitimate
- They grant permissions thinking it's a security tool

## 🎮 Bluetooth Control:

### From Your Phone (Controller):

1. **Pair devices via Bluetooth**
2. **Connect to the app** (UUID: `00001101-0000-1000-8000-00805F9B34FB`)
3. **Send text commands:**
   - `camera` - Opens camera
   - `gallery` - Shows photos
   - `location` - Gets GPS
   - `contacts` - Lists contacts
   - `microphone` - Records audio
   - `vibrate` - Vibrates
   - `flashlight` - Flash LED
   - `glitch` - Visual glitch
   - `crash` - Fake crash
   - `battery` - Show battery
   - `bluetooth` - Scan Bluetooth
   - `reset` - Back to normal

## ⚠️ Permissions Required:

The app requests these permissions on first launch:
- ✅ Camera
- ✅ Microphone
- ✅ Location (GPS)
- ✅ Read Media Images (Photos)
- ✅ Read Contacts
- ✅ Bluetooth
- ✅ Phone State
- ✅ Notifications

**Frame it as:** "This scanner needs full device access to check for security issues"

## 🔧 Advanced: NFC Transfer (Future)

For NFC-based APK transfer:
1. Store APK on your phone
2. Use Android Beam or NFC file transfer app
3. Tap phones together
4. APK transfers automatically
5. They tap to install

## 📱 Tested On:
- Android 8.0+ (API 26+)
- Works on most modern Android devices
- Requires location, camera, storage permissions

## 🎯 Perfect For:
- Close-up magic tricks
- Phone hacking demonstrations
- "Let me check your device" routines
- Social engineering demos
- Tech-savvy audiences

## 💡 Pro Tips:

1. **Install quickly** - The faster you install, the less suspicious
2. **Frame it properly** - "Security scanner" or "Diagnostic tool"
3. **Grant all permissions** - More permissions = more effects
4. **Test beforehand** - Practice the install process
5. **Have a story** - "I developed this app to detect phone hacks"

## 🚨 IMPORTANT:

- This is for **entertainment/magic purposes only**
- Always get **consent** before installing
- **Uninstall** after the trick
- Don't use for malicious purposes
- The app accesses **REAL device data**

---

## 🔥 This is the REAL DEAL!

Unlike the PWA version, this app has:
- ✅ Actual camera access (not fake)
- ✅ Real photo gallery (their actual photos!)
- ✅ Real GPS location
- ✅ Real contact list
- ✅ Real microphone recording
- ✅ Full Bluetooth control

**The reactions will be INSANE when they see their own photos and data!** 🎩✨
