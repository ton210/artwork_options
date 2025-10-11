#!/bin/bash
# Install FocusBlock Auto-Scheduler to run on startup

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PLIST_FILE="$HOME/Library/LaunchAgents/com.focusblock.autoscheduler.plist"

echo "🔧 Installing FocusBlock Auto-Scheduler..."
echo ""

# Check if schedule module is installed
echo "📦 Checking Python dependencies..."
python3 -c "import schedule" 2>/dev/null
if [ $? -ne 0 ]; then
    echo "Installing 'schedule' module..."
    pip3 install schedule
    if [ $? -ne 0 ]; then
        echo "❌ Failed to install schedule module"
        echo "Please run: pip3 install schedule"
        exit 1
    fi
fi
echo "✅ Dependencies installed"
echo ""

# Create LaunchAgent plist
echo "📝 Creating LaunchAgent configuration..."
cat > "$PLIST_FILE" << EOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>com.focusblock.autoscheduler</string>

    <key>ProgramArguments</key>
    <array>
        <string>/usr/bin/python3</string>
        <string>$SCRIPT_DIR/auto_schedule.py</string>
    </array>

    <key>RunAtLoad</key>
    <true/>

    <key>KeepAlive</key>
    <true/>

    <key>StandardOutPath</key>
    <string>$HOME/.focusblock/scheduler.log</string>

    <key>StandardErrorPath</key>
    <string>$HOME/.focusblock/scheduler_error.log</string>
</dict>
</plist>
EOF

echo "✅ LaunchAgent created at:"
echo "   $PLIST_FILE"
echo ""

# Load the LaunchAgent
echo "🚀 Loading scheduler..."
launchctl unload "$PLIST_FILE" 2>/dev/null
launchctl load "$PLIST_FILE"

if [ $? -eq 0 ]; then
    echo ""
    echo "{'='*60}"
    echo "✅ SUCCESS! FocusBlock Auto-Scheduler is now installed"
    echo "{'='*60}"
    echo ""
    echo "📅 Schedule: Sunday-Thursday, 8 AM - 7 PM"
    echo "🔒 Strict Mode: ENABLED during active hours"
    echo "🔄 Auto-Start: Will run on every login/restart"
    echo ""
    echo "COMMANDS:"
    echo "  View logs:      tail -f ~/.focusblock/scheduler.log"
    echo "  Stop scheduler: launchctl unload $PLIST_FILE"
    echo "  Start scheduler: launchctl load $PLIST_FILE"
    echo "  Uninstall:      bash $SCRIPT_DIR/uninstall_schedule.sh"
    echo ""
else
    echo "❌ Failed to load LaunchAgent"
    echo "Check permissions and try again"
    exit 1
fi
