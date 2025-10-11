import React, {useEffect, useState} from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  Image,
  ActivityIndicator,
} from 'react-native';
import ProductCard from '../components/ProductCard';
import ShopifyService from '../services/ShopifyService';
import {STORE_CONFIG} from '../config/storeConfig';

const HomeScreen = ({navigation}) => {
  const [loading, setLoading] = useState(true);
  const [featuredProducts, setFeaturedProducts] = useState([]);
  const [collections, setCollections] = useState([]);
  const [blocks, setBlocks] = useState([]);

  useEffect(() => {
    loadHomeData();
  }, []);

  const loadHomeData = async () => {
    try {
      setLoading(true);
      
      // Load featured products
      const productsData = await ShopifyService.getProducts(8);
      setFeaturedProducts(productsData.products.edges.map(edge => edge.node));

      // Load collections
      const collectionsData = await ShopifyService.getCollections(6);
      setCollections(collectionsData.collections.edges.map(edge => edge.node));

      // Set up blocks from config
      setBlocks(STORE_CONFIG.layout.homeBlocks || getDefaultBlocks());
    } catch (error) {
      console.error('Error loading home data:', error);
    } finally {
      setLoading(false);
    }
  };

  const getDefaultBlocks = () => [
    {type: 'hero', title: 'Welcome to ' + STORE_CONFIG.appName},
    {type: 'featured-products', title: 'Featured Products'},
    {type: 'collections', title: 'Shop by Category'},
  ];

  const renderBlock = (block, index) => {
    switch (block.type) {
      case 'hero':
        return renderHeroBlock(block, index);
      case 'featured-products':
        return renderFeaturedProductsBlock(block, index);
      case 'collections':
        return renderCollectionsBlock(block, index);
      case 'banner':
        return renderBannerBlock(block, index);
      default:
        return null;
    }
  };

  const renderHeroBlock = (block, index) => (
    <View key={index} style={styles.heroBlock}>
      <Image 
        source={{uri: STORE_CONFIG.splashScreen || 'https://via.placeholder.com/400x200'}} 
        style={styles.heroImage}
      />
      <View style={styles.heroContent}>
        <Text style={styles.heroTitle}>{block.title}</Text>
        <Text style={styles.heroSubtitle}>{block.subtitle || 'Discover amazing products'}</Text>
        <TouchableOpacity style={styles.heroButton}>
          <Text style={styles.heroButtonText}>Shop Now</Text>
        </TouchableOpacity>
      </View>
    </View>
  );

  const renderFeaturedProductsBlock = (block, index) => (
    <View key={index} style={styles.section}>
      <Text style={styles.sectionTitle}>{block.title}</Text>
      <FlatList
        data={featuredProducts}
        renderItem={({item}) => (
          <ProductCard
            product={item}
            onPress={product => navigation.navigate('Product', {handle: product.handle})}
            style={styles.productCard}
          />
        )}
        keyExtractor={item => item.id}
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.productsList}
      />
    </View>
  );

  const renderCollectionsBlock = (block, index) => (
    <View key={index} style={styles.section}>
      <Text style={styles.sectionTitle}>{block.title}</Text>
      <FlatList
        data={collections}
        renderItem={({item}) => (
          <TouchableOpacity
            style={styles.collectionCard}
            onPress={() => navigation.navigate('Collection', {handle: item.handle})}>
            <Image
              source={{uri: item.image?.url || 'https://via.placeholder.com/150x100'}}
              style={styles.collectionImage}
            />
            <Text style={styles.collectionTitle}>{item.title}</Text>
          </TouchableOpacity>
        )}
        keyExtractor={item => item.id}
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.collectionsList}
      />
    </View>
  );

  const renderBannerBlock = (block, index) => (
    <View key={index} style={styles.bannerBlock}>
      <Image source={{uri: block.image}} style={styles.bannerImage} />
      <TouchableOpacity style={styles.bannerContent}>
        <Text style={styles.bannerTitle}>{block.title}</Text>
        <Text style={styles.bannerSubtitle}>{block.subtitle}</Text>
      </TouchableOpacity>
    </View>
  );

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={STORE_CONFIG.primaryColor} />
      </View>
    );
  }

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      {blocks.map((block, index) => renderBlock(block, index))}
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: STORE_CONFIG.backgroundColor || '#f5f5f5',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  heroBlock: {
    position: 'relative',
    height: 250,
    marginBottom: 20,
  },
  heroImage: {
    width: '100%',
    height: '100%',
    resizeMode: 'cover',
  },
  heroContent: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: 'rgba(0,0,0,0.6)',
    padding: 20,
  },
  heroTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 8,
  },
  heroSubtitle: {
    fontSize: 16,
    color: '#fff',
    marginBottom: 16,
  },
  heroButton: {
    backgroundColor: STORE_CONFIG.primaryColor || '#007AFF',
    paddingVertical: 12,
    paddingHorizontal: 24,
    borderRadius: 8,
    alignSelf: 'flex-start',
  },
  heroButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    marginHorizontal: 16,
    marginBottom: 16,
    color: STORE_CONFIG.textColor || '#333',
  },
  productsList: {
    paddingHorizontal: 16,
  },
  productCard: {
    width: 200,
    marginRight: 16,
  },
  collectionsList: {
    paddingHorizontal: 16,
  },
  collectionCard: {
    width: 120,
    marginRight: 16,
    alignItems: 'center',
  },
  collectionImage: {
    width: 120,
    height: 80,
    borderRadius: 8,
    marginBottom: 8,
  },
  collectionTitle: {
    fontSize: 14,
    fontWeight: '600',
    textAlign: 'center',
    color: STORE_CONFIG.textColor || '#333',
  },
  bannerBlock: {
    marginHorizontal: 16,
    marginBottom: 20,
    borderRadius: 8,
    overflow: 'hidden',
  },
  bannerImage: {
    width: '100%',
    height: 120,
    resizeMode: 'cover',
  },
  bannerContent: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: 'rgba(0,0,0,0.5)',
    padding: 12,
  },
  bannerTitle: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  bannerSubtitle: {
    color: '#fff',
    fontSize: 14,
  },
});

export default HomeScreen;