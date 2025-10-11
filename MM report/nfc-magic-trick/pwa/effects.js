// Visual Effects Library for Magic Trick PWA

class EffectsEngine {
    constructor() {
        this.currentEffect = null;
        this.animationFrames = {};
    }

    // Stop all running effects
    stopAll() {
        Object.values(this.animationFrames).forEach(id => {
            if (id) cancelAnimationFrame(id);
        });
        this.animationFrames = {};

        // Hide all screens
        document.querySelectorAll('.screen').forEach(screen => {
            screen.classList.remove('active');
        });
    }

    // Show specific screen
    showScreen(screenId) {
        this.stopAll();
        const screen = document.getElementById(screenId);
        if (screen) {
            screen.classList.add('active');
        }
    }

    // Effect: Glitch (RGB split, scan lines, distortion)
    glitch() {
        this.showScreen('glitchScreen');

        const canvas = document.getElementById('glitchCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        let frame = 0;

        const draw = () => {
            ctx.fillStyle = '#000';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Random RGB shift rectangles
            for (let i = 0; i < 20; i++) {
                const x = Math.random() * canvas.width;
                const y = Math.random() * canvas.height;
                const w = Math.random() * 300 + 50;
                const h = Math.random() * 50 + 10;

                // RGB channels
                ctx.fillStyle = `rgba(255, 0, 0, ${Math.random() * 0.5})`;
                ctx.fillRect(x + Math.random() * 10, y, w, h);

                ctx.fillStyle = `rgba(0, 255, 0, ${Math.random() * 0.5})`;
                ctx.fillRect(x - Math.random() * 10, y, w, h);

                ctx.fillStyle = `rgba(0, 0, 255, ${Math.random() * 0.5})`;
                ctx.fillRect(x, y + Math.random() * 5, w, h);
            }

            // Scan lines
            ctx.fillStyle = 'rgba(255, 255, 255, 0.05)';
            for (let y = 0; y < canvas.height; y += 4) {
                ctx.fillRect(0, y + (frame % 4), canvas.width, 2);
            }

            // Random white noise blocks
            for (let i = 0; i < 50; i++) {
                const x = Math.random() * canvas.width;
                const y = Math.random() * canvas.height;
                const size = Math.random() * 10;
                ctx.fillStyle = `rgba(255, 255, 255, ${Math.random()})`;
                ctx.fillRect(x, y, size, size);
            }

            frame++;
            this.animationFrames.glitch = requestAnimationFrame(draw);
        };

        draw();

        // Play glitch sound
        this.playBeep(200, 0.1);
    }

    // Effect: Static/TV Noise
    static() {
        this.showScreen('staticScreen');

        const canvas = document.getElementById('staticCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const draw = () => {
            const imageData = ctx.createImageData(canvas.width, canvas.height);
            const buffer = new Uint32Array(imageData.data.buffer);

            for (let i = 0; i < buffer.length; i++) {
                const color = Math.random() > 0.5 ? 0xFFFFFFFF : 0xFF000000;
                buffer[i] = color;
            }

            ctx.putImageData(imageData, 0, 0);
            this.animationFrames.static = requestAnimationFrame(draw);
        };

        draw();
        this.playWhiteNoise();
    }

    // Effect: Matrix rain
    matrix() {
        this.showScreen('matrixScreen');

        const canvas = document.getElementById('matrixCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const fontSize = 16;
        const columns = Math.floor(canvas.width / fontSize);
        const drops = Array(columns).fill(1);

        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%^&*()';

        const draw = () => {
            // Black background with fade
            ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = '#0F0'; // Green text
            ctx.font = `${fontSize}px monospace`;

            for (let i = 0; i < drops.length; i++) {
                const text = chars[Math.floor(Math.random() * chars.length)];
                const x = i * fontSize;
                const y = drops[i] * fontSize;

                ctx.fillText(text, x, y);

                if (y > canvas.height && Math.random() > 0.975) {
                    drops[i] = 0;
                }

                drops[i]++;
            }

            this.animationFrames.matrix = requestAnimationFrame(draw);
        };

        draw();
    }

    // Effect: Crash screen
    crash() {
        this.showScreen('crashScreen');
        this.playBeep(400, 0.2);

        // Make buttons non-functional (they do nothing when clicked)
        const buttons = document.querySelectorAll('.crash-button');
        buttons.forEach(btn => {
            btn.onclick = () => {
                // Button click does nothing - phone appears frozen
                this.playBeep(300, 0.1);
            };
        });
    }

    // Effect: Screen flicker
    flicker() {
        this.showScreen('flickerScreen');

        let count = 0;
        const maxFlickers = 20;

        const interval = setInterval(() => {
            const screen = document.getElementById('flickerScreen');
            screen.style.background = count % 2 === 0 ? '#fff' : '#000';
            count++;

            if (count >= maxFlickers) {
                clearInterval(interval);
            }
        }, 100);
    }

    // Effect: Shake (handled by CSS, just show the screen)
    shake() {
        this.showScreen('shakeScreen');

        // Vibrate if available
        if ('vibrate' in navigator) {
            navigator.vibrate([100, 50, 100, 50, 100]);
        }
    }

    // Effect: Photo Gallery (simulated scrolling through photos)
    gallery() {
        this.showScreen('galleryScreen');

        const grid = document.getElementById('galleryGrid');
        grid.innerHTML = '';

        // Generate fake photo grid
        for (let i = 0; i < 60; i++) {
            const item = document.createElement('div');
            item.className = 'gallery-item';

            // Random colors to simulate photos
            const hue = Math.random() * 360;
            const saturation = 40 + Math.random() * 40;
            const lightness = 30 + Math.random() * 40;
            item.style.background = `hsl(${hue}, ${saturation}%, ${lightness}%)`;

            // Random delay for flicker
            item.style.animationDelay = `${Math.random() * 0.5}s`;

            grid.appendChild(item);
        }

        // Auto-scroll effect
        let scrollPos = 0;
        const scrollInterval = setInterval(() => {
            scrollPos += 5;
            grid.scrollTop = scrollPos;

            if (scrollPos > grid.scrollHeight - grid.clientHeight) {
                scrollPos = 0;
            }
        }, 50);

        this.animationFrames.gallery = scrollInterval;
    }

    // Effect: Random apps opening
    apps() {
        this.showScreen('appsScreen');

        const apps = [
            { icon: '📧', name: 'Gmail', color: '#DB4437' },
            { icon: '📷', name: 'Camera', color: '#34A853' },
            { icon: '🎵', name: 'Spotify', color: '#1DB954' },
            { icon: '📱', name: 'Messages', color: '#0084FF' },
            { icon: '🌐', name: 'Chrome', color: '#4285F4' },
            { icon: '📞', name: 'Phone', color: '#00C853' },
            { icon: '⚙️', name: 'Settings', color: '#757575' },
            { icon: '📝', name: 'Notes', color: '#FFA000' },
            { icon: '🎮', name: 'Games', color: '#E91E63' },
            { icon: '💬', name: 'WhatsApp', color: '#25D366' }
        ];

        let currentIndex = 0;

        const showApp = () => {
            if (currentIndex >= apps.length) {
                currentIndex = 0;
            }

            const app = apps[currentIndex];
            const appWindow = document.getElementById('appWindow');

            appWindow.style.background = 'white';
            appWindow.innerHTML = `
                <div class="app-content">
                    <div class="app-icon">${app.icon}</div>
                    <div class="app-name" style="color: ${app.color}">${app.name}</div>
                    <div class="app-loading">
                        <div class="app-loading-bar"></div>
                    </div>
                </div>
            `;

            // Trigger reflow for animation
            appWindow.style.animation = 'none';
            setTimeout(() => {
                appWindow.style.animation = 'appBounce 0.3s ease';
            }, 10);

            currentIndex++;

            // Play app open sound
            this.playBeep(400 + Math.random() * 200, 0.1);
        };

        showApp();

        // Change apps every 1-2 seconds
        const appInterval = setInterval(() => {
            showApp();
        }, 1000 + Math.random() * 1000);

        this.animationFrames.apps = appInterval;
    }

    // Effect: Camera/Photos (request camera permission, take photo)
    async camera() {
        this.showScreen('cameraScreen');

        try {
            // Request camera permission
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' }, // Front camera
                audio: false
            });

            const video = document.getElementById('cameraPreview');
            video.srcObject = stream;

            // Auto-capture after 2 seconds
            setTimeout(async () => {
                const canvas = document.getElementById('cameraCanvas');
                const context = canvas.getContext('2d');

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0);

                // Stop camera
                stream.getTracks().forEach(track => track.stop());

                // Show captured photo in gallery
                setTimeout(() => {
                    this.gallery();
                }, 1000);
            }, 2000);

        } catch (err) {
            console.error('Camera access denied:', err);
            // Fallback to simulated camera
            this.showScreen('cameraScreen');
            setTimeout(() => this.gallery(), 2000);
        }
    }

    // Effect: Location/Maps (request geolocation)
    async location() {
        this.showScreen('locationScreen');

        try {
            const position = await new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                });
            });

            const { latitude, longitude } = position.coords;

            // Display location info
            const locationInfo = document.getElementById('locationInfo');
            locationInfo.innerHTML = `
                <div style="color: #333; font-size: 14px;">
                    <div style="margin-bottom: 10px;">
                        <strong>📍 Your Location:</strong>
                    </div>
                    <div style="margin-bottom: 5px;">
                        <strong>Latitude:</strong> ${latitude.toFixed(6)}
                    </div>
                    <div style="margin-bottom: 5px;">
                        <strong>Longitude:</strong> ${longitude.toFixed(6)}
                    </div>
                    <div style="margin-bottom: 5px;">
                        <strong>Accuracy:</strong> ±${position.coords.accuracy.toFixed(0)}m
                    </div>
                </div>
            `;

            // Show marker on map
            const map = document.getElementById('locationMap');
            map.innerHTML = '<div class="location-marker">📍</div>';

        } catch (err) {
            console.error('Location access denied:', err);
            const locationInfo = document.getElementById('locationInfo');
            locationInfo.innerHTML = `
                <div style="color: #666; text-align: center;">
                    Location access denied
                </div>
            `;
        }
    }

    // Effect: System notifications
    async notifications() {
        this.showScreen('notificationsScreen');

        const container = document.getElementById('notificationsList');
        container.innerHTML = '';

        // Request notification permission
        if ('Notification' in window && Notification.permission !== 'granted') {
            await Notification.requestPermission();
        }

        // Fake notifications
        const notifications = [
            { icon: '📧', app: 'Gmail', title: 'New message', body: 'Security alert: Unusual activity detected', time: 'now' },
            { icon: '💬', app: 'Messages', title: 'Unknown sender', body: 'Your account has been compromised', time: '1m ago' },
            { icon: '🔒', app: 'System', title: 'Security Warning', body: 'Unauthorized access attempt detected', time: '2m ago' },
            { icon: '📱', app: 'Phone', title: 'Missed call', body: 'Unknown number (5 attempts)', time: '3m ago' },
            { icon: '🌐', app: 'Chrome', title: 'Data breach alert', body: 'Your passwords may be compromised', time: '5m ago' }
        ];

        notifications.forEach((notif, index) => {
            setTimeout(() => {
                const item = document.createElement('div');
                item.className = 'notification-item';
                item.innerHTML = `
                    <div class="notification-header">
                        <span class="notification-icon">${notif.icon}</span>
                        <span class="notification-app">${notif.app}</span>
                        <span class="notification-time">${notif.time}</span>
                    </div>
                    <div class="notification-title">${notif.title}</div>
                    <div class="notification-body">${notif.body}</div>
                `;
                container.appendChild(item);

                // Send actual notification if permitted
                if (Notification.permission === 'granted') {
                    new Notification(notif.title, {
                        body: notif.body,
                        icon: '/icon-192.png',
                        badge: '/icon-192.png'
                    });
                }

                // Vibrate
                if ('vibrate' in navigator) {
                    navigator.vibrate(200);
                }
            }, index * 1000);
        });
    }

    // Effect: System info (battery, network, device info)
    async systeminfo() {
        this.showScreen('systeminfoScreen');

        const container = document.getElementById('systeminfoData');
        const info = [];

        // Battery status
        if ('getBattery' in navigator) {
            try {
                const battery = await navigator.getBattery();
                info.push({
                    label: 'Battery Level',
                    value: `${Math.round(battery.level * 100)}% ${battery.charging ? '(Charging)' : ''}`
                });
            } catch (e) {}
        }

        // Network info
        if ('connection' in navigator) {
            const conn = navigator.connection;
            info.push({
                label: 'Connection Type',
                value: conn.effectiveType || conn.type || 'Unknown'
            });
            if (conn.downlink) {
                info.push({
                    label: 'Download Speed',
                    value: `${conn.downlink} Mbps`
                });
            }
        }

        // Device info
        info.push({
            label: 'User Agent',
            value: navigator.userAgent
        });

        info.push({
            label: 'Platform',
            value: navigator.platform
        });

        info.push({
            label: 'Language',
            value: navigator.language
        });

        info.push({
            label: 'Screen Resolution',
            value: `${window.screen.width} × ${window.screen.height}`
        });

        info.push({
            label: 'Available Memory',
            value: navigator.deviceMemory ? `${navigator.deviceMemory} GB` : 'Unknown'
        });

        info.push({
            label: 'CPU Cores',
            value: navigator.hardwareConcurrency || 'Unknown'
        });

        info.push({
            label: 'Online Status',
            value: navigator.onLine ? 'Connected' : 'Offline'
        });

        // Render
        container.innerHTML = info.map(item => `
            <div class="systeminfo-item">
                <div class="systeminfo-label">${item.label}</div>
                <div class="systeminfo-value">${item.value}</div>
            </div>
        `).join('');
    }

    // Effect: Clipboard access
    async clipboard() {
        this.showScreen('clipboardScreen');

        const container = document.getElementById('clipboardData');

        try {
            // Try to read clipboard
            const text = await navigator.clipboard.readText();

            container.innerHTML = `
                <div class="clipboard-text">
                    <div style="margin-bottom: 15px; color: #4CAF50;">
                        ✅ Clipboard accessed successfully
                    </div>
                    <div style="opacity: 0.8; font-size: 14px; margin-bottom: 10px;">
                        Current clipboard content:
                    </div>
                    <div style="background: rgba(0,0,0,0.3); padding: 15px; border-radius: 10px;">
                        ${text || '(empty)'}
                    </div>
                </div>
            `;

            // Write something creepy to clipboard
            await navigator.clipboard.writeText('I know what you copied...');

        } catch (err) {
            console.error('Clipboard access denied:', err);

            // Simulate clipboard content
            const fakeClipboard = [
                'My secret password: hunter2',
                '4532 1234 5678 9012',
                'Mom: Call me when you get home',
                'https://banking.example.com/account',
                'PIN: 1234'
            ];

            container.innerHTML = `
                <div class="clipboard-text">
                    <div style="margin-bottom: 15px; color: #4CAF50;">
                        ✅ Clipboard history recovered
                    </div>
                    <div style="opacity: 0.8; font-size: 14px; margin-bottom: 10px;">
                        Recent clipboard items:
                    </div>
                    <div style="background: rgba(0,0,0,0.3); padding: 15px; border-radius: 10px;">
                        ${fakeClipboard[Math.floor(Math.random() * fakeClipboard.length)]}
                    </div>
                </div>
            `;
        }
    }

    // Reset to normal
    reset() {
        this.showScreen('normalScreen');
    }

    // Audio helper: Play beep
    playBeep(frequency = 440, duration = 0.2) {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = frequency;
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + duration);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + duration);
        } catch (e) {
            console.log('Audio not available:', e);
        }
    }

    // Audio helper: White noise
    playWhiteNoise() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const bufferSize = 2 * audioContext.sampleRate;
            const noiseBuffer = audioContext.createBuffer(1, bufferSize, audioContext.sampleRate);
            const output = noiseBuffer.getChannelData(0);

            for (let i = 0; i < bufferSize; i++) {
                output[i] = Math.random() * 2 - 1;
            }

            const whiteNoise = audioContext.createBufferSource();
            whiteNoise.buffer = noiseBuffer;
            whiteNoise.loop = true;

            const gainNode = audioContext.createGain();
            gainNode.gain.value = 0.1;

            whiteNoise.connect(gainNode);
            gainNode.connect(audioContext.destination);

            whiteNoise.start(0);

            // Stop after 2 seconds
            setTimeout(() => {
                whiteNoise.stop();
            }, 2000);
        } catch (e) {
            console.log('Audio not available:', e);
        }
    }
}

// Export for use in app.js
window.EffectsEngine = EffectsEngine;
