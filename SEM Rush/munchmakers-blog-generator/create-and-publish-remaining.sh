#!/bin/bash
# Batch publish remaining 5 blogs after Claude creates output files

cd "$(dirname "$0")"

echo "Publishing remaining 5 blog posts..."
echo ""

for file in blog-output-1763804685764.json blog-output-1763804720767.json blog-output-1763804743400.json blog-output-1763804787038.json blog-output-1763804816274.json; do
  if [ -f "data/$file" ]; then
    echo "Publishing $file..."
    node execute-blog-publish.js "$file" 2>&1 | grep -E "(Title:|Live URL:|Post ID:)"
    echo "---"
  else
    echo "⚠ $file not found - Claude Code needs to create it first"
  fi
done

echo ""
echo "✓ Batch publish complete!"
