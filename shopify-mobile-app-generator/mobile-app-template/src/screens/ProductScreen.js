import React, {useEffect, useState} from 'react';
import {
  View,
  Text,
  ScrollView,
  Image,
  TouchableOpacity,
  StyleSheet,
  Dimensions,
  ActivityIndicator,
  Alert,
} from 'react-native';
import ShopifyService from '../services/ShopifyService';
import {STORE_CONFIG} from '../config/storeConfig';

const {width} = Dimensions.get('window');

const ProductScreen = ({route, navigation}) => {
  const {handle} = route.params;
  const [product, setProduct] = useState(null);
  const [selectedVariant, setSelectedVariant] = useState(null);
  const [selectedOptions, setSelectedOptions] = useState({});
  const [loading, setLoading] = useState(true);
  const [currentImageIndex, setCurrentImageIndex] = useState(0);

  useEffect(() => {
    loadProduct();
  }, [handle]);

  const loadProduct = async () => {
    try {
      setLoading(true);
      const data = await ShopifyService.getProduct(handle);
      setProduct(data.product);
      
      // Set default variant
      if (data.product.variants.edges.length > 0) {
        const defaultVariant = data.product.variants.edges[0].node;
        setSelectedVariant(defaultVariant);
        
        // Set default options
        const defaultOptions = {};
        defaultVariant.selectedOptions.forEach(option => {
          defaultOptions[option.name] = option.value;
        });
        setSelectedOptions(defaultOptions);
      }
    } catch (error) {
      console.error('Error loading product:', error);
      Alert.alert('Error', 'Could not load product');
    } finally {
      setLoading(false);
    }
  };

  const handleOptionSelect = (optionName, optionValue) => {
    const newOptions = {...selectedOptions, [optionName]: optionValue};
    setSelectedOptions(newOptions);

    // Find matching variant
    const matchingVariant = product.variants.edges.find(edge => {
      const variant = edge.node;
      return variant.selectedOptions.every(option => 
        newOptions[option.name] === option.value
      );
    });

    if (matchingVariant) {
      setSelectedVariant(matchingVariant.node);
    }
  };

  const addToCart = () => {
    if (!selectedVariant) {
      Alert.alert('Error', 'Please select a variant');
      return;
    }

    if (!selectedVariant.availableForSale) {
      Alert.alert('Error', 'This variant is out of stock');
      return;
    }

    // Navigate to cart or add to cart logic
    Alert.alert('Success', 'Added to cart!');
  };

  const buyNow = async () => {
    if (!selectedVariant) {
      Alert.alert('Error', 'Please select a variant');
      return;
    }

    if (!selectedVariant.availableForSale) {
      Alert.alert('Error', 'This variant is out of stock');
      return;
    }

    try {
      const lineItems = [{
        variantId: selectedVariant.id,
        quantity: 1,
      }];

      const checkoutData = await ShopifyService.createCheckout(lineItems);
      
      if (checkoutData.checkoutCreate.checkout) {
        // Navigate to checkout webview
        navigation.navigate('Checkout', {
          checkoutUrl: checkoutData.checkoutCreate.checkout.webUrl
        });
      }
    } catch (error) {
      console.error('Error creating checkout:', error);
      Alert.alert('Error', 'Could not start checkout');
    }
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={STORE_CONFIG.primaryColor} />
      </View>
    );
  }

  if (!product) {
    return (
      <View style={styles.errorContainer}>
        <Text style={styles.errorText}>Product not found</Text>
      </View>
    );
  }

  const images = product.images.edges.map(edge => edge.node);
  const price = selectedVariant?.price || product.priceRange.minVariantPrice;
  const compareAtPrice = selectedVariant?.compareAtPrice;

  return (
    <ScrollView style={styles.container}>
      {/* Image Gallery */}
      <View style={styles.imageContainer}>
        <ScrollView
          horizontal
          pagingEnabled
          showsHorizontalScrollIndicator={false}
          onScroll={(event) => {
            const index = Math.round(event.nativeEvent.contentOffset.x / width);
            setCurrentImageIndex(index);
          }}
          scrollEventThrottle={16}
        >
          {images.map((image, index) => (
            <Image
              key={image.id}
              source={{uri: image.url}}
              style={styles.productImage}
            />
          ))}
        </ScrollView>
        
        {images.length > 1 && (
          <View style={styles.imageIndicators}>
            {images.map((_, index) => (
              <View
                key={index}
                style={[
                  styles.indicator,
                  currentImageIndex === index && styles.activeIndicator
                ]}
              />
            ))}
          </View>
        )}
      </View>

      {/* Product Info */}
      <View style={styles.productInfo}>
        <Text style={styles.productTitle}>{product.title}</Text>
        
        <View style={styles.priceContainer}>
          <Text style={styles.price}>
            {price.currencyCode} {parseFloat(price.amount).toFixed(2)}
          </Text>
          {compareAtPrice && parseFloat(compareAtPrice.amount) > parseFloat(price.amount) && (
            <Text style={styles.compareAtPrice}>
              {compareAtPrice.currencyCode} {parseFloat(compareAtPrice.amount).toFixed(2)}
            </Text>
          )}
        </View>

        {/* Product Options */}
        {product.options.map(option => (
          <View key={option.id} style={styles.optionContainer}>
            <Text style={styles.optionTitle}>{option.name}</Text>
            <View style={styles.optionValues}>
              {option.values.map(value => (
                <TouchableOpacity
                  key={value}
                  style={[
                    styles.optionValue,
                    selectedOptions[option.name] === value && styles.selectedOptionValue
                  ]}
                  onPress={() => handleOptionSelect(option.name, value)}
                >
                  <Text style={[
                    styles.optionValueText,
                    selectedOptions[option.name] === value && styles.selectedOptionValueText
                  ]}>
                    {value}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>
          </View>
        ))}

        {/* Description */}
        <View style={styles.descriptionContainer}>
          <Text style={styles.descriptionTitle}>Description</Text>
          <Text style={styles.description}>{product.description}</Text>
        </View>

        {/* Action Buttons */}
        <View style={styles.actionButtons}>
          <TouchableOpacity
            style={[styles.addToCartButton, !selectedVariant?.availableForSale && styles.disabledButton]}
            onPress={addToCart}
            disabled={!selectedVariant?.availableForSale}
          >
            <Text style={styles.addToCartText}>
              {selectedVariant?.availableForSale ? 'Add to Cart' : 'Out of Stock'}
            </Text>
          </TouchableOpacity>
          
          <TouchableOpacity
            style={[styles.buyNowButton, !selectedVariant?.availableForSale && styles.disabledButton]}
            onPress={buyNow}
            disabled={!selectedVariant?.availableForSale}
          >
            <Text style={styles.buyNowText}>Buy Now</Text>
          </TouchableOpacity>
        </View>
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  errorText: {
    fontSize: 18,
    color: '#666',
  },
  imageContainer: {
    position: 'relative',
  },
  productImage: {
    width: width,
    height: width,
    resizeMode: 'cover',
  },
  imageIndicators: {
    position: 'absolute',
    bottom: 16,
    left: 0,
    right: 0,
    flexDirection: 'row',
    justifyContent: 'center',
  },
  indicator: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: 'rgba(255,255,255,0.5)',
    marginHorizontal: 4,
  },
  activeIndicator: {
    backgroundColor: '#fff',
  },
  productInfo: {
    padding: 16,
  },
  productTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 8,
  },
  priceContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
  },
  price: {
    fontSize: 20,
    fontWeight: 'bold',
    color: STORE_CONFIG.primaryColor || '#007AFF',
    marginRight: 8,
  },
  compareAtPrice: {
    fontSize: 16,
    color: '#999',
    textDecorationLine: 'line-through',
  },
  optionContainer: {
    marginBottom: 16,
  },
  optionTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 8,
    color: '#333',
  },
  optionValues: {
    flexDirection: 'row',
    flexWrap: 'wrap',
  },
  optionValue: {
    borderWidth: 1,
    borderColor: '#ddd',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 6,
    marginRight: 8,
    marginBottom: 8,
  },
  selectedOptionValue: {
    borderColor: STORE_CONFIG.primaryColor || '#007AFF',
    backgroundColor: STORE_CONFIG.primaryColor || '#007AFF',
  },
  optionValueText: {
    fontSize: 14,
    color: '#333',
  },
  selectedOptionValueText: {
    color: '#fff',
  },
  descriptionContainer: {
    marginBottom: 24,
  },
  descriptionTitle: {
    fontSize: 18,
    fontWeight: '600',
    marginBottom: 8,
    color: '#333',
  },
  description: {
    fontSize: 14,
    lineHeight: 20,
    color: '#666',
  },
  actionButtons: {
    marginBottom: 20,
  },
  addToCartButton: {
    backgroundColor: '#f0f0f0',
    paddingVertical: 14,
    borderRadius: 8,
    marginBottom: 12,
    alignItems: 'center',
  },
  addToCartText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
  },
  buyNowButton: {
    backgroundColor: STORE_CONFIG.primaryColor || '#007AFF',
    paddingVertical: 14,
    borderRadius: 8,
    alignItems: 'center',
  },
  buyNowText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#fff',
  },
  disabledButton: {
    backgroundColor: '#ccc',
  },
});

export default ProductScreen;