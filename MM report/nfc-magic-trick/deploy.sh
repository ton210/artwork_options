#!/bin/bash

echo "🎩 Magic Trick Deployment Script"
echo "================================"
echo ""

# Check if Heroku CLI is installed
if ! command -v heroku &> /dev/null
then
    echo "❌ Heroku CLI not found!"
    echo "📦 Install it with: brew install heroku/brew/heroku"
    echo "   Or download from: https://devcenter.heroku.com/articles/heroku-cli"
    exit 1
fi

echo "✅ Heroku CLI found"
echo ""

# Ask for app name
read -p "Enter your Heroku app name (e.g., my-magic-trick): " APP_NAME

if [ -z "$APP_NAME" ]; then
    echo "❌ App name cannot be empty"
    exit 1
fi

echo ""
echo "🚀 Starting deployment to Heroku..."
echo ""

# Change to backend directory
cd backend

# Check if git repo exists
if [ ! -d ".git" ]; then
    echo "📝 Initializing git repository..."
    git init
    git add .
    git commit -m "Initial commit - Magic Trick Backend"
fi

# Check if Heroku app exists
if heroku apps:info --app $APP_NAME &> /dev/null; then
    echo "✅ App '$APP_NAME' already exists"
else
    echo "📦 Creating Heroku app '$APP_NAME'..."
    heroku create $APP_NAME
fi

# Add Heroku remote if not exists
if ! git remote | grep -q heroku; then
    echo "🔗 Adding Heroku remote..."
    heroku git:remote -a $APP_NAME
fi

# Deploy
echo "🚀 Deploying to Heroku..."
git push heroku main || git push heroku master

# Check deployment status
if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Deployment successful!"
    echo ""
    echo "📱 Your app is live at:"
    echo "   https://$APP_NAME.herokuapp.com"
    echo ""
    echo "📱 PWA URL (for NFC):"
    echo "   https://$APP_NAME.herokuapp.com/magic"
    echo ""
    echo "🔌 WebSocket URL (for Android app):"
    echo "   wss://$APP_NAME.herokuapp.com"
    echo ""
    echo "Next steps:"
    echo "1. Test PWA: https://$APP_NAME.herokuapp.com/magic"
    echo "2. Update Android app MainActivity.kt with:"
    echo "   serverUrl = \"wss://$APP_NAME.herokuapp.com\""
    echo "   NFC url = \"https://$APP_NAME.herokuapp.com/magic\""
    echo "3. Build and install Android app"
    echo ""

    # Ask if user wants to open the app
    read -p "Open app in browser? (y/n): " OPEN_APP
    if [ "$OPEN_APP" = "y" ]; then
        heroku open --app $APP_NAME
    fi
else
    echo ""
    echo "❌ Deployment failed!"
    echo "Check the error messages above"
    exit 1
fi
