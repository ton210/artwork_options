/**
 * SWPD Memory Manager - Prevent crashes and optimize performance
 * Handles memory leaks, lazy loading, and resource cleanup
 */

class SWPDMemoryManager {
    constructor() {
        this.observers = new Set();
        this.imageCache = new Map();
        this.resourceRefs = new WeakSet();
        this.activeObjects = new Set();
        this.memoryThreshold = 50 * 1024 * 1024; // 50MB threshold
        this.maxCacheSize = 20;
        this.cleanupTimer = null;
        this.performanceMonitor = null;
        
        this.init();
    }

    init() {
        this.startPerformanceMonitoring();
        this.setupAutoCleanup();
        this.setupUnloadHandlers();
        this.createImageObserver();
        this.monitorMemoryUsage();
        
        console.log('Memory Manager initialized');
    }

    /**
     * Performance monitoring with Web Vitals
     */
    startPerformanceMonitoring() {
        this.performanceMonitor = {
            startTime: performance.now(),
            measurements: new Map(),
            
            mark: (name) => {
                this.performanceMonitor.measurements.set(name, performance.now());
            },
            
            measure: (name, startMark) => {
                const start = this.performanceMonitor.measurements.get(startMark) || this.performanceMonitor.startTime;
                const duration = performance.now() - start;
                this.performanceMonitor.measurements.set(name + '_duration', duration);
                return duration;
            },
            
            getReport: () => {
                const report = {};
                this.performanceMonitor.measurements.forEach((value, key) => {
                    report[key] = value;
                });
                return report;
            }
        };

        // Monitor memory usage every 10 seconds
        setInterval(() => {
            this.checkMemoryUsage();
        }, 10000);
    }

    /**
     * Memory usage monitoring
     */
    checkMemoryUsage() {
        if (!window.performance || !window.performance.memory) {
            return;
        }

        const memInfo = window.performance.memory;
        const usedMemory = memInfo.usedJSHeapSize;
        const totalMemory = memInfo.totalJSHeapSize;
        const memoryLimit = memInfo.jsHeapSizeLimit;

        // Log memory stats
        this.performanceMonitor.mark('memory_check');
        this.performanceMonitor.measurements.set('memory_used', usedMemory);
        this.performanceMonitor.measurements.set('memory_total', totalMemory);
        this.performanceMonitor.measurements.set('memory_limit', memoryLimit);

        // Warning threshold (80% of limit)
        if (usedMemory > memoryLimit * 0.8) {
            console.warn('High memory usage detected:', {
                used: this.formatBytes(usedMemory),
                limit: this.formatBytes(memoryLimit),
                percentage: Math.round((usedMemory / memoryLimit) * 100)
            });
            
            this.triggerEmergencyCleanup();
        }

        // Critical threshold (90% of limit)
        if (usedMemory > memoryLimit * 0.9) {
            this.triggerCriticalCleanup();
        }
    }

    /**
     * Lazy image loading with intersection observer
     */
    createImageObserver() {
        if (!window.IntersectionObserver) {
            return;
        }

        this.imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    this.loadImage(img);
                    this.imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.1
        });
    }

    /**
     * Smart image loading with memory management
     */
    async loadImage(img) {
        const src = img.dataset.src || img.src;
        if (!src) return;

        // Check cache first
        if (this.imageCache.has(src)) {
            const cached = this.imageCache.get(src);
            if (cached.blob) {
                img.src = URL.createObjectURL(cached.blob);
                cached.lastUsed = Date.now();
                return;
            }
        }

        try {
            this.performanceMonitor.mark(`image_load_start_${src}`);
            
            const response = await fetch(src);
            const blob = await response.blob();
            
            // Create object URL
            const objectUrl = URL.createObjectURL(blob);
            img.src = objectUrl;

            // Cache management
            this.addToImageCache(src, {
                blob: blob,
                objectUrl: objectUrl,
                size: blob.size,
                lastUsed: Date.now()
            });

            this.performanceMonitor.mark(`image_load_end_${src}`);
            this.performanceMonitor.measure(`image_load_duration_${src}`, `image_load_start_${src}`);

            // Track for cleanup
            this.resourceRefs.add(img);

        } catch (error) {
            console.error('Failed to load image:', src, error);
            img.src = 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100" fill="%23f0f0f0"/></svg>';
        }
    }

    /**
     * Add image to cache with size management
     */
    addToImageCache(src, data) {
        // Check cache size limit
        if (this.imageCache.size >= this.maxCacheSize) {
            this.cleanImageCache();
        }

        this.imageCache.set(src, data);
    }

    /**
     * Clean image cache using LRU strategy
     */
    cleanImageCache() {
        // Sort by last used time
        const sortedEntries = Array.from(this.imageCache.entries())
            .sort((a, b) => a[1].lastUsed - b[1].lastUsed);

        // Remove oldest 30%
        const removeCount = Math.ceil(sortedEntries.length * 0.3);
        
        for (let i = 0; i < removeCount; i++) {
            const [src, data] = sortedEntries[i];
            
            // Revoke object URL to free memory
            if (data.objectUrl) {
                URL.revokeObjectURL(data.objectUrl);
            }
            
            this.imageCache.delete(src);
        }

        console.log(`Cleaned ${removeCount} images from cache`);
    }

    /**
     * Register object for memory tracking
     */
    registerObject(obj, type = 'unknown') {
        if (!obj) return;

        const objectData = {
            object: obj,
            type: type,
            timestamp: Date.now(),
            size: this.estimateObjectSize(obj)
        };

        this.activeObjects.add(objectData);
        
        // Automatic cleanup for temporary objects
        if (type === 'temp') {
            setTimeout(() => {
                this.unregisterObject(obj);
            }, 60000); // Clean up temp objects after 1 minute
        }
    }

    /**
     * Unregister object and clean up
     */
    unregisterObject(obj) {
        for (const objectData of this.activeObjects) {
            if (objectData.object === obj) {
                this.cleanupObject(objectData);
                this.activeObjects.delete(objectData);
                break;
            }
        }
    }

    /**
     * Estimate object memory size
     */
    estimateObjectSize(obj) {
        if (!obj) return 0;

        try {
            if (obj instanceof HTMLImageElement) {
                return (obj.naturalWidth || 0) * (obj.naturalHeight || 0) * 4; // RGBA
            }
            
            if (obj instanceof HTMLCanvasElement) {
                return obj.width * obj.height * 4; // RGBA
            }
            
            if (typeof obj === 'string') {
                return obj.length * 2; // UTF-16
            }
            
            if (obj instanceof ArrayBuffer) {
                return obj.byteLength;
            }
            
            // Rough estimate for objects
            return JSON.stringify(obj).length * 2;
            
        } catch (e) {
            return 1024; // Default estimate
        }
    }

    /**
     * Clean up a specific object
     */
    cleanupObject(objectData) {
        const obj = objectData.object;
        
        try {
            if (obj instanceof HTMLImageElement) {
                if (obj.src.startsWith('blob:')) {
                    URL.revokeObjectURL(obj.src);
                }
                obj.src = '';
                obj.onload = null;
                obj.onerror = null;
            }
            
            if (obj instanceof HTMLCanvasElement) {
                const ctx = obj.getContext('2d');
                if (ctx) {
                    ctx.clearRect(0, 0, obj.width, obj.height);
                }
                obj.width = 0;
                obj.height = 0;
            }
            
            if (obj && typeof obj.destroy === 'function') {
                obj.destroy();
            }
            
            if (obj && typeof obj.dispose === 'function') {
                obj.dispose();
            }
            
        } catch (error) {
            console.warn('Error cleaning up object:', error);
        }
    }

    /**
     * Setup automatic cleanup
     */
    setupAutoCleanup() {
        // Clean up every 2 minutes
        this.cleanupTimer = setInterval(() => {
            this.performRoutineCleanup();
        }, 120000);

        // Clean up on visibility change
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.performHiddenCleanup();
            }
        });
    }

    /**
     * Routine cleanup for old objects
     */
    performRoutineCleanup() {
        const now = Date.now();
        const maxAge = 5 * 60 * 1000; // 5 minutes
        
        let cleanedCount = 0;
        
        for (const objectData of this.activeObjects) {
            if (now - objectData.timestamp > maxAge) {
                this.cleanupObject(objectData);
                this.activeObjects.delete(objectData);
                cleanedCount++;
            }
        }
        
        // Clean image cache
        this.cleanImageCache();
        
        // Force garbage collection if available
        if (window.gc && typeof window.gc === 'function') {
            window.gc();
        }
        
        console.log(`Routine cleanup completed: ${cleanedCount} objects cleaned`);
    }

    /**
     * Cleanup when page is hidden
     */
    performHiddenCleanup() {
        // More aggressive cleanup when page is hidden
        const now = Date.now();
        const maxAge = 2 * 60 * 1000; // 2 minutes for hidden cleanup
        
        for (const objectData of this.activeObjects) {
            if (now - objectData.timestamp > maxAge) {
                this.cleanupObject(objectData);
                this.activeObjects.delete(objectData);
            }
        }
        
        // Clear most of image cache
        const cacheEntries = Array.from(this.imageCache.entries());
        cacheEntries.forEach(([src, data]) => {
            if (data.objectUrl) {
                URL.revokeObjectURL(data.objectUrl);
            }
        });
        this.imageCache.clear();
        
        console.log('Hidden cleanup completed');
    }

    /**
     * Emergency cleanup for high memory usage
     */
    triggerEmergencyCleanup() {
        console.warn('Triggering emergency cleanup due to high memory usage');
        
        // Clean all temporary objects immediately
        for (const objectData of this.activeObjects) {
            if (objectData.type === 'temp') {
                this.cleanupObject(objectData);
                this.activeObjects.delete(objectData);
            }
        }
        
        // Clear image cache
        this.imageCache.clear();
        
        // Trigger garbage collection
        if (window.gc) {
            window.gc();
        }
        
        // Notify user
        this.notifyMemoryWarning();
    }

    /**
     * Critical cleanup - last resort
     */
    triggerCriticalCleanup() {
        console.error('Critical memory usage - performing aggressive cleanup');
        
        // Clean ALL tracked objects
        for (const objectData of this.activeObjects) {
            this.cleanupObject(objectData);
        }
        this.activeObjects.clear();
        
        // Clear all caches
        this.imageCache.clear();
        
        // Clear browser caches if possible
        if ('caches' in window) {
            caches.keys().then(names => {
                names.forEach(name => {
                    if (name.includes('swpd')) {
                        caches.delete(name);
                    }
                });
            });
        }
        
        // Force multiple GC cycles
        if (window.gc) {
            for (let i = 0; i < 3; i++) {
                setTimeout(() => window.gc(), i * 100);
            }
        }
        
        this.notifyCriticalMemoryWarning();
    }

    /**
     * Setup unload handlers
     */
    setupUnloadHandlers() {
        const cleanup = () => {
            this.cleanup();
        };
        
        window.addEventListener('beforeunload', cleanup);
        window.addEventListener('unload', cleanup);
        
        // Cleanup on page hide (for mobile browsers)
        document.addEventListener('pagehide', cleanup);
    }

    /**
     * Monitor memory usage continuously
     */
    monitorMemoryUsage() {
        if (!window.performance || !window.performance.memory) {
            console.warn('Performance memory API not available');
            return;
        }
        
        const checkInterval = 15000; // Check every 15 seconds
        
        setInterval(() => {
            const memory = window.performance.memory;
            const usedPercent = (memory.usedJSHeapSize / memory.jsHeapSizeLimit) * 100;
            
            // Log memory usage periodically
            if (Math.random() < 0.1) { // 10% chance to log
                console.log(`Memory usage: ${this.formatBytes(memory.usedJSHeapSize)} / ${this.formatBytes(memory.jsHeapSizeLimit)} (${Math.round(usedPercent)}%)`);
            }
            
            // Progressive cleanup based on memory usage
            if (usedPercent > 70) {
                this.performPartialCleanup();
            }
            
        }, checkInterval);
    }

    /**
     * Partial cleanup for moderate memory usage
     */
    performPartialCleanup() {
        // Clean oldest 50% of cache
        const cacheEntries = Array.from(this.imageCache.entries())
            .sort((a, b) => a[1].lastUsed - b[1].lastUsed);
        
        const removeCount = Math.ceil(cacheEntries.length * 0.5);
        
        for (let i = 0; i < removeCount; i++) {
            const [src, data] = cacheEntries[i];
            if (data.objectUrl) {
                URL.revokeObjectURL(data.objectUrl);
            }
            this.imageCache.delete(src);
        }
    }

    /**
     * Create lazy loading observer for elements
     */
    observeElement(element) {
        if (this.imageObserver && element) {
            this.imageObserver.observe(element);
        }
    }

    /**
     * Unobserve element
     */
    unobserveElement(element) {
        if (this.imageObserver && element) {
            this.imageObserver.unobserve(element);
        }
    }

    /**
     * Notify user about memory warnings
     */
    notifyMemoryWarning() {
        if (window.swpdDesigner && typeof window.swpdDesigner.showNotification === 'function') {
            window.swpdDesigner.showNotification(
                'High memory usage detected. Some features may be limited.', 
                'warning'
            );
        }
    }

    notifyCriticalMemoryWarning() {
        if (window.swpdDesigner && typeof window.swpdDesigner.showNotification === 'function') {
            window.swpdDesigner.showNotification(
                'Critical memory usage! Please save your work and refresh the page.', 
                'error'
            );
        }
    }

    /**
     * Format bytes for human readable output
     */
    formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Get memory statistics
     */
    getMemoryStats() {
        const stats = {
            activeObjects: this.activeObjects.size,
            cachedImages: this.imageCache.size,
            estimatedCacheSize: 0
        };
        
        // Calculate cache size
        for (const [src, data] of this.imageCache) {
            stats.estimatedCacheSize += data.size || 0;
        }
        
        if (window.performance && window.performance.memory) {
            const memory = window.performance.memory;
            stats.heapUsed = memory.usedJSHeapSize;
            stats.heapTotal = memory.totalJSHeapSize;
            stats.heapLimit = memory.jsHeapSizeLimit;
            stats.heapUsedPercent = (memory.usedJSHeapSize / memory.jsHeapSizeLimit) * 100;
        }
        
        return stats;
    }

    /**
     * Complete cleanup
     */
    cleanup() {
        // Clear all timers
        if (this.cleanupTimer) {
            clearInterval(this.cleanupTimer);
        }
        
        // Clean all objects
        for (const objectData of this.activeObjects) {
            this.cleanupObject(objectData);
        }
        this.activeObjects.clear();
        
        // Clean image cache
        for (const [src, data] of this.imageCache) {
            if (data.objectUrl) {
                URL.revokeObjectURL(data.objectUrl);
            }
        }
        this.imageCache.clear();
        
        // Disconnect observers
        if (this.imageObserver) {
            this.imageObserver.disconnect();
        }
        
        console.log('Memory manager cleanup completed');
    }

    /**
     * Public API
     */
    getPerformanceReport() {
        return {
            performance: this.performanceMonitor.getReport(),
            memory: this.getMemoryStats()
        };
    }
}

// Create global memory manager
window.swpdMemoryManager = new SWPDMemoryManager();

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SWPDMemoryManager;
}