# Shopify API Official Requirements Documentation 2024-2025

## Table of Contents
1. [API Versioning and Lifecycle](#api-versioning-and-lifecycle)
2. [Authentication Examples](#authentication-examples)
3. [GraphQL API Examples](#graphql-api-examples)
4. [REST API Examples](#rest-api-examples)
5. [Webhook Implementation](#webhook-implementation)
6. [Rate Limiting and Performance](#rate-limiting-and-performance)
7. [Built for Shopify Requirements](#built-for-shopify-requirements)
8. [Latest Release Notes (2025-01)](#latest-release-notes-2025-01)

---

## API Versioning and Lifecycle

### Current API Status
- **GraphQL Admin API**: Required for all new public apps starting April 1, 2025
- **REST Admin API**: Legacy status as of October 1, 2024
- **Latest Version**: 2025-01 (released January 2025)
- **Quarterly Releases**: New versions every quarter at 5pm UTC

### API Endpoints
```
GraphQL Admin API: https://{shop}.myshopify.com/admin/api/2025-01/graphql.json
REST Admin API: https://{shop}.myshopify.com/admin/api/2025-01/{resource}.json
Storefront API: https://{shop}.myshopify.com/api/2025-01/graphql.json
```

---

## Authentication Examples

### OAuth Flow Implementation

#### 1. Installation Request Verification (Security Step)
```javascript
// Verify HMAC before starting OAuth flow
const crypto = require('crypto');

const verifyInstallRequest = (query) => {
  const { hmac, ...params } = query;
  const queryString = Object.keys(params)
    .sort()
    .map(key => `${key}=${params[key]}`)
    .join('&');
    
  const generatedHmac = crypto
    .createHmac('sha256', CLIENT_SECRET)
    .update(queryString)
    .digest('hex');
    
  return crypto.timingSafeEqual(
    Buffer.from(hmac),
    Buffer.from(generatedHmac)
  );
};
```

#### 2. Authorization URL Generation
```javascript
// Generate secure nonce for state parameter
const state = crypto.randomBytes(32).toString('hex');

// Build authorization URL with exact format
const authUrl = `https://${shop}.myshopify.com/admin/oauth/authorize?` +
  `client_id=${CLIENT_ID}&` +
  `scope=${SCOPES}&` +  // comma-separated list of scopes
  `redirect_uri=${encodeURIComponent(REDIRECT_URI)}&` +
  `state=${state}&` +
  `grant_options[]=${ACCESS_MODE}`;  // optional: 'per-user' for online tokens

// Store state in session for later verification
session.oauthState = state;
```

#### 3. Authorization Code Validation
```javascript
// Validate authorization callback
const validateAuthCallback = (query, storedState) => {
  const { code, hmac, state, shop } = query;
  
  // Verify state matches stored nonce
  if (state !== storedState) {
    throw new Error('Invalid state parameter');
  }
  
  // Verify shop format
  if (!shop.match(/^[a-zA-Z0-9][a-zA-Z0-9-]*\.myshopify\.com$/)) {
    throw new Error('Invalid shop format');
  }
  
  // Verify HMAC
  const { hmac: receivedHmac, ...params } = query;
  const queryString = Object.keys(params)
    .sort()
    .map(key => `${key}=${params[key]}`)
    .join('&');
    
  const generatedHmac = crypto
    .createHmac('sha256', CLIENT_SECRET)
    .update(queryString)
    .digest('hex');
    
  if (!crypto.timingSafeEqual(
    Buffer.from(receivedHmac),
    Buffer.from(generatedHmac)
  )) {
    throw new Error('Invalid HMAC');
  }
  
  return { code, shop };
};
```

#### 4. Access Token Exchange
```javascript
// Exchange authorization code for access token
const exchangeCodeForToken = async (code, shop) => {
  const response = await fetch(`https://${shop}/admin/oauth/access_token`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      client_id: CLIENT_ID,
      client_secret: CLIENT_SECRET,
      code: code,
    }),
  });
  
  if (!response.ok) {
    throw new Error(`Token exchange failed: ${response.statusText}`);
  }
  
  const tokenData = await response.json();
  
  // Verify granted scopes match requested scopes
  const grantedScopes = tokenData.scope.split(',');
  const requestedScopes = SCOPES.split(',');
  
  const missingScopes = requestedScopes.filter(
    scope => !grantedScopes.includes(scope)
  );
  
  if (missingScopes.length > 0) {
    console.warn('Missing scopes:', missingScopes);
  }
  
  return {
    access_token: tokenData.access_token,
    scope: tokenData.scope,
    associated_user_scope: tokenData.associated_user_scope, // for online tokens
    associated_user: tokenData.associated_user // for online tokens
  };
};
```

#### 5. Complete Access Scopes Reference
```javascript
// Authenticated Access Scopes (choose only what you need)
const AUTHENTICATED_SCOPES = {
  // Orders
  'read_orders': 'Read order data',
  'write_orders': 'Modify orders',
  'read_all_orders': 'Access all orders (requires special permission)',
  
  // Products
  'read_products': 'Read product data',
  'write_products': 'Create and modify products',
  'read_product_listings': 'Read product listings',
  'write_product_listings': 'Modify product listings',
  'read_inventory': 'Read inventory levels',
  'write_inventory': 'Modify inventory',
  
  // Customers
  'read_customers': 'Read customer data',
  'write_customers': 'Create and modify customers',
  'read_customer_payment_methods': 'Read payment methods (protected)',
  
  // Content
  'read_content': 'Read blog posts, pages, comments',
  'write_content': 'Create and modify content',
  
  // Themes
  'read_themes': 'Read theme files',
  'write_themes': 'Modify themes',
  
  // Script Tags
  'read_script_tags': 'Read script tags',
  'write_script_tags': 'Create and modify script tags',
  
  // Fulfillments
  'read_fulfillments': 'Read fulfillment data',
  'write_fulfillments': 'Create and modify fulfillments',
  
  // Shipping
  'read_shipping': 'Read shipping zones and rates',
  'write_shipping': 'Modify shipping settings',
  
  // Analytics
  'read_analytics': 'Access analytics data',
  
  // Markets
  'read_markets': 'Read market data',
  'write_markets': 'Modify markets',
  
  // Locales
  'read_locales': 'Read locale data',
  'write_locales': 'Modify locales',
  
  // Price Rules
  'read_price_rules': 'Read discount codes and automatic discounts',
  'write_price_rules': 'Create and modify discounts',
  
  // Marketing Events
  'read_marketing_events': 'Read marketing event data',
  'write_marketing_events': 'Create marketing events',
  
  // Resource Feedbacks
  'read_resource_feedbacks': 'Read resource feedback',
  'write_resource_feedbacks': 'Create resource feedback',
  
  // Shopify Payments
  'read_shopify_payments_payouts': 'Read payout data',
  'read_shopify_payments_disputes': 'Read dispute data',
  
  // Third Party Fulfillment Orders
  'read_third_party_fulfillment_orders': 'Read 3PL fulfillment orders',
  'write_third_party_fulfillment_orders': 'Modify 3PL fulfillment orders',
  
  // Translations
  'read_translations': 'Read translations',
  'write_translations': 'Create and modify translations',
  
  // Reports
  'read_reports': 'Access reports',
  'write_reports': 'Create reports'
};

// Unauthenticated Access Scopes (for Storefront API)
const UNAUTHENTICATED_SCOPES = {
  'unauthenticated_read_checkouts': 'Read checkout data without authentication',
  'unauthenticated_read_customers': 'Read customer data without authentication',
  'unauthenticated_read_customer_tags': 'Read customer tags without authentication',
  'unauthenticated_read_product_listings': 'Read product listings without authentication',
  'unauthenticated_read_product_tags': 'Read product tags without authentication',
  'unauthenticated_read_selling_plans': 'Read selling plans without authentication',
  'unauthenticated_write_checkouts': 'Create checkouts without authentication',
  'unauthenticated_write_customers': 'Create customers without authentication'
};

// Example scope selection
const scopes = [
  'read_products',
  'write_products',
  'read_orders',
  'read_customers'
].join(',');  // Must be comma-separated
```

### Authentication Headers
```javascript
const headers = {
  'X-Shopify-Access-Token': access_token,
  'Content-Type': 'application/json',
};
```

---

## GraphQL API Examples

### Basic GraphQL Setup

#### Node.js with Official Shopify Library
```javascript
import { shopifyApi } from '@shopify/shopify-api';

const shopify = shopifyApi({
  apiKey: process.env.SHOPIFY_API_KEY,
  apiSecretKey: process.env.SHOPIFY_API_SECRET,
  scopes: ['read_products', 'write_products'],
  hostName: process.env.HOST,
});

// In your route handler
const { admin } = await shopify.authenticate.admin(request);
```

#### cURL Example
```bash
curl -X POST \
  https://your-shop.myshopify.com/admin/api/2025-01/graphql.json \
  -H 'Content-Type: application/json' \
  -H 'X-Shopify-Access-Token: your-access-token' \
  -d '{
    "query": "query getProducts($first: Int!) { products(first: $first) { edges { node { id title } } } }",
    "variables": { "first": 10 }
  }'
```

### Common GraphQL Queries

#### 1. Fetch Products
```graphql
query getProducts($first: Int!, $after: String) {
  products(first: $first, after: $after) {
    edges {
      node {
        id
        title
        handle
        status
        totalInventory
        variants(first: 5) {
          edges {
            node {
              id
              title
              price
              inventoryQuantity
            }
          }
        }
      }
      cursor
    }
    pageInfo {
      hasNextPage
      endCursor
    }
  }
}
```

#### 2. Fetch Orders
```graphql
query getOrders($first: Int!) {
  orders(first: $first) {
    edges {
      node {
        id
        name
        email
        totalPriceSet {
          shopMoney {
            amount
            currencyCode
          }
        }
        lineItems(first: 10) {
          edges {
            node {
              id
              title
              quantity
              variant {
                id
                title
              }
            }
          }
        }
      }
    }
  }
}
```

#### 3. Fetch Customer Data
```graphql
query getCustomers($first: Int!) {
  customers(first: $first) {
    edges {
      node {
        id
        firstName
        lastName
        email
        phone
        ordersCount
        totalSpentV2 {
          amount
          currencyCode
        }
      }
    }
  }
}
```

### GraphQL Mutations

#### 1. Create Product
```graphql
mutation createProduct($input: ProductInput!) {
  productCreate(input: $input) {
    product {
      id
      title
      handle
    }
    userErrors {
      field
      message
    }
  }
}
```

```javascript
// Variables
const variables = {
  input: {
    title: "New Product",
    bodyHtml: "Product description",
    vendor: "Your Vendor",
    productType: "Type",
    variants: [
      {
        price: "29.99",
        inventoryQuantity: 100,
        inventoryManagement: "SHOPIFY"
      }
    ]
  }
};
```

#### 2. Update Product
```graphql
mutation updateProduct($input: ProductInput!) {
  productUpdate(input: $input) {
    product {
      id
      title
    }
    userErrors {
      field
      message
    }
  }
}
```

#### 3. Create Order
```graphql
mutation draftOrderCreate($input: DraftOrderInput!) {
  draftOrderCreate(input: $input) {
    draftOrder {
      id
      name
    }
    userErrors {
      field
      message
    }
  }
}
```

### Error Handling
```javascript
const response = await admin.graphql(`
  query getProducts($first: Int!) {
    products(first: $first) {
      edges {
        node {
          id
          title
        }
      }
    }
  }
`, {
  variables: { first: 10 }
});

const data = await response.json();

if (data.errors) {
  console.error('GraphQL errors:', data.errors);
}

if (data.data.products.edges.length === 0) {
  console.log('No products found');
}
```

---

## REST API Examples

### Basic REST Setup

#### Node.js Example
```javascript
const baseUrl = `https://${shop}.myshopify.com/admin/api/2025-01`;
const headers = {
  'X-Shopify-Access-Token': accessToken,
  'Content-Type': 'application/json',
};
```

### Common REST Operations

#### 1. Get Products
```javascript
// GET /admin/api/2025-01/products.json
const response = await fetch(`${baseUrl}/products.json?limit=50&fields=id,title,handle,status`, {
  headers
});
const { products } = await response.json();
```

#### 2. Create Product
```javascript
// POST /admin/api/2025-01/products.json
const productData = {
  product: {
    title: "New Product",
    body_html: "Product description",
    vendor: "Your Vendor",
    product_type: "Type",
    variants: [
      {
        price: "29.99",
        inventory_quantity: 100,
        inventory_management: "shopify"
      }
    ]
  }
};

const response = await fetch(`${baseUrl}/products.json`, {
  method: 'POST',
  headers,
  body: JSON.stringify(productData)
});
```

#### 3. Update Product
```javascript
// PUT /admin/api/2025-01/products/{id}.json
const updateData = {
  product: {
    id: productId,
    title: "Updated Product Title"
  }
};

const response = await fetch(`${baseUrl}/products/${productId}.json`, {
  method: 'PUT',
  headers,
  body: JSON.stringify(updateData)
});
```

#### 4. Get Orders
```javascript
// GET /admin/api/2025-01/orders.json
const response = await fetch(`${baseUrl}/orders.json?status=any&limit=50`, {
  headers
});
const { orders } = await response.json();
```

---

## Webhook Implementation

### Webhook Setup

#### 1. Create Webhook Subscription (GraphQL)
```graphql
mutation webhookSubscriptionCreate($topic: WebhookSubscriptionTopic!, $webhookSubscription: WebhookSubscriptionInput!) {
  webhookSubscriptionCreate(topic: $topic, webhookSubscription: $webhookSubscription) {
    webhookSubscription {
      id
      callbackUrl
      topic
    }
    userErrors {
      field
      message
    }
  }
}
```

```javascript
const variables = {
  topic: "ORDERS_PAID",
  webhookSubscription: {
    callbackUrl: "https://yourapp.com/webhooks/orders/paid",
    format: "JSON"
  }
};
```

#### 2. Create Webhook Subscription (REST)
```javascript
const webhookData = {
  webhook: {
    topic: 'orders/paid',
    address: 'https://yourapp.com/webhooks/orders/paid',
    format: 'json'
  }
};

const response = await fetch(`${baseUrl}/webhooks.json`, {
  method: 'POST',
  headers,
  body: JSON.stringify(webhookData)
});
```

### Webhook Handler Examples

#### Node.js/Express Webhook Handler
```javascript
import crypto from 'crypto';

app.post('/webhooks/orders/paid', express.raw({ type: 'application/json' }), (req, res) => {
  const body = req.body;
  const hmac = req.get('X-Shopify-Hmac-Sha256');
  const shop = req.get('X-Shopify-Shop-Domain');
  
  // Verify webhook
  const generatedHash = crypto
    .createHmac('sha256', process.env.SHOPIFY_WEBHOOK_SECRET)
    .update(body)
    .digest('base64');
  
  if (!crypto.timingSafeEqual(Buffer.from(generatedHash), Buffer.from(hmac))) {
    return res.status(401).send('Unauthorized');
  }
  
  // Process the webhook
  const order = JSON.parse(body);
  console.log(`Order ${order.name} was paid for ${order.total_price}`);
  
  res.status(200).send('OK');
});
```

#### Remix Webhook Handler
```javascript
export const action = async ({ request }) => {
  const { payload, topic, shop, session, admin } = await authenticate.webhook(request);
  
  switch (topic) {
    case "ORDERS_PAID":
      console.log(`Order ${payload.name} was paid`);
      
      // Use GraphQL admin if needed
      if (admin) {
        const response = await admin.graphql(`
          query getOrder($id: ID!) {
            order(id: $id) {
              id
              name
              totalPriceSet {
                shopMoney {
                  amount
                  currencyCode
                }
              }
            }
          }
        `, {
          variables: { id: `gid://shopify/Order/${payload.id}` }
        });
      }
      break;
      
    case "PRODUCTS_CREATE":
      console.log(`Product ${payload.title} was created`);
      break;
  }
  
  return new Response("OK", { status: 200 });
};
```

### Common Webhook Topics
```javascript
const webhookTopics = [
  'ORDERS_PAID',
  'ORDERS_CANCELLED',
  'ORDERS_FULFILLED',
  'ORDERS_PARTIALLY_FULFILLED',
  'PRODUCTS_CREATE',
  'PRODUCTS_UPDATE',
  'CUSTOMERS_CREATE',
  'CUSTOMERS_UPDATE',
  'APP_UNINSTALLED'
];
```

---

## Rate Limiting and Performance

### GraphQL Rate Limiting
```javascript
// Query cost calculation example
const query = `
  query getProducts($first: Int!) {  # Cost: 1
    products(first: $first) {        # Cost: calculated based on $first value
      edges {
        node {
          id                         # Cost: 0
          title                      # Cost: 0
          variants(first: 10) {      # Cost: 10
            edges {
              node {
                id                   # Cost: 0
                price                # Cost: 0
              }
            }
          }
        }
      }
    }
  }
`;
// Total cost: 1 + ($first value) + ($first * 10)
```

### Rate Limit Handling
```javascript
const makeGraphQLRequest = async (query, variables, retries = 3) => {
  try {
    const response = await admin.graphql(query, { variables });
    const result = await response.json();
    
    // Check for rate limit in extensions
    if (result.extensions?.cost?.throttleStatus?.currentlyAvailable < 100) {
      console.warn('Rate limit approaching, consider slowing requests');
    }
    
    return result;
  } catch (error) {
    if (error.response?.status === 429 && retries > 0) {
      const retryAfter = error.response.headers.get('Retry-After') || 2;
      await new Promise(resolve => setTimeout(resolve, retryAfter * 1000));
      return makeGraphQLRequest(query, variables, retries - 1);
    }
    throw error;
  }
};
```

### Performance Optimization

#### Pagination Example
```javascript
const getAllProducts = async () => {
  let allProducts = [];
  let hasNextPage = true;
  let cursor = null;
  
  while (hasNextPage) {
    const response = await admin.graphql(`
      query getProducts($first: Int!, $after: String) {
        products(first: $first, after: $after) {
          edges {
            node {
              id
              title
            }
            cursor
          }
          pageInfo {
            hasNextPage
            endCursor
          }
        }
      }
    `, {
      variables: { first: 50, after: cursor }
    });
    
    const data = await response.json();
    allProducts.push(...data.data.products.edges.map(edge => edge.node));
    
    hasNextPage = data.data.products.pageInfo.hasNextPage;
    cursor = data.data.products.pageInfo.endCursor;
  }
  
  return allProducts;
};
```

---

## Built for Shopify Requirements

### Technical Requirements with Examples

#### 1. App Bridge Integration
```javascript
// app/routes/app._index.tsx (Remix)
import { Page, Layout, Card, Button } from '@shopify/polaris';
import { useAppBridge } from '@shopify/app-bridge-react';

export default function Index() {
  const app = useAppBridge();
  
  return (
    <Page title="Dashboard">
      <Layout>
        <Layout.Section>
          <Card>
            <p>Your app content here</p>
            <Button 
              primary 
              onClick={() => {
                app.toast.show('Action completed');
              }}
            >
              Take Action
            </Button>
          </Card>
        </Layout.Section>
      </Layout>
    </Page>
  );
}
```

#### 2. Performance Monitoring
```javascript
// Performance tracking implementation
const trackPerformance = () => {
  // Largest Contentful Paint
  new PerformanceObserver((list) => {
    const entries = list.getEntries();
    const lastEntry = entries[entries.length - 1];
    console.log('LCP:', lastEntry.startTime);
    // Should be ≤ 2500ms
  }).observe({entryTypes: ['largest-contentful-paint']});
  
  // Cumulative Layout Shift
  let cumulativeLayoutShift = 0;
  new PerformanceObserver((list) => {
    for (const entry of list.getEntries()) {
      if (!entry.hadRecentInput) {
        cumulativeLayoutShift += entry.value;
      }
    }
    console.log('CLS:', cumulativeLayoutShift);
    // Should be ≤ 0.1
  }).observe({entryTypes: ['layout-shift']});
};
```

#### 3. OAuth Integration
```javascript
// Seamless signup implementation
export const loader = async ({ request }) => {
  const url = new URL(request.url);
  const shop = url.searchParams.get('shop');
  
  if (!shop) {
    return redirect('/install');
  }
  
  // Check if app is already installed
  const session = await sessionStorage.findSessionsByShop(shop);
  
  if (!session) {
    // Redirect to OAuth
    const authUrl = await shopify.auth.begin({
      shop,
      callbackPath: "/auth/callback",
      isOnline: false,
      rawRequest: request,
    });
    
    return redirect(authUrl);
  }
  
  return json({ shop, authenticated: true });
};
```

### Performance Standards
- **LCP**: ≤ 2.5 seconds
- **CLS**: ≤ 0.1
- **INP**: ≤ 200 milliseconds
- **Lighthouse Impact**: ≤ 10-point reduction

---

## Latest Release Notes (2025-01)

### Key API Changes with Examples

#### 1. Automatic Discounts - Optional Minimum Requirements
```graphql
# Before (2024-10): minimumRequirement was required
mutation discountAutomaticBasicCreate($automaticBasicDiscount: DiscountAutomaticBasicInput!) {
  discountAutomaticBasicCreate(automaticBasicDiscount: $automaticBasicDiscount) {
    automaticDiscountNode {
      id
      automaticDiscount {
        ... on DiscountAutomaticBasic {
          title
          minimumRequirement {  # This was required
            greaterThanOrEqualToQuantity
          }
        }
      }
    }
  }
}

# Now (2025-01): minimumRequirement is optional
mutation discountAutomaticBasicCreate($automaticBasicDiscount: DiscountAutomaticBasicInput!) {
  discountAutomaticBasicCreate(automaticBasicDiscount: $automaticBasicDiscount) {
    automaticDiscountNode {
      id
      automaticDiscount {
        ... on DiscountAutomaticBasic {
          title
          # minimumRequirement is now optional
        }
      }
    }
  }
}
```

#### 2. Multiple Fulfillment Holds
```graphql
# New capability: Multiple holds per fulfillment order
mutation fulfillmentOrderHold($id: ID!, $reason: FulfillmentHoldReason!, $reasonNotes: String) {
  fulfillmentOrderHold(id: $id, reason: $reason, reasonNotes: $reasonNotes) {
    fulfillmentOrder {
      id
      status
      holds {  # Now returns array instead of single hold
        reason
        reasonNotes
        heldByApp  # New field replaces heldBy
      }
    }
    userErrors {
      field
      message
    }
  }
}
```

#### 3. Enhanced Payment Verification
```graphql
# New 3DS verification mutation
mutation verificationSessionRedirect($id: ID!, $redirectUrl: String!) {
  verificationSessionRedirect(id: $id, redirectUrl: $redirectUrl) {
    verificationSession {
      id
      status
      redirectUrl
    }
    userErrors {
      field
      message
    }
  }
}
```

#### 4. Custom ID Support and Upserts
```graphql
# Product upsert with custom ID
mutation productUpsert($productSet: ProductSetInput!, $ifProductDoesNotExist: ProductUpsertIfProductDoesNotExistInput) {
  productSet(productSet: $productSet, ifProductDoesNotExist: $ifProductDoesNotExist) {
    product {
      id
      title
    }
    userErrors {
      field
      message
    }
  }
}

# Customer upsert
mutation customerUpsert($customerInput: CustomerInput!) {
  customerUpsert(input: $customerInput) {
    customer {
      id
      email
    }
    userErrors {
      field
      message
    }
  }
}
```

### Migration Checklist
- [ ] Update to GraphQL Admin API if using REST
- [ ] Test automatic discount mutations without minimumRequirement
- [ ] Update fulfillment hold handling for multiple holds
- [ ] Implement new payment verification flows
- [ ] Test metafield permission changes
- [ ] Update webhook handlers for new topic representations

---

## Error Handling Best Practices

### GraphQL Error Handling
```javascript
const handleGraphQLResponse = async (response) => {
  const result = await response.json();
  
  // Check for GraphQL errors
  if (result.errors) {
    console.error('GraphQL Errors:', result.errors);
    result.errors.forEach(error => {
      console.error(`Error: ${error.message}`);
      if (error.locations) {
        console.error(`Location: Line ${error.locations[0].line}, Column ${error.locations[0].column}`);
      }
    });
  }
  
  // Check for user errors in mutations
  if (result.data) {
    const mutationKey = Object.keys(result.data)[0];
    const mutationResult = result.data[mutationKey];
    
    if (mutationResult?.userErrors?.length > 0) {
      console.error('User Errors:', mutationResult.userErrors);
      mutationResult.userErrors.forEach(error => {
        console.error(`Field: ${error.field}, Message: ${error.message}`);
      });
    }
  }
  
  return result;
};
```

### REST Error Handling
```javascript
const handleRESTResponse = async (response) => {
  if (!response.ok) {
    const errorData = await response.json();
    
    switch (response.status) {
      case 401:
        throw new Error('Authentication failed. Check your access token.');
      case 403:
        throw new Error('Access denied. Check your app permissions.');
      case 429:
        const retryAfter = response.headers.get('Retry-After');
        throw new Error(`Rate limited. Retry after ${retryAfter} seconds.`);
      case 422:
        throw new Error(`Validation error: ${JSON.stringify(errorData.errors)}`);
      default:
        throw new Error(`API error: ${response.status} ${response.statusText}`);
    }
  }
  
  return response.json();
};
```

---

## Testing and Validation

### GraphQL Query Testing
```javascript
// Test query validity
const testQuery = `
  query testProducts {
    products(first: 1) {
      edges {
        node {
          id
          title
        }
      }
    }
  }
`;

// Validate against schema
const validateQuery = async (query) => {
  try {
    const response = await admin.graphql(query);
    const result = await response.json();
    return !result.errors;
  } catch (error) {
    console.error('Query validation failed:', error);
    return false;
  }
};
```

---

## Resources and Tools

### Official Libraries
```bash
# Node.js
npm install @shopify/shopify-api @shopify/polaris @shopify/app-bridge-react

# Ruby
gem install shopify_api

# Python
pip install shopify-python-api
```

### Development Tools
- **Shopify CLI**: `npm install -g @shopify/cli @shopify/theme`
- **GraphiQL Explorer**: Built into Shopify CLI
- **Partner Dashboard**: https://partners.shopify.com
- **App Store Connect**: For app submissions

---

*Last Updated: January 2025 | Complete with API Examples and Code Samples*