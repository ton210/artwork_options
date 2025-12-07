/**
 * Process all pending blog inputs in batch
 * Claude Code creates output files, then executes publishing
 */

require('dotenv').config();
const fs = require('fs').promises;
const path = require('path');
const { executeBlogPublish } = require('./execute-blog-publish');

async function processPendingBlogs() {
  console.log('\n' + '='.repeat(70));
  console.log('BATCH PROCESSING PENDING BLOG INPUTS');
  console.log('='.repeat(70));

  try {
    // Find all pending input files
    const dataDir = path.join(__dirname, 'data');
    const files = await fs.readdir(dataDir);

    const pendingInputs = files.filter(f =>
      f.startsWith('blog-input-') && f.endsWith('.json')
    );

    console.log(`\nFound ${pendingInputs.length} input files total`);

    // Check which ones have outputs already
    const inputsNeedingProcessing = [];

    for (const inputFile of pendingInputs) {
      const timestamp = inputFile.replace('blog-input-', '').replace('.json', '');
      const outputFile = `blog-output-${timestamp}.json`;
      const outputPath = path.join(dataDir, outputFile);

      try {
        await fs.access(outputPath);
        console.log(`  ✓ ${inputFile} - output exists, will publish`);
        inputsNeedingProcessing.push({ inputFile, outputFile, needsContent: false });
      } catch {
        console.log(`  ⚠ ${inputFile} - needs Claude Code to write content first`);
        inputsNeedingProcessing.push({ inputFile, outputFile, needsContent: true });
      }
    }

    console.log(`\n${inputsNeedingProcessing.filter(i => i.needsContent).length} need content generation by Claude Code`);
    console.log(`${inputsNeedingProcessing.filter(i => !i.needsContent).length} ready to publish`);

    return inputsNeedingProcessing;

  } catch (error) {
    console.error('Error:', error.message);
    return [];
  }
}

// Run
processPendingBlogs().then(files => {
  console.log('\n' + '='.repeat(70));
  console.log('FILES NEEDING CLAUDE CODE CONTENT GENERATION:');
  console.log('='.repeat(70));

  const needsContent = files.filter(f => f.needsContent);
  needsContent.forEach((f, i) => {
    console.log(`\n${i + 1}. ${f.inputFile}`);
  });

  if (needsContent.length > 0) {
    console.log('\n\nClaude Code needs to create output files for these first!');
  }
});
