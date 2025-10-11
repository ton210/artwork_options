# Seinfeld Trivia Game

A fun Android trivia game testing your knowledge of the classic TV show Seinfeld!

## Features

- **4 Difficulty Modes:**
  - Easy Mode: Basic questions about main characters and plot
  - Medium Mode: More detailed questions about episodes and storylines  
  - Hard Mode: Challenging questions about specific details
  - Expert Mode: The most difficult questions for true Seinfeld masters

- **Game Features:**
  - Over 30+ trivia questions across all difficulty levels
  - Score tracking with performance messages
  - Seinfeld-themed UI and quotes
  - Play again functionality
  - Main menu navigation

## How to Build

To build the APK, you'll need Android Studio or the Android SDK installed:

1. Open the project in Android Studio
2. Build > Generate Signed Bundle/APK
3. Or use command line: `./gradlew assembleDebug`

## Game Flow

1. **Main Menu**: Choose your difficulty level
2. **Game Screen**: Answer 10 questions with multiple choice answers
3. **Results Screen**: See your score with Seinfeld-themed performance messages

## Performance Messages

Based on your score percentage:
- 100%: "Master of Your Domain! Perfect Score!"
- 90%+: "Master of Your Domain! You're gold, Jerry! Gold!"
- 80%+: "Serenity Now! Excellent knowledge!"
- 70%+: "That's Gold, Jerry! Pretty, pretty good!"
- 60%+: "Yada Yada Yada... Not bad at all!"
- 50%+: "Newman! You can do better!"
- <50%: "No Soup For You! Better luck next time!"

## Technical Details

- **Target SDK**: 33
- **Min SDK**: 21
- **Language**: Java
- **Dependencies**: AndroidX, Material Design Components

The game uses a question database system with difficulty-based filtering and includes proper Android lifecycle management.

---

*"Not that there's anything wrong with that!"*