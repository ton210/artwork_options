import axios from 'axios';

// Configure axios defaults
const api = axios.create({
  baseURL: '/api',
  timeout: 30000,
});

// Request interceptor to add auth headers if needed
api.interceptors.request.use((config) => {
  // Add any auth headers here if needed
  return config;
});

// Response interceptor for error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error('API Error:', error.response?.data || error.message);
    return Promise.reject(error);
  }
);

// App Configuration APIs
export const fetchAppConfig = async () => {
  const response = await api.get('/app/config');
  return response.data;
};

export const updateAppConfig = async (config) => {
  const formData = new FormData();
  
  // Separate files from config
  const { logo, splashScreen, favicon, ...configData } = config;
  
  formData.append('config', JSON.stringify(configData));
  
  if (logo instanceof File) {
    formData.append('logo', logo);
  }
  if (splashScreen instanceof File) {
    formData.append('splashScreen', splashScreen);
  }
  if (favicon instanceof File) {
    formData.append('favicon', favicon);
  }
  
  const response = await api.put('/app/config', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });
  return response.data;
};

// Store APIs
export const fetchStoreInfo = async () => {
  const response = await api.get('/app/store-info');
  return response.data;
};

export const generateStorefrontToken = async () => {
  const response = await api.post('/app/generate-storefront-token');
  return response.data;
};

// Template APIs
export const fetchTemplates = async () => {
  const response = await api.get('/templates');
  return response.data;
};

export const fetchTemplate = async (templateId) => {
  const response = await api.get(`/templates/${templateId}`);
  return response.data;
};

export const fetchBlockTypes = async () => {
  const response = await api.get('/templates/blocks/types');
  return response.data;
};

export const fetchBlockType = async (blockId) => {
  const response = await api.get(`/templates/blocks/types/${blockId}`);
  return response.data;
};

export const validateBlock = async (blockType, settings) => {
  const response = await api.post('/templates/blocks/validate', {
    blockType,
    settings
  });
  return response.data;
};

// Build APIs
export const buildAPK = async () => {
  const response = await api.post('/app/build-apk');
  return response.data;
};

export const fetchBuildStatus = async (buildId) => {
  if (!buildId) return null;
  const response = await api.get(`/app/build-status/${buildId}`);
  return response.data;
};

export const fetchBuilds = async () => {
  const response = await api.get('/builds');
  return response.data;
};

export const downloadAPK = async (buildId) => {
  const response = await api.get(`/app/download-apk/${buildId}`);
  return response.data;
};

// Helper functions
export const uploadFile = (file, onProgress) => {
  return new Promise((resolve, reject) => {
    const formData = new FormData();
    formData.append('file', file);
    
    api.post('/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (progressEvent) => {
        const percentCompleted = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        );
        onProgress?.(percentCompleted);
      }
    })
    .then(response => resolve(response.data))
    .catch(reject);
  });
};

export default api;