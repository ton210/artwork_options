// Main PWA Application Logic

let ws = null;
let effectsEngine = null;
let reconnectInterval = null;

// Get WebSocket URL from current location
function getWebSocketUrl() {
    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    const host = window.location.host;
    return `${protocol}//${host}`;
}

// Track effect count for auto-redirect
let effectCounter = 0;
let MAX_EFFECTS_BEFORE_REDIRECT = 10; // Redirect after 10 effects
let redirectTimeout = null;

// Auto mode settings
let autoModeActive = false;
let autoModeInterval = null;

// Initialize the app
function init() {
    console.log('🎩 Magic Trick PWA Initializing...');

    effectsEngine = new EffectsEngine();

    // Request fullscreen immediately on user interaction
    document.addEventListener('click', enterFullscreen, { once: true });
    document.addEventListener('touchstart', enterFullscreen, { once: true });

    // Also try to enter fullscreen immediately
    setTimeout(enterFullscreen, 500);

    // Check URL parameters
    const urlParams = new URLSearchParams(window.location.search);

    // Auto mode parameter
    if (urlParams.get('auto') === '1' || urlParams.get('auto') === 'true') {
        console.log('🤖 Auto mode enabled via URL parameter');
        // Start auto mode after connection
        setTimeout(() => {
            startLocalAutoMode();
        }, 2000); // Wait 2 seconds for connection
    }

    // Max effects parameter
    if (urlParams.get('max')) {
        const maxEffects = parseInt(urlParams.get('max'));
        if (!isNaN(maxEffects) && maxEffects > 0) {
            MAX_EFFECTS_BEFORE_REDIRECT = maxEffects;
            console.log(`⚙️ Max effects set to ${maxEffects} via URL parameter`);
        }
    }

    // Connect to WebSocket server
    connectWebSocket();

    // Handle orientation and resize
    window.addEventListener('resize', handleResize);
    window.addEventListener('orientationchange', handleResize);

    // Prevent default touch behaviors
    document.addEventListener('touchmove', (e) => {
        e.preventDefault();
    }, { passive: false });

    // Register service worker for PWA
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').then(() => {
            console.log('Service Worker registered');
        }).catch(err => {
            console.log('Service Worker registration failed:', err);
        });
    }
}

// Connect to WebSocket server
function connectWebSocket() {
    const wsUrl = getWebSocketUrl();
    console.log('🔌 Connecting to:', wsUrl);

    updateConnectionStatus('Connecting...');

    try {
        ws = new WebSocket(wsUrl);

        ws.onopen = () => {
            console.log('✅ WebSocket connected');
            updateConnectionStatus('Connected ✓');

            // Get device info
            const deviceInfo = {
                userAgent: navigator.userAgent,
                platform: navigator.platform,
                screenWidth: window.screen.width,
                screenHeight: window.screen.height,
                language: navigator.language
            };

            // Register as spectator with device info
            ws.send(JSON.stringify({
                type: 'register',
                role: 'spectator',
                deviceInfo: deviceInfo
            }));

            // Clear reconnect interval
            if (reconnectInterval) {
                clearInterval(reconnectInterval);
                reconnectInterval = null;
            }
        };

        ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                console.log('📨 Received:', data);

                handleMessage(data);
            } catch (e) {
                console.error('Error parsing message:', e);
            }
        };

        ws.onerror = (error) => {
            console.error('❌ WebSocket error:', error);
            updateConnectionStatus('Error ✗');
        };

        ws.onclose = () => {
            console.log('🔌 WebSocket closed');
            updateConnectionStatus('Disconnected ✗');

            // Try to reconnect
            if (!reconnectInterval) {
                reconnectInterval = setInterval(() => {
                    console.log('🔄 Attempting to reconnect...');
                    connectWebSocket();
                }, 3000);
            }
        };

    } catch (e) {
        console.error('Failed to create WebSocket:', e);
        updateConnectionStatus('Failed ✗');
    }
}

// Handle incoming WebSocket messages
function handleMessage(data) {
    switch (data.type) {
        case 'connected':
            console.log('👋 Welcome message:', data.message);
            break;

        case 'registered':
            console.log('✅ Registered as:', data.role);
            break;

        case 'effect':
            console.log('✨ Triggering effect:', data.effect);
            triggerEffect(data.effect);
            break;

        case 'config':
            console.log('⚙️ Config update:', data.config);
            if (data.config.maxEffectsBeforeRedirect !== undefined) {
                MAX_EFFECTS_BEFORE_REDIRECT = data.config.maxEffectsBeforeRedirect;
                console.log(`Updated max effects to: ${MAX_EFFECTS_BEFORE_REDIRECT}`);
            }
            break;

        default:
            console.log('Unknown message type:', data.type);
    }
}

// Trigger visual effect
function triggerEffect(effectName) {
    if (!effectsEngine) return;

    console.log('🎬 Executing effect:', effectName);

    // Track effects (except reset)
    if (effectName !== 'reset') {
        effectCounter++;
        console.log(`Effect count: ${effectCounter}/${MAX_EFFECTS_BEFORE_REDIRECT}`);

        // Schedule redirect if we've hit the limit
        if (effectCounter >= MAX_EFFECTS_BEFORE_REDIRECT) {
            scheduleRedirect();
        }
    } else {
        // Reset counter if user manually resets
        effectCounter = 0;
        cancelRedirect();
    }

    switch (effectName) {
        case 'glitch':
            effectsEngine.glitch();
            break;
        case 'crash':
            effectsEngine.crash();
            break;
        case 'flicker':
            effectsEngine.flicker();
            break;
        case 'shake':
            effectsEngine.shake();
            break;
        case 'static':
            effectsEngine.static();
            break;
        case 'matrix':
            effectsEngine.matrix();
            break;
        case 'gallery':
            effectsEngine.gallery();
            break;
        case 'apps':
            effectsEngine.apps();
            break;
        case 'camera':
            effectsEngine.camera();
            break;
        case 'location':
            effectsEngine.location();
            break;
        case 'notifications':
            effectsEngine.notifications();
            break;
        case 'systeminfo':
            effectsEngine.systeminfo();
            break;
        case 'clipboard':
            effectsEngine.clipboard();
            break;
        case 'reset':
            effectsEngine.reset();
            break;
        default:
            console.log('Unknown effect:', effectName);
    }
}

// Schedule redirect to Google after effects
function scheduleRedirect() {
    if (redirectTimeout) return; // Already scheduled

    console.log('🔄 Scheduling redirect to Google in 5 seconds...');

    redirectTimeout = setTimeout(() => {
        console.log('👋 Redirecting to Google...');

        // Exit fullscreen first
        if (document.exitFullscreen) {
            document.exitFullscreen().catch(err => console.log('Exit fullscreen failed:', err));
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }

        // Redirect to Google
        setTimeout(() => {
            window.location.href = 'https://www.google.com';
        }, 500);
    }, 5000); // 5 second delay after last effect
}

// Cancel scheduled redirect
function cancelRedirect() {
    if (redirectTimeout) {
        clearTimeout(redirectTimeout);
        redirectTimeout = null;
        console.log('❌ Redirect cancelled');
    }
}

// Update connection status display
function updateConnectionStatus(status) {
    const statusElement = document.getElementById('connectionStatus');
    if (statusElement) {
        statusElement.textContent = status;
    }
}

// Enter fullscreen mode (like a video player)
function enterFullscreen() {
    const elem = document.documentElement;

    // Try multiple fullscreen methods
    const requestFullscreen =
        elem.requestFullscreen ||
        elem.webkitRequestFullscreen ||
        elem.webkitEnterFullscreen ||
        elem.mozRequestFullScreen ||
        elem.msRequestFullscreen;

    if (requestFullscreen) {
        requestFullscreen.call(elem).then(() => {
            console.log('✅ Entered fullscreen');

            // Lock orientation to portrait if available
            if (screen.orientation && screen.orientation.lock) {
                screen.orientation.lock('portrait').catch(err => {
                    console.log('Orientation lock failed:', err);
                });
            }
        }).catch(err => {
            console.log('❌ Fullscreen request failed:', err);

            // For iOS Safari, try alternative approach
            if (elem.webkitEnterFullscreen) {
                elem.webkitEnterFullscreen();
            }
        });
    } else {
        console.log('⚠️ Fullscreen API not supported');
    }

    // For iOS: try to maximize viewport
    if (window.navigator.standalone === false) {
        // Prompt user to add to home screen for better fullscreen on iOS
        console.log('💡 Tip: Add to home screen for fullscreen on iOS');
    }

    // Hide address bar on mobile by scrolling
    setTimeout(() => {
        window.scrollTo(0, 1);
    }, 100);
}

// Handle window resize/orientation change
function handleResize() {
    // Resize canvases
    const canvases = ['glitchCanvas', 'staticCanvas', 'matrixCanvas'];
    canvases.forEach(id => {
        const canvas = document.getElementById(id);
        if (canvas) {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

// Handle visibility change (when user switches apps)
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        console.log('📱 App hidden');
    } else {
        console.log('📱 App visible');
        // Try to reconnect if needed
        if (!ws || ws.readyState !== WebSocket.OPEN) {
            connectWebSocket();
        }
    }
});

// Start local auto mode (independent of admin)
function startLocalAutoMode() {
    if (autoModeActive) return;

    autoModeActive = true;
    console.log('🤖 Starting local auto mode');

    const effects = [
        'glitch', 'flicker', 'shake', 'static', 'matrix', 'crash',
        'gallery', 'apps', 'camera', 'location', 'notifications',
        'systeminfo', 'clipboard'
    ];

    function triggerRandomEffect() {
        if (!autoModeActive) return;

        const randomEffect = effects[Math.floor(Math.random() * effects.length)];
        console.log(`🎲 Auto mode triggering: ${randomEffect}`);
        triggerEffect(randomEffect);

        // Random delay between 2-5 seconds
        const randomDelay = Math.random() * 3000 + 2000;
        autoModeInterval = setTimeout(triggerRandomEffect, randomDelay);
    }

    // Start first effect
    triggerRandomEffect();
}

// Stop local auto mode
function stopLocalAutoMode() {
    autoModeActive = false;
    if (autoModeInterval) {
        clearTimeout(autoModeInterval);
        autoModeInterval = null;
    }
    console.log('⏹️ Stopped local auto mode');
}

// Prevent accidental exit
window.addEventListener('beforeunload', (e) => {
    // Don't show prompt, just close WebSocket cleanly
    if (ws) {
        ws.close();
    }
    stopLocalAutoMode();
});
