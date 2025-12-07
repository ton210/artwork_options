#!/bin/bash
# Resume batch import from where it stopped (after Connecticut)
# Starts with New York (state 11) and continues through remaining states

APP_NAME="bestdispensaries-munchmakers"

echo "🚀 Resuming batch import from New York..."
echo "Remaining: 10 states"
echo ""

STATES=(
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

  STATE_UPPER=$(echo "$STATE" | tr '[:lower:]' '[:upper:]')
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "[$COMPLETED/$TOTAL_STATES] Importing: $STATE_UPPER ($COUNT dispensaries)"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

  heroku run "node import-$STATE.js" -a $APP_NAME

  EXIT_CODE=$?

  if [ $EXIT_CODE -eq 0 ]; then
    echo "✅ $STATE import completed successfully"
  else
    echo "⚠️  $STATE import finished with errors (exit code: $EXIT_CODE)"
  fi

  echo ""

  if [ $COMPLETED -lt $TOTAL_STATES ]; then
    echo "⏳ Waiting 5 seconds before next import..."
    sleep 5
  fi
done

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🎉 All remaining imports complete!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Next step: Calculate rankings"
echo "Run: heroku run npm run rankings:calculate -a $APP_NAME"
