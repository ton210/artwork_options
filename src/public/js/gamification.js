/**
 * Gamification System for Dispensary Site
 * Adds visual celebrations and feedback for user interactions
 */
(function() {
  'use strict';

  // ===== CONFIGURATION =====
  const CONFIG = {
    emojis: {
      review: ['⭐', '🌟', '✨', '💚', '🎉'],
      vote: ['👍', '💚', '🔥', '✅', '🎊'],
      click: ['👆', '💫', '✨'],
      milestone: ['🏆', '👑', '🎯', '💎']
    },
    sounds: {
      enabled: true,
      volume: 0.3
    }
  };

  // ===== CELEBRATION EFFECTS =====
  const Celebrations = {
    /**
     * Trigger emoji confetti celebration
     */
    celebrate(type = 'review', intensity = 50) {
      const container = document.createElement('div');
      container.className = 'celebration-container';
      container.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 999999;
      `;

      document.body.appendChild(container);

      // Create particles
      const emojis = CONFIG.emojis[type] || CONFIG.emojis.review;
      const particleCount = Math.floor((intensity / 100) * 50);

      for (let i = 0; i < particleCount; i++) {
        setTimeout(() => {
          this.createParticle(container, emojis);
        }, i * 40);
      }

      // Play sound
      this.playSound(type);

      // Cleanup
      setTimeout(() => {
        container.remove();
      }, 5000);
    },

    /**
     * Create individual particle
     */
    createParticle(container, emojis) {
      const particle = document.createElement('div');
      const emoji = emojis[Math.floor(Math.random() * emojis.length)];

      particle.innerHTML = emoji;
      particle.style.cssText = `
        position: absolute;
        left: ${Math.random() * 100}%;
        top: -50px;
        font-size: ${20 + Math.random() * 30}px;
        animation: fall ${2 + Math.random() * 2}s linear forwards;
        transform: rotate(${Math.random() * 360}deg);
        pointer-events: none;
        text-shadow: 0 0 10px rgba(255,255,255,0.5);
      `;

      container.appendChild(particle);
      setTimeout(() => particle.remove(), 4000);
    },

    /**
     * Show success message
     */
    showMessage(text, type = 'success') {
      const colors = {
        success: ['#10b981', '#059669'],
        info: ['#3b82f6', '#2563eb'],
        warning: ['#f59e0b', '#d97706'],
        milestone: ['#8b5cf6', '#7c3aed']
      };

      const messageEl = document.createElement('div');
      messageEl.className = 'celebration-message';
      messageEl.style.cssText = `
        position: fixed;
        top: 20%;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, ${colors[type][0]}, ${colors[type][1]});
        color: white;
        padding: 20px 40px;
        border-radius: 50px;
        font-size: 20px;
        font-weight: bold;
        z-index: 1000000;
        animation: bounce 0.5s ease, fadeOut 0.5s ease 2s forwards;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        text-align: center;
        pointer-events: none;
      `;
      messageEl.textContent = text;
      document.body.appendChild(messageEl);

      setTimeout(() => messageEl.remove(), 2500);
    },

    /**
     * Pulse effect on element
     */
    pulseElement(element, color = '#10b981') {
      if (!element) return;

      const originalTransform = element.style.transform;
      const originalBoxShadow = element.style.boxShadow;

      element.style.transition = 'all 0.3s ease';
      element.style.transform = 'scale(1.1)';
      element.style.boxShadow = `0 0 20px ${color}`;

      setTimeout(() => {
        element.style.transform = originalTransform;
        element.style.boxShadow = originalBoxShadow;
      }, 300);
    },

    /**
     * Play celebration sound
     */
    playSound(type) {
      if (!CONFIG.sounds.enabled) return;

      // Create simple audio feedback using Web Audio API
      try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        // Different frequencies for different actions
        const frequencies = {
          review: [523.25, 659.25, 783.99], // C5-E5-G5 (happy chord)
          vote: [440, 554.37],                // A4-C#5
          click: [523.25],                    // C5
          milestone: [523.25, 659.25, 783.99, 1046.50] // C5-E5-G5-C6
        };

        const notes = frequencies[type] || frequencies.click;
        let time = audioContext.currentTime;

        notes.forEach((freq, index) => {
          const osc = audioContext.createOscillator();
          const gain = audioContext.createGain();

          osc.connect(gain);
          gain.connect(audioContext.destination);

          osc.frequency.value = freq;
          osc.type = 'sine';

          gain.gain.setValueAtTime(CONFIG.sounds.volume, time);
          gain.gain.exponentialRampToValueAtTime(0.01, time + 0.3);

          osc.start(time);
          osc.stop(time + 0.3);

          time += 0.15;
        });
      } catch (error) {
        // Audio not supported, silently fail
      }
    }
  };

  // ===== POINTS SYSTEM =====
  const PointsSystem = {
    points: {
      review: 10,
      vote: 2,
      click: 1,
      share: 5
    },

    /**
     * Award points for action
     */
    awardPoints(action, showNotification = true) {
      const earnedPoints = this.points[action] || 0;
      const totalPoints = this.getTotalPoints() + earnedPoints;

      this.setTotalPoints(totalPoints);

      if (showNotification && earnedPoints > 0) {
        Celebrations.showMessage(`+${earnedPoints} points!`, 'success');
      }

      // Check for milestones
      this.checkMilestones(totalPoints);

      return earnedPoints;
    },

    /**
     * Get total points from localStorage
     */
    getTotalPoints() {
      try {
        return parseInt(localStorage.getItem('dispensary_points') || '0');
      } catch (e) {
        return 0;
      }
    },

    /**
     * Set total points
     */
    setTotalPoints(points) {
      try {
        localStorage.setItem('dispensary_points', points.toString());
        this.updatePointsDisplay();
      } catch (e) {
        // LocalStorage not available
      }
    },

    /**
     * Update points display if element exists
     */
    updatePointsDisplay() {
      const display = document.getElementById('user-points-display');
      if (display) {
        display.textContent = this.getTotalPoints();
      }
    },

    /**
     * Check and celebrate milestones
     */
    checkMilestones(points) {
      const milestones = [10, 25, 50, 100, 250, 500, 1000];
      const lastMilestone = parseInt(localStorage.getItem('last_milestone') || '0');

      for (const milestone of milestones) {
        if (points >= milestone && lastMilestone < milestone) {
          localStorage.setItem('last_milestone', milestone.toString());
          Celebrations.celebrate('milestone', 80);
          Celebrations.showMessage(`🏆 ${milestone} Points Milestone!`, 'milestone');
          break;
        }
      }
    }
  };

  // ===== AUTO-ATTACH TO EXISTING INTERACTIONS =====
  function attachGamification() {
    // Review submission
    const reviewForm = document.getElementById('review-form');
    if (reviewForm) {
      reviewForm.addEventListener('submit', function(e) {
        setTimeout(() => {
          Celebrations.celebrate('review', 70);
          Celebrations.showMessage('Thanks for your review! 🌟', 'success');
          PointsSystem.awardPoints('review');
        }, 500);
      });
    }

    // Vote buttons
    document.querySelectorAll('[data-vote-btn], .vote-btn, .upvote-btn, .downvote-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        Celebrations.celebrate('vote', 40);
        Celebrations.pulseElement(this, '#10b981');
        PointsSystem.awardPoints('vote');
      });
    });

    // Review helpful buttons
    document.addEventListener('click', function(e) {
      if (e.target.closest('.helpful-btn')) {
        Celebrations.pulseElement(e.target.closest('.helpful-btn'), '#3b82f6');
        PointsSystem.awardPoints('click', false);
      }
    });

    // Track clicks (phone, website, directions)
    document.querySelectorAll('[data-track]').forEach(el => {
      el.addEventListener('click', function() {
        Celebrations.pulseElement(this, '#f59e0b');
        PointsSystem.awardPoints('click', false);
      });
    });
  }

  // ===== CSS ANIMATIONS =====
  function injectStyles() {
    if (document.getElementById('gamification-styles')) return;

    const style = document.createElement('style');
    style.id = 'gamification-styles';
    style.textContent = `
      @keyframes fall {
        to {
          transform: translateY(100vh) rotate(720deg);
          opacity: 0;
        }
      }
      @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0) translateX(-50%); }
        40% { transform: translateY(-20px) translateX(-50%); }
        60% { transform: translateY(-10px) translateX(-50%); }
      }
      @keyframes fadeOut {
        to {
          opacity: 0;
          transform: translateY(-20px) translateX(-50%);
        }
      }
      .celebration-container {
        overflow: hidden;
      }
    `;
    document.head.appendChild(style);
  }

  // ===== INITIALIZE =====
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      injectStyles();
      attachGamification();
      PointsSystem.updatePointsDisplay();
    });
  } else {
    injectStyles();
    attachGamification();
    PointsSystem.updatePointsDisplay();
  }

  // ===== GLOBAL EXPORT =====
  window.DispensaryGamification = {
    celebrate: Celebrations.celebrate.bind(Celebrations),
    showMessage: Celebrations.showMessage.bind(Celebrations),
    pulseElement: Celebrations.pulseElement.bind(Celebrations),
    awardPoints: PointsSystem.awardPoints.bind(PointsSystem),
    getPoints: PointsSystem.getTotalPoints.bind(PointsSystem)
  };

  // Test function
  window.testGamification = function() {
    Celebrations.celebrate('review', 70);
    Celebrations.showMessage('🎉 Gamification Test!', 'success');
    PointsSystem.awardPoints('review');
  };

})();
