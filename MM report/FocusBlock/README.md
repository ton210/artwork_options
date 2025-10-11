# FocusBlock - Android App Blocker

A powerful Android application that helps you stay focused by blocking distracting apps and websites. Inspired by AppBlock, FocusBlock gives you control over your digital life.

## Features

### 🚀 Quick Block
- Instantly block distracting apps with a single toggle
- Easy on/off switch for immediate focus mode
- Fast app selection from installed applications

### 📅 Smart Schedules
- **Time-based blocking**: Block apps during specific hours (e.g., 9 AM - 5 PM for work)
- **Location-based blocking**: Block apps when you're at specific locations
- **Usage limits**: Set daily time limits for app usage
- Multiple schedules can run simultaneously

### 🔒 Strict Mode
- Prevents bypassing blocks once enabled
- Ensures you stay committed to your focus goals
- Acts as a digital "policeman" for your productivity

### 🎯 App & Website Blocking
- Block any installed application
- Block distracting websites via VPN service
- Full-screen block overlay prevents app access
- Automatic return to home screen when blocked app is launched

## Technical Implementation

### Architecture
- **MVVM Architecture** with Jetpack Compose UI
- **Room Database** for local data persistence
- **Kotlin Coroutines** for asynchronous operations
- **DataStore** for preferences management

### Core Components

#### 1. App Blocking Service (`AppBlockingService`)
- Uses `UsageStatsManager` to monitor foreground apps
- Runs as a foreground service for reliability
- Checks blocked apps every 500ms
- Integrates with schedules and Quick Block

#### 2. Accessibility Service (`BlockingAccessibilityService`)
- Shows full-screen blocking overlay when blocked app is detected
- Automatically returns user to home screen
- Displays app name and blocking message

#### 3. VPN Service (`VpnBlockingService`)
- Blocks websites at the network level
- Filters DNS requests and HTTP/HTTPS traffic
- Runs as a system VPN for comprehensive blocking

#### 4. Database Schema
- `BlockedApp`: Stores apps to block
- `BlockedWebsite`: Stores websites to block
- `BlockSchedule`: Stores blocking schedules with time/location/usage data

## Setup Instructions

### Prerequisites
- Android Studio Hedgehog or newer
- Android SDK 24+ (Android 7.0 or higher)
- Kotlin 1.9.0+

### Installation

1. **Clone or copy the project to your machine**

2. **Open in Android Studio**
   ```bash
   cd FocusBlock
   ```
   - Open Android Studio
   - Select "Open an Existing Project"
   - Navigate to the FocusBlock folder

3. **Sync Gradle**
   - Android Studio will automatically prompt to sync Gradle
   - Wait for all dependencies to download

4. **Run the app**
   - Connect an Android device or start an emulator
   - Click the "Run" button (green play icon)
   - Select your device

### Required Permissions Setup

After installing the app, you need to grant several permissions:

#### 1. Usage Stats Permission
- Go to: **Settings → Home → Tap "Usage Stats Permission Required"**
- Or manually: **Android Settings → Apps → Special App Access → Usage Access**
- Find "FocusBlock" and enable it
- **Required for**: Monitoring which apps are running

#### 2. Accessibility Service
- Go to: **Settings → Permissions → Accessibility Service**
- Or manually: **Android Settings → Accessibility → Downloaded Apps**
- Find "FocusBlock" and enable it
- **Required for**: Showing block overlays and returning to home screen

#### 3. VPN Permission (Optional - for website blocking)
- Automatically requested when you try to block websites
- Tap "OK" when prompted
- **Required for**: Blocking websites at network level

#### 4. Notification Permission (Android 13+)
- Automatically requested on first launch
- **Required for**: Showing "Blocking Active" notification

## Usage Guide

### Blocking Apps with Quick Block

1. **Go to Home screen**
2. **Tap the "+" button** under "Blocked Apps"
3. **Select apps** you want to block from the list
4. **Toggle Quick Block** to ON
5. Apps are now blocked!

### Creating Schedules

1. **Go to Schedules tab**
2. **Tap the "+" button**
3. **Enter a name** for your schedule (e.g., "Work Hours")
4. **Select schedule type**:
   - **Time-based**: Enter start and end times (e.g., 09:00 - 17:00)
   - **Location-based**: Set location and radius
   - **Usage limit**: Set daily minutes allowed
5. **Tap Create**
6. **Toggle the schedule ON** to activate

### Using Strict Mode

1. **Go to Home screen**
2. **Toggle Strict Mode** to ON
3. Once enabled, blocks cannot be easily bypassed
4. This ensures you stay committed to your focus time

### Testing the App

1. **Add a test app** (e.g., Chrome, YouTube) to blocked apps
2. **Enable Quick Block**
3. **Try to open the blocked app**
4. You should see a full-screen block message
5. The app will automatically return you to home screen

## Project Structure

```
FocusBlock/
├── app/
│   ├── src/
│   │   └── main/
│   │       ├── java/com/focusblock/app/
│   │       │   ├── data/
│   │       │   │   ├── dao/              # Database DAOs
│   │       │   │   ├── database/         # Room database
│   │       │   │   └── model/            # Data models
│   │       │   ├── service/              # Background services
│   │       │   ├── receiver/             # Broadcast receivers
│   │       │   ├── ui/                   # Compose UI
│   │       │   │   ├── screens/          # App screens
│   │       │   │   └── theme/            # Material theme
│   │       │   ├── util/                 # Utilities
│   │       │   └── FocusBlockApplication.kt
│   │       ├── res/                      # Resources
│   │       └── AndroidManifest.xml
│   └── build.gradle.kts
├── build.gradle.kts
├── settings.gradle.kts
└── README.md
```

## Key Technologies

- **Jetpack Compose**: Modern declarative UI
- **Room**: Local database with Flow support
- **Kotlin Coroutines & Flow**: Asynchronous programming
- **WorkManager**: Background task scheduling
- **UsageStatsManager**: App usage monitoring
- **AccessibilityService**: UI overlay and navigation
- **VpnService**: Network-level website blocking
- **DataStore**: Modern preferences storage

## Limitations & Known Issues

1. **VPN Service**: The website blocking via VPN is a simplified implementation. Full deep packet inspection would require significant additional work.

2. **Accessibility Service**: Must be manually enabled by the user. The app cannot programmatically enable it for security reasons.

3. **Battery Optimization**: Users may need to disable battery optimization for the app to ensure blocking works reliably in the background.

4. **Android 12+**: May require additional permissions or settings due to stricter background restrictions.

## Future Enhancements

- [ ] Website blocking with full DNS filtering
- [ ] Usage statistics and analytics
- [ ] Custom block screen themes
- [ ] Focus mode rewards and gamification
- [ ] Export/import blocked app lists
- [ ] Cloud sync across devices
- [ ] Break reminders
- [ ] Pomodoro timer integration

## Building for Release

1. **Generate a signing key**:
   ```bash
   keytool -genkey -v -keystore focusblock.keystore -alias focusblock -keyalg RSA -keysize 2048 -validity 10000
   ```

2. **Update app/build.gradle.kts** with signing config

3. **Build release APK**:
   ```bash
   ./gradlew assembleRelease
   ```

4. **Find APK** in: `app/build/outputs/apk/release/`

## License

This is a demonstration project created for educational purposes. Feel free to use and modify as needed.

## Contributing

This is a standalone project, but improvements and suggestions are welcome!

## Support

For issues or questions, please refer to the Android documentation:
- [UsageStatsManager](https://developer.android.com/reference/android/app/usage/UsageStatsManager)
- [AccessibilityService](https://developer.android.com/reference/android/accessibilityservice/AccessibilityService)
- [VpnService](https://developer.android.com/reference/android/net/VpnService)
- [Jetpack Compose](https://developer.android.com/jetpack/compose)

---

**FocusBlock** - Take control of your focus, one app at a time. 🎯
