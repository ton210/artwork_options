/**
 * Library Loader Module
 * Handles loading of external dependencies (Fabric.js, Cropper.js)
 */

export class LibraryLoader {
  constructor() {
    this.loadedLibraries = new Set();
    this.maxRetries = 3;
    this.retryDelay = 1000;
  }

  async loadScript(src, globalName, retries = 0) {
    // Check if already loaded
    if (globalName && window[globalName]) {
      this.loadedLibraries.add(globalName);
      return Promise.resolve();
    }

    return new Promise((resolve, reject) => {
      const existingScript = document.querySelector(`script[src="${src}"]`);
      if (existingScript) {
        // Script tag exists, wait for it to load
        if (globalName && window[globalName]) {
          resolve();
        } else {
          existingScript.addEventListener('load', resolve);
          existingScript.addEventListener('error', () => {
            if (retries < this.maxRetries) {
              setTimeout(() => {
                this.loadScript(src, globalName, retries + 1).then(resolve).catch(reject);
              }, this.retryDelay);
            } else {
              reject(new Error(`Failed to load ${src} after ${this.maxRetries} attempts`));
            }
          });
        }
        return;
      }

      const script = document.createElement('script');
      script.src = src;
      script.async = true;

      script.onload = () => {
        if (globalName && window[globalName]) {
          this.loadedLibraries.add(globalName);
          resolve();
        } else if (!globalName) {
          resolve();
        } else {
          reject(new Error(`${globalName} not found after loading ${src}`));
        }
      };

      script.onerror = () => {
        if (retries < this.maxRetries) {
          setTimeout(() => {
            this.loadScript(src, globalName, retries + 1).then(resolve).catch(reject);
          }, this.retryDelay);
        } else {
          reject(new Error(`Failed to load ${src} after ${this.maxRetries} attempts`));
        }
      };

      document.head.appendChild(script);
    });
  }

  async loadStylesheet(href) {
    return new Promise((resolve, reject) => {
      const existingLink = document.querySelector(`link[href="${href}"]`);
      if (existingLink) {
        resolve();
        return;
      }

      const link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = href;

      link.onload = () => resolve();
      link.onerror = () => reject(new Error(`Failed to load stylesheet: ${href}`));

      document.head.appendChild(link);
    });
  }

  async loadAllDependencies() {
    try {
      // Load Fabric.js
      if (!window.fabric) {
        await this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js', 'fabric');
      }

      // Load Cropper.js
      if (!window.Cropper) {
        await this.loadStylesheet('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css');
        await this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js', 'Cropper');
      }

      return true;
    } catch (error) {
      console.error('Failed to load dependencies:', error);
      throw error;
    }
  }
}