#!/bin/bash
# Uninstall FocusBlock Auto-Scheduler

PLIST_FILE="$HOME/Library/LaunchAgents/com.focusblock.autoscheduler.plist"

echo "🗑️  Uninstalling FocusBlock Auto-Scheduler..."
echo ""

# Unload the LaunchAgent
if [ -f "$PLIST_FILE" ]; then
    echo "Stopping scheduler..."
    launchctl unload "$PLIST_FILE" 2>/dev/null

    echo "Removing LaunchAgent..."
    rm "$PLIST_FILE"

    echo ""
    echo "✅ FocusBlock Auto-Scheduler uninstalled"
    echo ""
    echo "Note: Blocking is still configured in the blocker."
    echo "To disable blocking, run:"
    echo "  python3 blocker.py disable"
    echo ""
else
    echo "❌ LaunchAgent not found - may already be uninstalled"
fi
