#!/bin/bash
# Sequential batch import manager for all states
# This runs each state import one after another to avoid API rate limits

APP_NAME="bestdispensaries-munchmakers"

echo "🚀 Starting batch import of all remaining states..."
echo "Total: 20 states (2,310 dispensaries)"
echo ""

STATES=(
  "nevada:108"
  "oregon:186"
  "massachusetts:242"
  "vermont:59"
  "colorado:199"
  "washington:188"
  "michigan:177"
  "california:218"
  "maine:138"
  "connecticut:33"
  "new-york:160"
  "rhode-island:11"
  "alaska:125"
  "delaware:14"
  "maryland:113"
  "minnesota:29"
  "montana:150"
  "new-mexico:145"
  "ohio:120"
  "washington-dc:68"
)

TOTAL_STATES=${#STATES[@]}
COMPLETED=0

for state_info in "${STATES[@]}"; do
  STATE=$(echo $state_info | cut -d: -f1)
  COUNT=$(echo $state_info | cut -d: -f2)
  COMPLETED=$((COMPLETED + 1))

  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "[$COMPLETED/$TOTAL_STATES] Importing: ${STATE^^} ($COUNT dispensaries)"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

  heroku run "node import-$STATE.js" -a $APP_NAME

  EXIT_CODE=$?

  if [ $EXIT_CODE -eq 0 ]; then
    echo "✅ $STATE import completed successfully"
  else
    echo "⚠️  $STATE import finished with errors (exit code: $EXIT_CODE)"
  fi

  echo ""

  # Small delay between states
  if [ $COMPLETED -lt $TOTAL_STATES ]; then
    echo "⏳ Waiting 5 seconds before next import..."
    sleep 5
  fi
done

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🎉 All imports complete!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Next step: Calculate rankings for all dispensaries"
echo "Run: heroku run npm run rankings:calculate -a $APP_NAME"
