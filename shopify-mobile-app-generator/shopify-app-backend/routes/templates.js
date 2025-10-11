const express = require('express');
const router = express.Router();

// Available templates
const templates = {
  minimal: {
    id: 'minimal',
    name: 'Minimal',
    description: 'Clean and simple design focusing on products',
    preview: '/templates/minimal-preview.png',
    colors: {
      primary: '#000000',
      secondary: '#666666',
      accent: '#999999',
      text: '#333333',
      background: '#FFFFFF'
    },
    features: {
      reviews: true,
      wishlist: true,
      pushNotifications: true,
      socialLogin: false,
      guestCheckout: true
    },
    defaultBlocks: [
      {
        id: '1',
        type: 'hero',
        title: 'Shop Our Collection',
        subtitle: 'Discover quality products',
        order: 0,
        settings: {
          height: 'medium',
          textAlign: 'center'
        }
      },
      {
        id: '2',
        type: 'featured-products',
        title: 'Featured',
        order: 1,
        settings: {
          count: 6,
          layout: 'grid'
        }
      },
      {
        id: '3',
        type: 'collections',
        title: 'Categories',
        order: 2,
        settings: {
          layout: 'horizontal'
        }
      }
    ]
  },
  
  modern: {
    id: 'modern',
    name: 'Modern',
    description: 'Contemporary design with bold colors and modern typography',
    preview: '/templates/modern-preview.png',
    colors: {
      primary: '#007AFF',
      secondary: '#5856D6',
      accent: '#FF9500',
      text: '#1D1D1F',
      background: '#F2F2F7'
    },
    features: {
      reviews: true,
      wishlist: true,
      pushNotifications: true,
      socialLogin: true,
      guestCheckout: true
    },
    defaultBlocks: [
      {
        id: '1',
        type: 'hero',
        title: 'Welcome to Our Store',
        subtitle: 'Discover amazing products tailored for you',
        order: 0,
        settings: {
          height: 'large',
          textAlign: 'left',
          showButton: true,
          buttonText: 'Shop Now'
        }
      },
      {
        id: '2',
        type: 'banner',
        title: 'Special Offer',
        subtitle: 'Get 20% off your first order',
        order: 1,
        settings: {
          backgroundColor: '#FF9500',
          textColor: '#FFFFFF'
        }
      },
      {
        id: '3',
        type: 'featured-products',
        title: 'Trending Now',
        order: 2,
        settings: {
          count: 8,
          layout: 'carousel'
        }
      },
      {
        id: '4',
        type: 'collections',
        title: 'Shop by Category',
        order: 3,
        settings: {
          layout: 'grid',
          columns: 2
        }
      },
      {
        id: '5',
        type: 'testimonials',
        title: 'What Our Customers Say',
        order: 4,
        settings: {
          layout: 'carousel',
          autoPlay: true
        }
      }
    ]
  },

  classic: {
    id: 'classic',
    name: 'Classic',
    description: 'Traditional ecommerce layout with proven conversion patterns',
    preview: '/templates/classic-preview.png',
    colors: {
      primary: '#2C5530',
      secondary: '#D4AF37',
      accent: '#8B4513',
      text: '#2F2F2F',
      background: '#FAFAFA'
    },
    features: {
      reviews: true,
      wishlist: false,
      pushNotifications: true,
      socialLogin: false,
      guestCheckout: true
    },
    defaultBlocks: [
      {
        id: '1',
        type: 'hero',
        title: 'Quality Products Since 1995',
        subtitle: 'Trusted by thousands of customers worldwide',
        order: 0,
        settings: {
          height: 'medium',
          textAlign: 'center',
          overlay: true
        }
      },
      {
        id: '2',
        type: 'featured-products',
        title: 'Best Sellers',
        order: 1,
        settings: {
          count: 4,
          layout: 'grid',
          showPricing: true
        }
      },
      {
        id: '3',
        type: 'text',
        title: 'Our Story',
        content: 'For over 25 years, we have been committed to providing our customers with the highest quality products and exceptional service.',
        order: 2,
        settings: {
          textAlign: 'center',
          padding: 'large'
        }
      },
      {
        id: '4',
        type: 'collections',
        title: 'Shop Our Collections',
        order: 3,
        settings: {
          layout: 'list'
        }
      }
    ]
  },

  bold: {
    id: 'bold',
    name: 'Bold',
    description: 'Eye-catching design with vibrant colors and dynamic layouts',
    preview: '/templates/bold-preview.png',
    colors: {
      primary: '#FF2D92',
      secondary: '#00D4FF',
      accent: '#FFE500',
      text: '#000000',
      background: '#FFFFFF'
    },
    features: {
      reviews: true,
      wishlist: true,
      pushNotifications: true,
      socialLogin: true,
      guestCheckout: true
    },
    defaultBlocks: [
      {
        id: '1',
        type: 'hero',
        title: 'Bold. Beautiful. Yours.',
        subtitle: 'Stand out from the crowd with our exclusive collection',
        order: 0,
        settings: {
          height: 'large',
          textAlign: 'center',
          gradient: true
        }
      },
      {
        id: '2',
        type: 'banner',
        title: 'Flash Sale',
        subtitle: 'Up to 50% off - Limited time only!',
        order: 1,
        settings: {
          backgroundColor: '#FF2D92',
          textColor: '#FFFFFF',
          animation: 'pulse'
        }
      },
      {
        id: '3',
        type: 'featured-products',
        title: 'Hot Picks',
        order: 2,
        settings: {
          count: 6,
          layout: 'masonry',
          showBadges: true
        }
      },
      {
        id: '4',
        type: 'video',
        title: 'See It in Action',
        order: 3,
        settings: {
          aspectRatio: '16:9',
          autoPlay: false
        }
      },
      {
        id: '5',
        type: 'collections',
        title: 'Explore Collections',
        order: 4,
        settings: {
          layout: 'carousel',
          showOverlay: true
        }
      }
    ]
  }
};

// Available blocks
const blockTypes = {
  hero: {
    id: 'hero',
    name: 'Hero Section',
    description: 'Large banner with title, subtitle and call-to-action',
    icon: '🎯',
    category: 'content',
    settings: {
      title: { type: 'text', label: 'Title', required: true },
      subtitle: { type: 'text', label: 'Subtitle' },
      image: { type: 'image', label: 'Background Image' },
      height: { 
        type: 'select', 
        label: 'Height', 
        options: ['small', 'medium', 'large'],
        default: 'medium'
      },
      textAlign: {
        type: 'select',
        label: 'Text Alignment',
        options: ['left', 'center', 'right'],
        default: 'center'
      },
      showButton: { type: 'boolean', label: 'Show Button', default: false },
      buttonText: { type: 'text', label: 'Button Text', default: 'Shop Now' },
      overlay: { type: 'boolean', label: 'Dark Overlay', default: false }
    }
  },

  'featured-products': {
    id: 'featured-products',
    name: 'Featured Products',
    description: 'Showcase selected products in various layouts',
    icon: '⭐',
    category: 'products',
    settings: {
      title: { type: 'text', label: 'Section Title', required: true },
      count: { 
        type: 'number', 
        label: 'Number of Products', 
        min: 2, 
        max: 12, 
        default: 6 
      },
      layout: {
        type: 'select',
        label: 'Layout',
        options: ['grid', 'carousel', 'list', 'masonry'],
        default: 'grid'
      },
      columns: {
        type: 'select',
        label: 'Columns (Grid)',
        options: [1, 2, 3, 4],
        default: 2
      },
      showPricing: { type: 'boolean', label: 'Show Pricing', default: true },
      showBadges: { type: 'boolean', label: 'Show Sale Badges', default: false }
    }
  },

  collections: {
    id: 'collections',
    name: 'Collections',
    description: 'Display product collections/categories',
    icon: '📂',
    category: 'products',
    settings: {
      title: { type: 'text', label: 'Section Title', required: true },
      layout: {
        type: 'select',
        label: 'Layout',
        options: ['grid', 'list', 'horizontal', 'carousel'],
        default: 'grid'
      },
      columns: {
        type: 'select',
        label: 'Columns (Grid)',
        options: [1, 2, 3, 4],
        default: 2
      },
      showOverlay: { type: 'boolean', label: 'Text Overlay', default: false }
    }
  },

  banner: {
    id: 'banner',
    name: 'Banner',
    description: 'Promotional banner with text and optional image',
    icon: '📢',
    category: 'content',
    settings: {
      title: { type: 'text', label: 'Title', required: true },
      subtitle: { type: 'text', label: 'Subtitle' },
      image: { type: 'image', label: 'Background Image' },
      backgroundColor: { type: 'color', label: 'Background Color', default: '#F0F0F0' },
      textColor: { type: 'color', label: 'Text Color', default: '#333333' },
      height: {
        type: 'select',
        label: 'Height',
        options: ['small', 'medium', 'large'],
        default: 'small'
      },
      animation: {
        type: 'select',
        label: 'Animation',
        options: ['none', 'fade', 'slide', 'pulse'],
        default: 'none'
      }
    }
  },

  text: {
    id: 'text',
    name: 'Text Block',
    description: 'Rich text content with formatting options',
    icon: '📝',
    category: 'content',
    settings: {
      title: { type: 'text', label: 'Title' },
      content: { type: 'textarea', label: 'Content', required: true },
      textAlign: {
        type: 'select',
        label: 'Text Alignment',
        options: ['left', 'center', 'right'],
        default: 'left'
      },
      padding: {
        type: 'select',
        label: 'Padding',
        options: ['small', 'medium', 'large'],
        default: 'medium'
      }
    }
  },

  image: {
    id: 'image',
    name: 'Image',
    description: 'Display an image with optional caption',
    icon: '🖼️',
    category: 'content',
    settings: {
      image: { type: 'image', label: 'Image', required: true },
      caption: { type: 'text', label: 'Caption' },
      aspectRatio: {
        type: 'select',
        label: 'Aspect Ratio',
        options: ['1:1', '4:3', '16:9', 'auto'],
        default: 'auto'
      },
      alignment: {
        type: 'select',
        label: 'Alignment',
        options: ['left', 'center', 'right'],
        default: 'center'
      }
    }
  },

  video: {
    id: 'video',
    name: 'Video',
    description: 'Embed videos from YouTube, Vimeo or upload directly',
    icon: '🎥',
    category: 'content',
    settings: {
      title: { type: 'text', label: 'Title' },
      videoUrl: { type: 'text', label: 'Video URL', required: true },
      aspectRatio: {
        type: 'select',
        label: 'Aspect Ratio',
        options: ['16:9', '4:3', '1:1'],
        default: '16:9'
      },
      autoPlay: { type: 'boolean', label: 'Auto Play', default: false },
      showControls: { type: 'boolean', label: 'Show Controls', default: true }
    }
  },

  testimonials: {
    id: 'testimonials',
    name: 'Testimonials',
    description: 'Customer reviews and testimonials',
    icon: '💬',
    category: 'social',
    settings: {
      title: { type: 'text', label: 'Section Title', required: true },
      layout: {
        type: 'select',
        label: 'Layout',
        options: ['carousel', 'grid', 'list'],
        default: 'carousel'
      },
      count: {
        type: 'number',
        label: 'Number to Show',
        min: 1,
        max: 10,
        default: 3
      },
      autoPlay: { type: 'boolean', label: 'Auto Play (Carousel)', default: false },
      showStars: { type: 'boolean', label: 'Show Star Ratings', default: true }
    }
  }
};

// Get all templates
router.get('/', (req, res) => {
  const templateList = Object.values(templates).map(template => ({
    id: template.id,
    name: template.name,
    description: template.description,
    preview: template.preview
  }));
  
  res.json(templateList);
});

// Get specific template
router.get('/:templateId', (req, res) => {
  const template = templates[req.params.templateId];
  
  if (!template) {
    return res.status(404).json({ error: 'Template not found' });
  }
  
  res.json(template);
});

// Get available block types
router.get('/blocks/types', (req, res) => {
  const blocksList = Object.values(blockTypes).map(block => ({
    id: block.id,
    name: block.name,
    description: block.description,
    icon: block.icon,
    category: block.category
  }));
  
  // Group by category
  const groupedBlocks = blocksList.reduce((acc, block) => {
    if (!acc[block.category]) {
      acc[block.category] = [];
    }
    acc[block.category].push(block);
    return acc;
  }, {});
  
  res.json(groupedBlocks);
});

// Get block type with settings schema
router.get('/blocks/types/:blockId', (req, res) => {
  const blockType = blockTypes[req.params.blockId];
  
  if (!blockType) {
    return res.status(404).json({ error: 'Block type not found' });
  }
  
  res.json(blockType);
});

// Validate block configuration
router.post('/blocks/validate', (req, res) => {
  const { blockType, settings } = req.body;
  
  const blockDef = blockTypes[blockType];
  if (!blockDef) {
    return res.status(400).json({ error: 'Invalid block type' });
  }
  
  const errors = [];
  
  // Validate required fields
  Object.entries(blockDef.settings).forEach(([key, config]) => {
    if (config.required && !settings[key]) {
      errors.push(`${config.label} is required`);
    }
    
    // Type validation
    if (settings[key]) {
      switch (config.type) {
        case 'number':
          if (isNaN(settings[key])) {
            errors.push(`${config.label} must be a number`);
          }
          if (config.min && settings[key] < config.min) {
            errors.push(`${config.label} must be at least ${config.min}`);
          }
          if (config.max && settings[key] > config.max) {
            errors.push(`${config.label} must be at most ${config.max}`);
          }
          break;
        case 'color':
          if (!/^#[0-9A-F]{6}$/i.test(settings[key])) {
            errors.push(`${config.label} must be a valid hex color`);
          }
          break;
      }
    }
  });
  
  if (errors.length > 0) {
    return res.status(400).json({ errors });
  }
  
  res.json({ valid: true });
});

module.exports = router;