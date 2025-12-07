# MunchMakers AI Blog Generator

**Collaborative blog creation powered by Claude Code + Imagen3**

## How It Works

This is a unique system where:
1. **You** input AI prompt/response data via web interface
2. **Web app** saves the data to a JSON file
3. **Claude Code** (me!) analyzes and generates everything:
   - Original SEO-optimized blog content
   - 3 Imagen3 prompts for images
   - Internal link strategy
   - Meta title & description
4. **APIs** execute: Imagen3 → WebDAV → BigCommerce

---

## Setup

### 1. Install Dependencies

```bash
cd munchmakers-blog-generator
npm install
```

### 2. Environment Variables

The `.env` file is already configured with your credentials:
- BigCommerce API access
- WebDAV credentials for image uploads
- Google Imagen3 API key

### 3. Start the Server

```bash
npm start
```

Server will run at: `http://localhost:3000`

---

## Usage Workflow

### Step 1: Open Web Interface

Navigate to `http://localhost:3000` in your browser

### Step 2: Enter Data

- **AI Prompt**: The search query people are using (e.g., "What are the best custom grinders?")
- **AI Response**: The AI-generated answer you scraped from other tools
- Add multiple pairs if you have them
- (Optional) Add target keyword and internal link suggestions

### Step 3: Submit

Click **"Save & Prepare for Claude"**

Data is saved to: `data/blog-input-{timestamp}.json`

### Step 4: Tell Claude Code to Process

In your Claude Code session, say:

```
"Process the blog generator input file and create a blog post"
```

### Step 5: Claude Code Will:

1. **Read** the input file
2. **Analyze** all prompt/response pairs to understand intent
3. **Write** original, better content (1000-1800 words)
4. **Create** 3 Imagen3 prompts for images
5. **Select** internal links strategically
6. **Generate** SEO meta title & description
7. **Call Imagen3 API** to generate images
8. **Upload images** via WebDAV
9. **Publish** complete blog post to BigCommerce
10. **Return** the live blog URL

---

## File Structure

```
data/
├── blog-input-{timestamp}.json   # Your input data
└── blog-output-{timestamp}.json  # Claude's generated content

services/
├── imagen.js        # Imagen3 API calls
├── webdav.js        # Image uploads
└── bigcommerce.js   # Blog publishing

public/
├── index.html       # Web interface
├── css/style.css    # Styling
└── js/app.js        # Frontend logic
```

---

## What Claude Code Does

When you tell me to "process the blog generator input":

1. I read `data/blog-input-*.json`
2. I analyze the AI prompts to understand search intent
3. I review the AI responses to see what competitors provide
4. I write ORIGINAL content that's better and more comprehensive
5. I create detailed Imagen3 prompts following best practices
6. I select internal links that enhance SEO and user experience
7. I generate metadata optimized for search engines
8. I execute all the API calls to generate images and publish

---

## Example Input

```json
{
  "pairs": [
    {
      "prompt": "What are the best custom grinders for dispensaries?",
      "response": "Custom grinders are essential for dispensaries..."
    }
  ],
  "targetKeyword": "custom grinders wholesale",
  "internalLinks": "Custom Grinders, /product/custom-big-grinder"
}
```

## Example Output (by Claude Code)

```json
{
  "title": "The Ultimate Guide to Custom Grinders for Dispensaries: Wholesale Buying Guide 2025",
  "slug": "custom-grinders-dispensaries-wholesale-guide",
  "metaDescription": "Discover the best custom grinders for dispensaries...",
  "content": "<p>Full HTML blog post content...</p>",
  "images": [
    "https://cdn11.bigcommerce.com/...",
    "https://cdn11.bigcommerce.com/...",
    "https://cdn11.bigcommerce.com/..."
  ],
  "publishedUrl": "https://munchmakers.com/blog/..."
}
```

---

## Benefits of This Approach

✅ **No rigid templates** - Claude Code thinks and adapts to each topic
✅ **Original content** - Not templatized or formulaic
✅ **Strategic** - Internal links placed where they make sense
✅ **SEO-optimized** - Real analysis, not template SEO
✅ **High-quality images** - Prompts tailored to specific content
✅ **Flexible** - Can handle any topic or product

---

## Troubleshooting

**Server won't start:**
- Run `npm install` first
- Check `.env` file exists with credentials

**Claude Code can't find input file:**
- Check `data/` directory exists
- Verify JSON file was created after form submission

**Image generation fails:**
- Check Google API key is valid
- Verify project ID is correct
- Check Imagen3 quota/billing

**Blog post won't publish:**
- Check BigCommerce API credentials
- Verify store hash is correct
- Check blog post slug doesn't already exist

---

## Next Steps

1. Start the server: `npm start`
2. Open browser: `http://localhost:3000`
3. Enter your AI prompt/response data
4. Tell Claude Code to process it!
