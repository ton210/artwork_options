const { shopifyApi } = require('@shopify/shopify-api');

function authenticateShopify(shopifyApp) {
  return async (req, res, next) => {
    try {
      // Get session from request
      const sessionId = shopifyApp.api.session.getCurrentId({
        isOnline: false,
        rawRequest: req,
        rawResponse: res,
      });

      if (!sessionId) {
        return res.status(401).json({ error: 'No active session found' });
      }

      const session = await shopifyApp.config.sessionStorage.loadSession(sessionId);

      if (!session || !session.accessToken) {
        return res.status(401).json({ error: 'Invalid or expired session' });
      }

      // Verify the session is still valid by making a simple API call
      try {
        const client = new shopifyApi.clients.Rest({
          session: session,
        });
        
        await client.get({ path: 'shop' });
      } catch (error) {
        return res.status(401).json({ error: 'Session expired or invalid' });
      }

      // Add session to response locals
      res.locals.shopify = { session };
      next();
    } catch (error) {
      console.error('Authentication error:', error);
      res.status(401).json({ error: 'Authentication failed' });
    }
  };
}

module.exports = authenticateShopify;