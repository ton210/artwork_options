import {STORE_CONFIG, API_CONFIG} from '../config/storeConfig';

class ShopifyService {
  constructor() {
    this.storefrontToken = STORE_CONFIG.storefrontAccessToken;
    this.apiUrl = API_CONFIG.storefront;
  }

  async makeRequest(query, variables = {}) {
    try {
      const response = await fetch(this.apiUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Shopify-Storefront-Access-Token': this.storefrontToken,
        },
        body: JSON.stringify({
          query,
          variables,
        }),
      });

      const data = await response.json();
      if (data.errors) {
        throw new Error(data.errors[0].message);
      }
      return data.data;
    } catch (error) {
      console.error('Shopify API Error:', error);
      throw error;
    }
  }

  // Get products with pagination
  async getProducts(first = 20, after = null, query = '') {
    const gqlQuery = `
      query getProducts($first: Int!, $after: String, $query: String) {
        products(first: $first, after: $after, query: $query) {
          edges {
            node {
              id
              title
              description
              handle
              images(first: 5) {
                edges {
                  node {
                    id
                    url
                    altText
                  }
                }
              }
              priceRange {
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
                    price {
                      amount
                      currencyCode
                    }
                    availableForSale
                    selectedOptions {
                      name
                      value
                    }
                  }
                }
              }
              tags
              availableForSale
            }
          }
          pageInfo {
            hasNextPage
            endCursor
          }
        }
      }
    `;

    return this.makeRequest(gqlQuery, {first, after, query});
  }

  // Get single product by handle
  async getProduct(handle) {
    const gqlQuery = `
      query getProduct($handle: String!) {
        product(handle: $handle) {
          id
          title
          description
          descriptionHtml
          handle
          images(first: 10) {
            edges {
              node {
                id
                url
                altText
              }
            }
          }
          priceRange {
            minVariantPrice {
              amount
              currencyCode
            }
            maxVariantPrice {
              amount
              currencyCode
            }
          }
          variants(first: 250) {
            edges {
              node {
                id
                title
                price {
                  amount
                  currencyCode
                }
                compareAtPrice {
                  amount
                  currencyCode
                }
                availableForSale
                selectedOptions {
                  name
                  value
                }
                image {
                  id
                  url
                  altText
                }
              }
            }
          }
          options {
            id
            name
            values
          }
          tags
          availableForSale
        }
      }
    `;

    return this.makeRequest(gqlQuery, {handle});
  }

  // Get collections
  async getCollections(first = 20) {
    const gqlQuery = `
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
                    priceRange {
                      minVariantPrice {
                        amount
                        currencyCode
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

    return this.makeRequest(gqlQuery, {first});
  }

  // Get products in collection
  async getCollectionProducts(handle, first = 20, after = null) {
    const gqlQuery = `
      query getCollectionProducts($handle: String!, $first: Int!, $after: String) {
        collection(handle: $handle) {
          id
          title
          description
          products(first: $first, after: $after) {
            edges {
              node {
                id
                title
                description
                handle
                images(first: 3) {
                  edges {
                    node {
                      id
                      url
                      altText
                    }
                  }
                }
                priceRange {
                  minVariantPrice {
                    amount
                    currencyCode
                  }
                }
                variants(first: 3) {
                  edges {
                    node {
                      id
                      availableForSale
                    }
                  }
                }
                availableForSale
              }
            }
            pageInfo {
              hasNextPage
              endCursor
            }
          }
        }
      }
    `;

    return this.makeRequest(gqlQuery, {handle, first, after});
  }

  // Create checkout
  async createCheckout(lineItems) {
    const gqlQuery = `
      mutation checkoutCreate($input: CheckoutCreateInput!) {
        checkoutCreate(input: $input) {
          checkout {
            id
            webUrl
            totalTax {
              amount
              currencyCode
            }
            totalPrice {
              amount
              currencyCode
            }
            subtotalPrice {
              amount
              currencyCode
            }
            lineItems(first: 250) {
              edges {
                node {
                  id
                  title
                  quantity
                  variant {
                    id
                    title
                    image {
                      url
                    }
                    price {
                      amount
                      currencyCode
                    }
                  }
                }
              }
            }
          }
          checkoutUserErrors {
            field
            message
          }
        }
      }
    `;

    return this.makeRequest(gqlQuery, {input: {lineItems}});
  }

  // Update checkout
  async updateCheckout(checkoutId, lineItems) {
    const gqlQuery = `
      mutation checkoutLineItemsReplace($checkoutId: ID!, $lineItems: [CheckoutLineItemInput!]!) {
        checkoutLineItemsReplace(checkoutId: $checkoutId, lineItems: $lineItems) {
          checkout {
            id
            webUrl
            totalTax {
              amount
              currencyCode
            }
            totalPrice {
              amount
              currencyCode
            }
            subtotalPrice {
              amount
              currencyCode
            }
            lineItems(first: 250) {
              edges {
                node {
                  id
                  title
                  quantity
                  variant {
                    id
                    title
                    image {
                      url
                    }
                    price {
                      amount
                      currencyCode
                    }
                  }
                }
              }
            }
          }
          checkoutUserErrors {
            field
            message
          }
        }
      }
    `;

    return this.makeRequest(gqlQuery, {checkoutId, lineItems});
  }
}

export default new ShopifyService();