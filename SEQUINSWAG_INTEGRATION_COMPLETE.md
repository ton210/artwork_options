# SequinSwag → Vendor Portal Integration Complete

## Overview
The SequinSwag PHP custom dashboard has been successfully configured to sync orders directly with the MunchMakers Vendor Portal instead of WordPress/WooCommerce.

## What Changed

### 1. SequinSwag Dashboard (`/Users/tomernahumi/Documents/Plugins/sequinswag-php`)

#### Updated Files:
- **`.env`** - Added vendor portal configuration:
  ```env
  VENDOR_PORTAL_URL=http://localhost:5000
  VENDOR_PORTAL_WEBHOOK_SECRET=sequinswag-webhook-secret-2024
  VENDOR_PORTAL_API_KEY=sequinswag-2024-api-key
  ```

- **`includes/Controllers/CartController.php`** - Added vendor portal sync:
  - Added `syncOrderToVendorPortal()` method
  - Added `backupOrderForRetry()` method for failed syncs
  - Integrated sync call in `processCheckout()` workflow

#### How It Works:
1. Customer completes checkout on SequinSwag website
2. Order is processed with Stripe payment
3. Order is saved to SequinSwag PostgreSQL database
4. **NEW:** Order is automatically synced to Vendor Portal via webhook
5. Order confirmation email is sent
6. Slack notification is sent

### 2. Vendor Portal (`/Users/tomernahumi/Documents/Plugins/vendor-portal-clean`)

#### Updated Files:
- **`.env`** - Added SequinSwag integration settings:
  ```env
  SEQUINSWAG_BASE_URL=http://localhost:8000
  SEQUINSWAG_WEBHOOK_SECRET=sequinswag-webhook-secret-2024
  ```

#### Existing Integration (Already in place):
- **`backend/src/controllers/sequinSwagController.js`** - Handles incoming webhooks
- **`backend/src/routes/sequinswag.js`** - Webhook endpoints
- Webhook endpoints already available:
  - `POST /api/sequinswag/orders/webhook` - Receives new orders
  - `POST /api/sequinswag/orders/sync` - Manual sync endpoint
  - `GET /api/sequinswag/orders/test` - Test connection

## Order Flow

```
SequinSwag Customer Checkout
         ↓
   Payment Processing (Stripe)
         ↓
   Save to SequinSwag DB (PostgreSQL)
         ↓
   📤 Webhook to Vendor Portal ← NEW!
         ↓
   Create Order in Vendor Portal DB
         ↓
   Auto-assign to Vendors
         ↓
   📧 Send Confirmation Email
         ↓
   💬 Send Slack Notification
```

## Order Data Transformation

### SequinSwag Format → Vendor Portal Format

```javascript
{
  order_number: 'SEQ-20250116-ABC123',
  customer_name: 'John Doe',
  customer_email: 'john@example.com',
  customer_phone: '555-1234',
  line_items: [
    {
      name: 'Custom Sequin Pillow',
      sku: 'SEQUIN-PILLOW-001',
      quantity: 2,
      price: 49.99,
      total: 99.98,
      custom_design: 'https://...'  // ← Custom design image
    }
  ],
  subtotal: 99.98,
  shipping_cost: 10.00,
  tax_amount: 9.00,
  total_amount: 118.98,
  currency: 'USD',
  status: 'processing',
  payment_status: 'paid',
  payment_method: 'Credit Card',
  order_date: '2025-01-16T10:30:00Z',
  source: 'sequinswag'
}
```

## Security

### Webhook Signature Verification
- Uses HMAC SHA256 signature
- Secret key: `sequinswag-webhook-secret-2024`
- Header: `X-SequinSwag-Signature: sha256=...`
- Both sides validate signatures to prevent tampering

## Testing

### Test Script Created:
`/Users/tomernahumi/Documents/Plugins/vendor-portal-clean/backend/test-sequinswag-integration.js`

### To Run Tests:

1. **Start the Vendor Portal backend:**
   ```bash
   cd /Users/tomernahumi/Documents/Plugins/vendor-portal-clean/backend
   npm start
   ```

2. **Run the integration test:**
   ```bash
   node test-sequinswag-integration.js
   ```

3. **Expected Output:**
   ```
   ✅ Webhook sent successfully!
   Response:
     Status: 201 Created
     Data: {
       "success": true,
       "message": "Order synced successfully",
       "data": {
         "order_id": 123,
         "external_order_id": "SEQ-TEST-...",
         "store_name": "SequinSwag"
       }
     }
   ```

### Manual Testing:

1. **Test from SequinSwag webhook file:**
   ```bash
   cd /Users/tomernahumi/Documents/Plugins/sequinswag-php
   php webhook-order-sync.php
   ```

2. **Check Vendor Portal Dashboard:**
   - Navigate to: http://localhost:3000/admin/orders
   - Look for orders with source "SequinSwag"
   - Verify order details and line items

## Failover & Reliability

### Automatic Retry Mechanism:
If the vendor portal sync fails:
1. Error is logged
2. Order is backed up to: `sequinswag-php/cache/vendor_portal_sync/`
3. Customer order still succeeds (doesn't fail checkout)
4. Admin can manually retry failed syncs later

### Backup Files:
- Location: `sequinswag-php/cache/vendor_portal_sync/`
- Format: `order_SEQ-XXXXXX.json`
- Includes: Original order data + sync status

## Production Deployment

### Update Environment Variables:

**SequinSwag (Heroku):**
```bash
heroku config:set VENDOR_PORTAL_URL=https://vendors.munchmakers.com
heroku config:set VENDOR_PORTAL_WEBHOOK_SECRET=your-production-secret
```

**Vendor Portal (Heroku):**
```bash
heroku config:set SEQUINSWAG_BASE_URL=https://sequinswag.herokuapp.com
heroku config:set SEQUINSWAG_WEBHOOK_SECRET=your-production-secret
```

### Important Notes:
- Use HTTPS URLs in production
- Use strong, unique webhook secret
- Monitor logs for sync failures
- Set up alerts for failed webhooks

## Monitoring

### Check Order Sync Status:

**SequinSwag Logs:**
```bash
tail -f sequinswag-php/cache/vendor_portal_sync.log
```

**Vendor Portal Logs:**
```bash
heroku logs --tail --app your-vendor-portal-app
```

### Success Indicators:
- ✅ `Order SEQ-XXX synced to Vendor Portal successfully`
- ✅ `Created new order: [order_id]`
- ✅ `Auto-assigned order [order_id] to vendor [vendor_name]`

### Failure Indicators:
- ❌ `Vendor portal sync error`
- ❌ `cURL error: Connection refused`
- ❌ `Invalid webhook signature`

## Next Steps

1. **Start both applications:**
   - SequinSwag: `cd sequinswag-php && php -S localhost:8000 -t public`
   - Vendor Portal: `cd vendor-portal-clean/backend && npm start`

2. **Run integration test:**
   ```bash
   cd vendor-portal-clean/backend
   node test-sequinswag-integration.js
   ```

3. **Create a test order:**
   - Visit http://localhost:8000
   - Add product to cart
   - Complete checkout
   - Check vendor portal for new order

4. **Verify in Vendor Portal:**
   - Login to admin dashboard
   - Navigate to Orders page
   - Look for order with source "SequinSwag"
   - Check Slack for notification

## Support & Troubleshooting

### Common Issues:

**Issue:** "Vendor portal is not running"
- **Solution:** Start the vendor portal: `cd backend && npm start`

**Issue:** "Connection refused"
- **Solution:** Check that both apps are running and ports are correct

**Issue:** "Invalid webhook signature"
- **Solution:** Ensure `VENDOR_PORTAL_WEBHOOK_SECRET` matches in both `.env` files

**Issue:** "Order not showing in vendor portal"
- **Solution:** Check vendor portal logs for errors
- **Solution:** Check backup files in `cache/vendor_portal_sync/`

### Debug Mode:
Enable detailed logging:
```bash
# Vendor Portal
NODE_ENV=development npm start

# SequinSwag
APP_ENV=development php -S localhost:8000
```

## Summary

✅ **Configuration Complete**
- Both applications configured with correct environment variables
- Webhook secret shared between systems
- SSL certificate verification enabled

✅ **Integration Code Complete**
- SequinSwag sends webhooks on new orders
- Vendor Portal receives and processes webhooks
- Order data transformation implemented
- Signature verification implemented

✅ **Failover Mechanisms**
- Local backup for failed syncs
- Detailed error logging
- Checkout doesn't fail if sync fails

✅ **Testing Tools**
- Integration test script created
- Manual testing endpoints available
- Test connection endpoint available

🎉 **The integration is ready to use!**
