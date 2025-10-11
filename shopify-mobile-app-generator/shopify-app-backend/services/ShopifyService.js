const { shopifyApi } = require('@shopify/shopify-api');

class ShopifyService {
  constructor(session) {
    this.session = session;
    this.client = new shopifyApi.clients.Graphql({
      session: this.session
    });
    this.restClient = new shopifyApi.clients.Rest({
      session: this.session
    });
  }

  async getStoreInfo() {
    try {
      const query = `
        query {
          shop {
            id
            name
            email
            domain
            myshopifyDomain
            description
            currencyCode
            primaryDomain {
              url
            }
            plan {
              displayName
            }
          }
        }
      `;

      const response = await this.client.query({ data: query });
      return response.body.data.shop;
    } catch (error) {
      console.error('Error fetching store info:', error);
      throw error;
    }
  }

  async getProducts(first = 20, after = null) {
    try {
      const query = `
        query getProducts($first: Int!, $after: String) {
          products(first: $first, after: $after) {
            edges {
              node {
                id
                title
                description
                handle
                status
                createdAt
                updatedAt
                images(first: 5) {
                  edges {
                    node {
                      id
                      url
                      altText
                    }
                  }
                }
                priceRangeV2 {
                  minVariantPrice {
                    amount
                    currencyCode
                  }
                  maxVariantPrice {
                    amount
                    currencyCode
                  }
                }
                variants(first: 10) {
                  edges {
                    node {
                      id
                      title
                      price
                      inventoryQuantity
                      availableForSale
                    }
                  }
                }
                totalInventory
              }
            }
            pageInfo {
              hasNextPage
              endCursor
            }
          }
        }
      `;

      const response = await this.client.query({
        data: { query, variables: { first, after } }
      });

      return response.body.data.products;
    } catch (error) {
      console.error('Error fetching products:', error);
      throw error;
    }
  }

  async getCollections(first = 20) {
    try {
      const query = `
        query getCollections($first: Int!) {
          collections(first: $first) {
            edges {
              node {
                id
                title
                description
                handle
                image {
                  id
                  url
                  altText
                }
                productsCount
                products(first: 8) {
                  edges {
                    node {
                      id
                      title
                      images(first: 1) {
                        edges {
                          node {
                            url
                            altText
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      `;

      const response = await this.client.query({
        data: { query, variables: { first } }
      });

      return response.body.data.collections;
    } catch (error) {
      console.error('Error fetching collections:', error);
      throw error;
    }
  }

  async createStorefrontAccessToken() {
    try {
      const mutation = `
        mutation storefrontAccessTokenCreate($input: StorefrontAccessTokenInput!) {
          storefrontAccessTokenCreate(input: $input) {
            storefrontAccessToken {
              accessToken
              accessScope
            }
            userErrors {
              field
              message
            }
          }
        }
      `;

      const variables = {
        input: {
          title: 'Mobile App Storefront Token'
        }
      };

      const response = await this.client.query({
        data: { query: mutation, variables }
      });

      const result = response.body.data.storefrontAccessTokenCreate;
      
      if (result.userErrors && result.userErrors.length > 0) {
        throw new Error(result.userErrors[0].message);
      }

      return result.storefrontAccessToken.accessToken;
    } catch (error) {
      console.error('Error creating storefront access token:', error);
      throw error;
    }
  }

  async getThemes() {
    try {
      const themes = await this.restClient.get({ path: 'themes' });
      return themes.body.themes;
    } catch (error) {
      console.error('Error fetching themes:', error);
      throw error;
    }
  }

  async getMetafields(ownerId, ownerResource) {
    try {
      const query = `
        query getMetafields($ownerId: ID!, $ownerType: MetafieldOwnerType!) {
          metafields(ownerId: $ownerId, ownerType: $ownerType, first: 50) {
            edges {
              node {
                id
                namespace
                key
                value
                type
                description
              }
            }
          }
        }
      `;

      const response = await this.client.query({
        data: { 
          query, 
          variables: { 
            ownerId: ownerId, 
            ownerType: ownerResource.toUpperCase()
          } 
        }
      });

      return response.body.data.metafields.edges.map(edge => edge.node);
    } catch (error) {
      console.error('Error fetching metafields:', error);
      throw error;
    }
  }

  async createWebhook(topic, address) {
    try {
      const mutation = `
        mutation webhookSubscriptionCreate($topic: WebhookSubscriptionTopic!, $webhookSubscription: WebhookSubscriptionInput!) {
          webhookSubscriptionCreate(topic: $topic, webhookSubscription: $webhookSubscription) {
            webhookSubscription {
              id
              callbackUrl
              format
              topic
            }
            userErrors {
              field
              message
            }
          }
        }
      `;

      const variables = {
        topic: topic,
        webhookSubscription: {
          callbackUrl: address,
          format: 'JSON'
        }
      };

      const response = await this.client.query({
        data: { query: mutation, variables }
      });

      const result = response.body.data.webhookSubscriptionCreate;
      
      if (result.userErrors && result.userErrors.length > 0) {
        throw new Error(result.userErrors[0].message);
      }

      return result.webhookSubscription;
    } catch (error) {
      console.error('Error creating webhook:', error);
      throw error;
    }
  }

  async getWebhooks() {
    try {
      const query = `
        query {
          webhookSubscriptions(first: 50) {
            edges {
              node {
                id
                callbackUrl
                format
                topic
                createdAt
                updatedAt
              }
            }
          }
        }
      `;

      const response = await this.client.query({ data: query });
      return response.body.data.webhookSubscriptions.edges.map(edge => edge.node);
    } catch (error) {
      console.error('Error fetching webhooks:', error);
      throw error;
    }
  }

  // Analytics and insights
  async getOrdersCount(dateRange = 'LAST_30_DAYS') {
    try {
      const query = `
        query getOrdersCount {
          orders(first: 1) {
            pageInfo {
              hasNextPage
            }
          }
        }
      `;

      const response = await this.client.query({ data: query });
      // This is a simplified version - you'd need to implement proper counting
      return { count: 0 }; // Placeholder
    } catch (error) {
      console.error('Error fetching orders count:', error);
      throw error;
    }
  }
}

module.exports = ShopifyService;