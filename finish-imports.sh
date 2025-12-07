#!/bin/bash
# Finish the last remaining states (Ohio and Washington DC only)
# All other states are already complete

APP_NAME="bestdispensaries-munchmakers"

echo "🚀 Finishing final 2 states..."
echo ""

STATES=(
  "ohio:120"
  "washington-dc:68"
)

for state_info in "${STATES[@]}"; do
  STATE=$(echo $state_info | cut -d: -f1)
  COUNT=$(echo $state_info | cut -d: -f2)

  STATE_UPPER=$(echo "$STATE" | tr '[:lower:]' '[:upper:]')
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "Importing: $STATE_UPPER ($COUNT dispensaries)"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

  heroku run "node import-$STATE.js" -a $APP_NAME

  echo ""
  sleep 3
done

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🎉 ALL IMPORTS COMPLETE!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Next: Calculate final rankings"
echo "Run: heroku run npm run rankings:calculate -a $APP_NAME"
