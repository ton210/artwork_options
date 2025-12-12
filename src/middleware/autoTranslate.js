const translator = require('../services/translator');
const cheerio = require('cheerio');

// Elements to skip translation
const SKIP_ELEMENTS = ['script', 'style', 'code', 'pre', 'svg', 'path'];

// Attributes that might contain text to translate
const TRANSLATE_ATTRIBUTES = ['title', 'alt', 'placeholder', 'aria-label'];

async function translateHTML(html, targetLang) {
  if (!html || targetLang === 'en') return html;

  const $ = cheerio.load(html);

  // Collect all text nodes and their elements
  const textsToTranslate = [];
  const elements = [];

  // Function to check if element should be skipped
  const shouldSkip = (elem) => {
    const tagName = elem.name;
    if (SKIP_ELEMENTS.includes(tagName)) return true;

    // Skip if parent is a skip element
    let parent = elem.parent;
    while (parent && parent.name) {
      if (SKIP_ELEMENTS.includes(parent.name)) return true;
      parent = parent.parent;
    }

    return false;
  };

  // Extract text nodes
  $('*').each((i, elem) => {
    if (shouldSkip(elem)) return;

    $(elem).contents().each((j, node) => {
      if (node.type === 'text') {
        const text = $(node).text().trim();
        if (text && text.length > 1 && !/^[\d\s\W]+$/.test(text)) {
          textsToTranslate.push(text);
          elements.push({ node, originalText: text });
        }
      }
    });

    // Translate attributes
    TRANSLATE_ATTRIBUTES.forEach(attr => {
      const attrValue = $(elem).attr(attr);
      if (attrValue && attrValue.length > 1) {
        textsToTranslate.push(attrValue);
        elements.push({ elem, attr, originalText: attrValue });
      }
    });
  });

  if (textsToTranslate.length === 0) return html;

  // Translate all texts in batch
  try {
    const translations = await Promise.all(
      textsToTranslate.map((text, i) =>
        translator.translate(`auto-${i}-${targetLang}`, text, targetLang, 'page')
      )
    );

    // Apply translations back
    translations.forEach((translated, i) => {
      const elem = elements[i];
      if (elem.node) {
        // Text node
        $(elem.node).replaceWith(translated);
      } else if (elem.attr) {
        // Attribute
        $(elem.elem).attr(elem.attr, translated);
      }
    });

    return $.html();
  } catch (error) {
    console.error('Error translating HTML:', error);
    return html; // Return original on error
  }
}

// Middleware to auto-translate rendered pages
function autoTranslateMiddleware(req, res, next) {
  const originalRender = res.render.bind(res);

  res.render = async function(view, options, callback) {
    // Detect language
    const lang = req.language || 'en';

    if (lang === 'en' || !translator.isSupported(lang)) {
      // No translation needed
      return originalRender(view, options, callback);
    }

    // Render to HTML first
    originalRender(view, options, async (err, html) => {
      if (err) {
        if (callback) return callback(err);
        return next(err);
      }

      try {
        // Translate the HTML
        const translatedHTML = await translateHTML(html, lang);

        if (callback) {
          callback(null, translatedHTML);
        } else {
          res.send(translatedHTML);
        }
      } catch (error) {
        console.error('Translation error:', error);
        // Send original HTML on error
        if (callback) {
          callback(null, html);
        } else {
          res.send(html);
        }
      }
    });
  };

  next();
}

module.exports = {
  autoTranslateMiddleware,
  translateHTML
};
