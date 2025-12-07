require('dotenv').config();
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const db = require('./src/config/database');

// R2 Configuration
const R2_ACCOUNT_ID = process.env.R2_ACCOUNT_ID;
const R2_ACCESS_KEY_ID = process.env.R2_ACCESS_KEY_ID;
const R2_SECRET_ACCESS_KEY = process.env.R2_SECRET_ACCESS_KEY;
const R2_BUCKET = process.env.R2_BUCKET;
const R2_PUBLIC_URL = process.env.R2_PUBLIC_URL;
const R2_ENDPOINT = `https://${R2_ACCOUNT_ID}.r2.cloudflarestorage.com`;

const LOGOS_DIR = './brand-logos';
const R2_FOLDER = 'brand-logos';

// AWS Signature V4 implementation for R2
function getSignatureKey(key, dateStamp, regionName, serviceName) {
  const kDate = crypto.createHmac('sha256', 'AWS4' + key).update(dateStamp).digest();
  const kRegion = crypto.createHmac('sha256', kDate).update(regionName).digest();
  const kService = crypto.createHmac('sha256', kRegion).update(serviceName).digest();
  const kSigning = crypto.createHmac('sha256', kService).update('aws4_request').digest();
  return kSigning;
}

function sha256(data) {
  return crypto.createHash('sha256').update(data).digest('hex');
}

async function uploadToR2(filePath, key) {
  const fileContent = fs.readFileSync(filePath);
  const contentHash = sha256(fileContent);

  const now = new Date();
  const amzDate = now.toISOString().replace(/[:-]|\.\d{3}/g, '');
  const dateStamp = amzDate.slice(0, 8);

  const method = 'PUT';
  const service = 's3';
  const region = 'auto';
  const host = `${R2_BUCKET}.${R2_ACCOUNT_ID}.r2.cloudflarestorage.com`;
  const canonicalUri = '/' + key;

  const canonicalQueryString = '';
  const canonicalHeaders = [
    `content-type:image/jpeg`,
    `host:${host}`,
    `x-amz-content-sha256:${contentHash}`,
    `x-amz-date:${amzDate}`,
  ].join('\n') + '\n';

  const signedHeaders = 'content-type;host;x-amz-content-sha256;x-amz-date';
  const canonicalRequest = [
    method,
    canonicalUri,
    canonicalQueryString,
    canonicalHeaders,
    signedHeaders,
    contentHash,
  ].join('\n');

  const algorithm = 'AWS4-HMAC-SHA256';
  const credentialScope = `${dateStamp}/${region}/${service}/aws4_request`;
  const stringToSign = [
    algorithm,
    amzDate,
    credentialScope,
    sha256(canonicalRequest),
  ].join('\n');

  const signingKey = getSignatureKey(R2_SECRET_ACCESS_KEY, dateStamp, region, service);
  const signature = crypto.createHmac('sha256', signingKey).update(stringToSign).digest('hex');

  const authorizationHeader = `${algorithm} Credential=${R2_ACCESS_KEY_ID}/${credentialScope}, SignedHeaders=${signedHeaders}, Signature=${signature}`;

  const url = `https://${host}${canonicalUri}`;
  const response = await fetch(url, {
    method: 'PUT',
    headers: {
      'Content-Type': 'image/jpeg',
      'x-amz-content-sha256': contentHash,
      'x-amz-date': amzDate,
      'Authorization': authorizationHeader,
      'Cache-Control': 'public, max-age=31536000',
    },
    body: fileContent,
  });

  if (!response.ok) {
    const text = await response.text();
    throw new Error(`Upload failed: ${response.status} - ${text}`);
  }

  return `${R2_PUBLIC_URL}/${key}`;
}

async function uploadAllLogos() {
  console.log('===========================================');
  console.log('     UPLOAD BRAND LOGOS TO CLOUDFLARE R2');
  console.log('===========================================\n');

  console.log(`R2 Bucket: ${R2_BUCKET}`);
  console.log(`Public URL: ${R2_PUBLIC_URL}`);
  console.log(`Local directory: ${LOGOS_DIR}\n`);

  // Get all logo files
  const files = fs.readdirSync(LOGOS_DIR).filter(f => f.endsWith('.jpg'));
  console.log(`Found ${files.length} logo files to upload\n`);

  let uploaded = 0;
  let errors = 0;
  let dbUpdated = 0;

  for (const file of files) {
    const filePath = path.join(LOGOS_DIR, file);
    const slug = file.replace('.jpg', '');
    const r2Key = `${R2_FOLDER}/${file}`;

    try {
      // Upload to R2
      const publicUrl = await uploadToR2(filePath, r2Key);
      uploaded++;

      // Update database
      const result = await db.query(
        'UPDATE brands SET logo_url = $1 WHERE slug = $2',
        [publicUrl, slug]
      );

      if (result.rowCount > 0) {
        dbUpdated++;
        console.log(`[${uploaded}/${files.length}] ✓ ${slug}`);
      } else {
        console.log(`[${uploaded}/${files.length}] ✓ ${slug} (no DB match)`);
      }

      // Progress every 50
      if (uploaded % 50 === 0) {
        console.log(`\n--- Progress: ${uploaded}/${files.length} uploaded, ${dbUpdated} DB updated ---\n`);
      }

      // Small delay to avoid rate limiting
      await new Promise(r => setTimeout(r, 50));

    } catch (error) {
      errors++;
      console.error(`[ERROR] ${slug}: ${error.message}`);
    }
  }

  console.log(`\n===========================================`);
  console.log(`              COMPLETE`);
  console.log(`===========================================`);
  console.log(`Total files: ${files.length}`);
  console.log(`Uploaded to R2: ${uploaded}`);
  console.log(`Database updated: ${dbUpdated}`);
  console.log(`Errors: ${errors}`);
  console.log(`===========================================\n`);

  await db.pool.end();
}

// Run
uploadAllLogos().catch(console.error);
