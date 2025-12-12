const axios = require('axios');
const db = require('../config/database');
const querystring = require('querystring');

const TRANSLATEX_API_KEY = process.env.TRANSLATEX_API_KEY || 'AIzaTXCwvZt7iiRcuNDDjwpctNXrQnvayJ5KeDB';
const TRANSLATEX_API_URL = 'https://api.translatex.com/translate';

const SUPPORTED_LANGUAGES = {
  'es': 'Spanish',
  'fr': 'French',
  'nl': 'Dutch',
  'de': 'German',
  'pt': 'Portuguese'
};

class Translator {
  constructor() {
    this.cache = new Map(); // In-memory cache for session
  }

  /**
   * Check if language is supported
   */
  isSupported(lang) {
    return SUPPORTED_LANGUAGES.hasOwnProperty(lang);
  }

  /**
   * Get cached translation from database
   */
  async getCachedTranslation(contentKey, targetLang) {
    try {
      const result = await db.query(
        'SELECT translated_text FROM translations WHERE content_key = $1 AND target_language = $2',
        [contentKey, targetLang]
      );

      if (result.rows.length > 0) {
        return result.rows[0].translated_text;
      }
      return null;
    } catch (error) {
      console.error('Error getting cached translation:', error);
      return null;
    }
  }

  /**
   * Save translation to database cache
   */
  async saveTranslation(contentKey, contentType, sourceLang, targetLang, sourceText, translatedText) {
    try {
      await db.query(
        `INSERT INTO translations (content_key, content_type, source_language, target_language, source_text, translated_text)
         VALUES ($1, $2, $3, $4, $5, $6)
         ON CONFLICT (content_key, target_language)
         DO UPDATE SET translated_text = EXCLUDED.translated_text, updated_at = NOW()`,
        [contentKey, contentType, sourceLang, targetLang, sourceText, translatedText]
      );
    } catch (error) {
      console.error('Error saving translation:', error);
    }
  }

  /**
   * Translate text using TranslateX API
   */
  async translateText(text, targetLang, sourceLang = 'en') {
    if (!text) return text;
    if (!this.isSupported(targetLang)) {
      throw new Error(`Unsupported language: ${targetLang}`);
    }

    try {
      const url = `${TRANSLATEX_API_URL}?sl=${sourceLang}&tl=${targetLang}&key=${TRANSLATEX_API_KEY}`;

      const response = await axios.post(
        url,
        querystring.stringify({ text }),
        {
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          timeout: 10000
        }
      );

      if (response.data && response.data.translation && response.data.translation.length > 0) {
        return response.data.translation[0];
      }

      throw new Error('Invalid API response');
    } catch (error) {
      console.error('Translation API error:', error.message);
      // Fallback to original text if translation fails
      return text;
    }
  }

  /**
   * Translate content with caching
   */
  async translate(contentKey, text, targetLang, contentType = 'page') {
    if (!text || targetLang === 'en') return text;

    // Check in-memory cache first
    const cacheKey = `${contentKey}:${targetLang}`;
    if (this.cache.has(cacheKey)) {
      return this.cache.get(cacheKey);
    }

    // Check database cache
    const cached = await this.getCachedTranslation(contentKey, targetLang);
    if (cached) {
      this.cache.set(cacheKey, cached);
      return cached;
    }

    // Translate using API
    const translated = await this.translateText(text, targetLang);

    // Save to cache
    await this.saveTranslation(contentKey, contentType, 'en', targetLang, text, translated);
    this.cache.set(cacheKey, translated);

    return translated;
  }

  /**
   * Batch translate multiple texts
   */
  async translateBatch(items, targetLang) {
    const promises = items.map(item =>
      this.translate(item.key, item.text, targetLang, item.type)
    );
    return Promise.all(promises);
  }

  /**
   * Get language name
   */
  getLanguageName(code) {
    return SUPPORTED_LANGUAGES[code] || code;
  }

  /**
   * Get all supported languages
   */
  getSupportedLanguages() {
    return SUPPORTED_LANGUAGES;
  }
}

module.exports = new Translator();
