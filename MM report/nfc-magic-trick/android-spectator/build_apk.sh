#!/bin/bash

echo "🎩 Building Magic Trick Spectator APK..."
echo ""

# Check if Android SDK is available
if ! command -v ./gradlew &> /dev/null; then
    echo "❌ Gradle wrapper not found!"
    echo "Please open this project in Android Studio first."
    exit 1
fi

# Make gradlew executable
chmod +x ./gradlew

# Build the APK
echo "📦 Building APK (this may take a minute)..."
./gradlew assembleDebug

# Check if build succeeded
if [ -f "app/build/outputs/apk/debug/app-debug.apk" ]; then
    echo ""
    echo "✅ Build SUCCESS!"
    echo ""
    echo "📱 APK Location:"
    echo "   app/build/outputs/apk/debug/app-debug.apk"
    echo ""
    echo "📏 APK Size:"
    ls -lh app/build/outputs/apk/debug/app-debug.apk | awk '{print "   " $5}'
    echo ""
    echo "🚀 Next Steps:"
    echo "   1. Transfer APK to your phone"
    echo "   2. Send to victim via Bluetooth"
    echo "   3. Install on their phone"
    echo "   4. Control via Bluetooth from your phone"
    echo ""
    echo "📖 See QUICKSTART.md for detailed instructions"
else
    echo ""
    echo "❌ Build FAILED!"
    echo "Check errors above or open in Android Studio"
fi
