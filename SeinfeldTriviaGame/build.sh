#!/bin/bash

# Set up environment
export PATH="/opt/homebrew/opt/openjdk@17/bin:$PATH"
export ANDROID_HOME="/opt/homebrew/share/android-commandlinetools"
export PATH="$PATH:$ANDROID_HOME/cmdline-tools/latest/bin:$ANDROID_HOME/platform-tools"
export JAVA_HOME="/opt/homebrew/opt/openjdk@17"

echo "=== Building Seinfeld Trivia Game APK ==="
echo "Java version:"
java -version
echo ""
echo "Android SDK path: $ANDROID_HOME"
echo ""

# Build the APK
echo "Building APK..."
chmod +x gradlew
./gradlew assembleDebug --stacktrace

if [ $? -eq 0 ]; then
    echo ""
    echo "=== BUILD SUCCESSFUL ==="
    echo "APK created at: app/build/outputs/apk/debug/"
    ls -la app/build/outputs/apk/debug/ || echo "APK directory not found"
else
    echo ""
    echo "=== BUILD FAILED ==="
    echo "Check the error messages above"
fi