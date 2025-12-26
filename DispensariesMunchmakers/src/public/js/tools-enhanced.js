// Enhanced Cannabis Tools Suite
(function() {
  'use strict';

  let charts = {}; // Store chart instances
  let timerInterval = null;
  let sessionStartTime = null;

  // ===== TAB SWITCHING =====
  window.switchTab = function(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));

    // Show selected tab
    document.getElementById(tabName + '-tab')?.classList.remove('hidden');
    document.querySelector(`[data-tab="${tabName}"]`)?.classList.add('active');
  };

  // ===== ADVANCED DOSAGE CALCULATOR =====
  window.calculateAdvancedDosage = function() {
    const experience = document.getElementById('exp-level').value;
    const weight = parseInt(document.getElementById('body-weight').value);
    const metabolism = document.getElementById('metabolism').value;
    const foodStatus = document.getElementById('food-status').value;
    const intensity = document.getElementById('intensity-level').value;

    // Base dosages by experience and intensity
    const baseDoses = {
      'first-time': { microdose: 1, mild: 2, moderate: 3, strong: 5 },
      'beginner': { microdose: 2, mild: 2.5, moderate: 5, strong: 7.5 },
      'occasional': { microdose: 3, mild: 5, moderate: 10, strong: 15 },
      'regular': { microdose: 5, mild: 10, moderate: 20, strong: 30 },
      'experienced': { microdose: 10, mild: 20, moderate: 40, strong: 60 }
    };

    let dose = baseDoses[experience][intensity];

    // Weight adjustment (±20%)
    if (weight < 120) dose *= 0.85;
    else if (weight < 150) dose *= 0.95;
    else if (weight > 200) dose *= 1.15;
    else if (weight > 250) dose *= 1.25;

    // Metabolism adjustment
    if (metabolism === 'fast') dose *= 1.15;
    else if (metabolism === 'slow') dose *= 0.85;

    // Food adjustment (affects absorption)
    if (foodStatus === 'empty') dose *= 0.8; // Hits harder on empty stomach
    else if (foodStatus === 'full') dose *= 1.1; // Need more with food

    dose = Math.round(dose * 10) / 10;

    // Calculate onset and duration
    let onset, peak, duration;
    if (foodStatus === 'empty') {
      onset = '30-60 minutes';
      peak = '1-2 hours';
      duration = '4-6 hours';
    } else if (foodStatus === 'light') {
      onset = '45-90 minutes';
      peak = '2-3 hours';
      duration = '6-8 hours';
    } else {
      onset = '60-120 minutes';
      peak = '2-4 hours';
      duration = '6-10 hours';
    }

    // Display results
    document.getElementById('dose-amount').textContent = dose + ' mg THC';

    const details = document.getElementById('dose-details');
    details.innerHTML = `
      <div class="p-4 bg-white rounded-lg border">
        <div class="font-semibold mb-2">Timeline:</div>
        <div class="space-y-1 text-sm">
          <div>⏱️ <strong>Onset:</strong> ${onset}</div>
          <div>📈 <strong>Peak Effects:</strong> ${peak}</div>
          <div>⌛ <strong>Total Duration:</strong> ${duration}</div>
        </div>
      </div>
      <div class="p-4 rounded-lg ${intensity === 'microdose' ? 'bg-green-100' : intensity === 'strong' ? 'bg-red-100' : 'bg-yellow-100'}">
        <div class="font-semibold mb-1">Safety Advice:</div>
        <div class="text-sm">
          ${getSafetyAdvice(experience, intensity, dose)}
        </div>
      </div>
      <div class="p-4 bg-gray-50 rounded-lg text-xs text-gray-600">
        <strong>Note:</strong> Individual responses vary. Start low, go slow, and wait full duration before taking more.
      </div>
    `;

    document.getElementById('advanced-dosage-result').classList.remove('hidden');

    // Create dosage comparison chart
    createDosageChart(experience, dose);
  };

  function getSafetyAdvice(exp, intensity, dose) {
    if (exp === 'first-time') {
      return '🚨 First time? Start with HALF this dose. Wait 2-3 hours. Edibles take time!';
    } else if (intensity === 'strong') {
      return '⚠️ High dose. Ensure safe environment, no driving/responsibilities for 8+ hours.';
    } else if (intensity === 'microdose') {
      return '✅ Microdose - subtle effects, safe for daytime use. Functional and mild.';
    } else {
      return '✅ Moderate dose. Should provide pleasant effects without overwhelming you.';
    }
  }

  function createDosageChart(experience, yourDose) {
    const ctx = document.getElementById('dosage-chart');
    if (!ctx) return;

    // Destroy existing chart
    if (charts.dosage) charts.dosage.destroy();

    const averageDoses = {
      'first-time': 2.5,
      'beginner': 5,
      'occasional': 10,
      'regular': 20,
      'experienced': 40
    };

    charts.dosage = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Your Dose', 'Average for Your Level', 'Beginner Avg', 'Experienced Avg'],
        datasets: [{
          label: 'THC (mg)',
          data: [yourDose, averageDoses[experience], 5, 40],
          backgroundColor: ['#10b981', '#3b82f6', '#94a3b8', '#6b7280']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: { display: true, text: 'Dosage Comparison' }
        },
        scales: {
          y: { beginAtZero: true, title: { display: true, text: 'mg THC' } }
        }
      }
    });
  }

  // ===== ONSET TIME CALCULATOR =====
  window.calculateOnsetTime = function() {
    const productType = document.getElementById('product-type').value;
    const dosage = parseInt(document.getElementById('onset-dosage').value);
    const mealTiming = parseInt(document.getElementById('meal-timing').value);

    const timings = {
      'smoking': { onset: [1, 5], peak: [10, 30], duration: [1, 3], method: 'Smoking/Vaping' },
      'vaping': { onset: [1, 5], peak: [10, 30], duration: [1, 3], method: 'Vaporizing' },
      'dabbing': { onset: [1, 3], peak: [5, 15], duration: [2, 4], method: 'Dabbing' },
      'edibles': { onset: [30, 120], peak: [120, 240], duration: [360, 600], method: 'Edibles' },
      'tincture': { onset: [15, 45], peak: [60, 120], duration: [180, 360], method: 'Tincture' },
      'topical': { onset: [15, 30], peak: [30, 60], duration: [120, 300], method: 'Topical' }
    };

    const timing = timings[productType] || timings.edibles;

    // Adjust for meal timing (edibles only)
    if (productType === 'edibles') {
      if (mealTiming === 0) { // Just ate
        timing.onset = [60, 150];
        timing.peak = [180, 300];
      } else if (mealTiming >= 5) { // Empty stomach
        timing.onset = [20, 60];
        timing.peak = [90, 150];
      }
    }

    const now = new Date();
    const onsetTime = new Date(now.getTime() + timing.onset[1] * 60000);
    const peakTime = new Date(now.getTime() + timing.peak[1] * 60000);
    const endTime = new Date(now.getTime() + timing.duration[1] * 60000);

    const timeline = document.getElementById('onset-timeline');
    timeline.innerHTML = `
      <div class="space-y-3">
        <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
          <div>
            <div class="font-semibold">Consumption Method:</div>
            <div class="text-sm text-gray-600">${timing.method}</div>
          </div>
          <div class="text-2xl">${productType === 'smoking' ? '💨' : productType === 'edibles' ? '🍪' : productType === 'vaping' ? '💨' : '💧'}</div>
        </div>

        <div class="p-4 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg border border-yellow-200">
          <div class="font-semibold mb-2">⏱️ Expected Timeline:</div>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span>Onset (effects begin):</span>
              <strong>${timing.onset[0]}-${timing.onset[1]} min</strong>
            </div>
            <div class="flex justify-between">
              <span>Peak (strongest effects):</span>
              <strong>${Math.floor(timing.peak[0]/60)}-${Math.floor(timing.peak[1]/60)} hours</strong>
            </div>
            <div class="flex justify-between">
              <span>Total duration:</span>
              <strong>${Math.floor(timing.duration[0]/60)}-${Math.floor(timing.duration[1]/60)} hours</strong>
            </div>
          </div>
        </div>

        <div class="p-4 bg-green-50 rounded-lg border border-green-200">
          <div class="font-semibold mb-2">🕐 Specific Times:</div>
          <div class="space-y-1 text-sm">
            <div>Effects start: <strong>${onsetTime.toLocaleTimeString()}</strong></div>
            <div>Peak effects: <strong>${peakTime.toLocaleTimeString()}</strong></div>
            <div>Effects end: <strong>${endTime.toLocaleTimeString()}</strong></div>
            <div class="mt-2 pt-2 border-t text-gray-600">Safe to drive after: <strong>${new Date(endTime.getTime() + 120 * 60000).toLocaleTimeString()}</strong></div>
          </div>
        </div>
      </div>
    `;

    document.getElementById('onset-result').classList.remove('hidden');
    createOnsetChart(timing);
  };

  function createOnsetChart(timing) {
    const ctx = document.getElementById('onset-chart');
    if (!ctx) return;

    if (charts.onset) charts.onset.destroy();

    const timePoints = [0, timing.onset[1], timing.peak[1], timing.duration[1]];
    const intensityPoints = [0, 30, 100, 0];

    charts.onset = new Chart(ctx, {
      type: 'line',
      data: {
        labels: timePoints.map(t => Math.floor(t/60) + 'h'),
        datasets: [{
          label: 'Effect Intensity (%)',
          data: intensityPoints,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: { display: true, text: 'Effects Over Time' }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            title: { display: true, text: 'Intensity %' }
          },
          x: { title: { display: true, text: 'Time' } }
        }
      }
    });
  }

  // ===== ADVANCED STRAIN FINDER =====
  window.runAdvancedStrainFinder = function() {
    const time = document.getElementById('sq-time').value;
    const goal = document.getElementById('sq-goal').value;
    const flavor = document.getElementById('sq-flavor').value;
    const anxiety = document.getElementById('sq-anxiety').value;

    // Comprehensive strain database
    const strainDatabase = {
      'morning-energy-low': ['Green Crack', 'Durban Poison', 'Super Lemon Haze', 'Strawberry Cough'],
      'morning-focus-low': ['Jack Herer', 'Harlequin', 'Sour Diesel', 'Tangie'],
      'evening-relax-low': ['Blue Dream', 'Northern Lights', 'Granddaddy Purple'],
      'evening-sleep-low': ['GDP', 'Purple Kush', 'Afghan Kush', 'Bubba Kush'],
      'anytime-pain-any': ['ACDC', 'Harlequin', 'Blue Dream', 'Girl Scout Cookies'],
      'anytime-creative-low': ['Jack Herer', 'Tangie', 'Trainwreck', 'Super Silver Haze'],
      'anytime-social-medium': ['Wedding Cake', 'Gelato', 'Zkittlez', 'Sunset Sherbet'],

      // High anxiety versions (lower THC/higher CBD)
      'any-any-high': ['ACDC', 'Harlequin', 'Charlotte\'s Web', 'Cannatonic', 'Pennywise']
    };

    let key = '';
    if (anxiety === 'high') {
      key = 'any-any-high';
    } else {
      const timeKey = (time === 'morning' || time === 'afternoon') ? 'morning' : time === 'night' ? 'evening' : 'anytime';
      const goalKey = goal;
      key = `${timeKey}-${goalKey}-${anxiety}`;

      // Fallback keys
      if (!strainDatabase[key]) {
        key = `${timeKey}-${goalKey}-low`;
      }
      if (!strainDatabase[key]) {
        key = `anytime-${goalKey}-any`;
      }
      if (!strainDatabase[key]) {
        key = 'anytime-social-medium';
      }
    }

    const strains = strainDatabase[key] || strainDatabase['anytime-social-medium'];

    // Display top 5 strains
    const matchesHTML = strains.slice(0, 5).map((strain, index) => `
      <div class="p-4 bg-white rounded-lg border-2 ${index === 0 ? 'border-purple-400 shadow-md' : 'border-gray-200'}">
        <div class="flex items-start justify-between mb-2">
          <div>
            <div class="font-bold text-lg">${index + 1}. ${strain}</div>
            <div class="text-xs text-gray-500">${index === 0 ? '⭐ Best Match' : 'Great Option'}</div>
          </div>
          ${index === 0 ? '<div class="text-3xl">🏆</div>' : ''}
        </div>
        <div class="text-sm text-gray-600 mb-2">
          ${getStrainDescription(strain, goal, flavor)}
        </div>
        <div class="flex items-center justify-between text-xs mt-3 pt-3 border-t">
          <span class="text-gray-500">THC: ${getStrainTHC(strain, anxiety)}</span>
          <a href="/" class="text-blue-600 hover:underline">Find dispensaries →</a>
        </div>
      </div>
    `).join('');

    document.getElementById('strain-matches').innerHTML = matchesHTML;
    document.getElementById('advanced-strain-result').classList.remove('hidden');
  };

  function getStrainDescription(strain, goal, flavor) {
    const descriptions = {
      'Green Crack': 'Energizing sativa perfect for daytime productivity',
      'Durban Poison': 'Pure sativa for energy and mental clarity',
      'Blue Dream': 'Balanced hybrid for relaxation without sedation',
      'Northern Lights': 'Classic indica for deep relaxation',
      'Jack Herer': 'Creative sativa with clear-headed focus',
      'ACDC': 'High-CBD strain for anxiety and pain without high',
      'Harlequin': 'Balanced CBD:THC for therapeutic effects'
    };
    return descriptions[strain] || 'Excellent choice for your needs';
  }

  function getStrainTHC(strain, anxietyLevel) {
    if (anxietyLevel === 'high' || strain.includes('ACDC') || strain.includes('Harlequin')) {
      return '8-12% | CBD: 10-15%';
    }
    return '18-24%';
  }

  // ===== SAVINGS CALCULATOR =====
  window.calculateCompleteSavings = function() {
    const weekly = parseFloat(document.getElementById('weekly-spend').value);
    const discount = parseFloat(document.getElementById('avg-discount').value);
    const loyalty = parseFloat(document.getElementById('loyalty-cash').value);
    const bulk = parseFloat(document.getElementById('bulk-discount').value);

    const annual = weekly * 52;
    const discountSave = annual * (discount / 100);
    const loyaltySave = annual * (loyalty / 100);
    const bulkSave = annual * (bulk / 100);
    const total = discountSave + loyaltySave + bulkSave;

    document.getElementById('total-spend').textContent = '$' + annual.toLocaleString();
    document.getElementById('total-save').textContent = '$' + Math.round(total).toLocaleString();

    const breakdown = document.getElementById('savings-breakdown');
    breakdown.innerHTML = `
      <div class="space-y-2">
        <div class="flex justify-between p-2 bg-white rounded">
          <span>💚 Regular Discounts:</span>
          <strong class="text-green-600">$${Math.round(discountSave).toLocaleString()}</strong>
        </div>
        <div class="flex justify-between p-2 bg-white rounded">
          <span>🎁 Loyalty Rewards:</span>
          <strong class="text-blue-600">$${Math.round(loyaltySave).toLocaleString()}</strong>
        </div>
        <div class="flex justify-between p-2 bg-white rounded">
          <span>📦 Bulk Savings:</span>
          <strong class="text-purple-600">$${Math.round(bulkSave).toLocaleString()}</strong>
        </div>
        <div class="flex justify-between p-3 bg-green-100 rounded font-bold text-lg border-2 border-green-300">
          <span>Total Annual Savings:</span>
          <span class="text-green-700">$${Math.round(total).toLocaleString()}</span>
        </div>
        <div class="text-center text-sm text-gray-600 mt-2">
          That's <strong class="text-green-600">${Math.round((total/annual)*100)}%</strong> of your budget saved!
        </div>
      </div>
    `;

    document.getElementById('complete-savings-result').classList.remove('hidden');
    createSavingsChart(discountSave, loyaltySave, bulkSave);
  };

  function createSavingsChart(discount, loyalty, bulk) {
    const ctx = document.getElementById('savings-chart');
    if (!ctx) return;

    if (charts.savings) charts.savings.destroy();

    charts.savings = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Discounts', 'Loyalty', 'Bulk'],
        datasets: [{
          data: [discount, loyalty, bulk],
          backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' },
          title: { display: true, text: 'Savings Breakdown' }
        }
      }
    });
  }

  // ===== MEDICAL CARD ROI =====
  window.calculateMedicalROI = function() {
    const monthly = parseFloat(document.getElementById('monthly-spending').value);
    const cardCost = parseFloat(document.getElementById('card-cost').value);
    const recTax = parseFloat(document.getElementById('rec-tax').value);
    const medTax = parseFloat(document.getElementById('med-tax').value);

    const annual = monthly * 12;
    const recTaxAmount = annual * (recTax / 100);
    const medTaxAmount = annual * (medTax / 100);
    const taxSavings = recTaxAmount - medTaxAmount;
    const netSavings = taxSavings - cardCost;
    const breakEven = Math.ceil(cardCost / (taxSavings / 12));
    const worthIt = netSavings > 0;

    const summary = document.getElementById('roi-summary');
    summary.innerHTML = `
      <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="text-center p-3 bg-white rounded-lg">
          <div class="text-xs text-gray-600">Card Cost</div>
          <div class="text-xl font-bold">$${cardCost}</div>
        </div>
        <div class="text-center p-3 bg-white rounded-lg">
          <div class="text-xs text-gray-600">Tax Savings/Year</div>
          <div class="text-xl font-bold text-green-600">$${Math.round(taxSavings)}</div>
        </div>
        <div class="text-center p-3 bg-white rounded-lg">
          <div class="text-xs text-gray-600">Break-Even</div>
          <div class="text-xl font-bold">${breakEven} mo</div>
        </div>
      </div>
      <div class="text-sm space-y-2">
        <div class="flex justify-between p-2 bg-white rounded">
          <span>Recreational tax (${recTax}%):</span>
          <span class="text-red-600">-$${Math.round(recTaxAmount)}/yr</span>
        </div>
        <div class="flex justify-between p-2 bg-white rounded">
          <span>Medical tax (${medTax}%):</span>
          <span class="text-orange-600">-$${Math.round(medTaxAmount)}/yr</span>
        </div>
        <div class="flex justify-between p-2 bg-green-100 rounded font-semibold">
          <span>Net savings after card:</span>
          <span class="${netSavings > 0 ? 'text-green-600' : 'text-red-600'}">$${Math.round(netSavings)}/yr</span>
        </div>
      </div>
    `;

    const recommendation = document.getElementById('roi-recommendation');
    if (worthIt) {
      recommendation.className = 'p-4 rounded-lg font-semibold text-center bg-green-100 border-2 border-green-400 text-green-800';
      recommendation.innerHTML = `
        ✅ <strong>YES</strong> - A medical card will save you $${Math.round(netSavings)} per year!<br>
        <span class="text-sm font-normal">Pays for itself in ${breakEven} month${breakEven > 1 ? 's' : ''}!</span>
      `;
    } else {
      recommendation.className = 'p-4 rounded-lg font-semibold text-center bg-red-100 border-2 border-red-400 text-red-800';
      recommendation.innerHTML = `
        ❌ <strong>NOT WORTH IT</strong> - You won't save enough to justify the cost.<br>
        <span class="text-sm font-normal">Consider if you plan to increase usage or tax rates rise.</span>
      `;
    }

    document.getElementById('medical-roi-result').classList.remove('hidden');
    createROIChart(cardCost, taxSavings, breakEven);
  };

  function createROIChart(cardCost, savings, breakEven) {
    const ctx = document.getElementById('roi-chart');
    if (!ctx) return;

    if (charts.roi) charts.roi.destroy();

    const months = Array.from({length: 12}, (_, i) => i + 1);
    const cumulativeSavings = months.map(m => (savings / 12) * m - cardCost);

    charts.roi = new Chart(ctx, {
      type: 'line',
      data: {
        labels: months.map(m => 'Month ' + m),
        datasets: [{
          label: 'Net Savings ($)',
          data: cumulativeSavings,
          borderColor: '#8b5cf6',
          backgroundColor: 'rgba(139, 92, 246, 0.1)',
          fill: true,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: { display: true, text: 'Savings Over 12 Months' }
        },
        scales: {
          y: {
            title: { display: true, text: 'Net Savings ($)' },
            grid: { color: (context) => context.tick.value === 0 ? '#000' : '#e5e7eb' }
          }
        }
      }
    });
  }

  // ===== TERPENE PROFILE BUILDER =====
  window.buildTerpeneProfile = function() {
    const selected = Array.from(document.querySelectorAll('.terp-effect:checked')).map(cb => cb.value);

    if (selected.length === 0) {
      alert('Please select at least one desired effect');
      return;
    }

    const terpeneDatabase = {
      relaxation: [
        { name: 'Linalool', desc: 'Floral lavender aroma. Calming, anti-anxiety, sleep aid.', percentage: '0.5-1.5%', strains: ['Granddaddy Purple', 'Lavender', 'LA Confidential'] },
        { name: 'Myrcene', desc: 'Earthy, musky aroma. Sedating, muscle relaxant, enhances THC.', percentage: '0.5-3%', strains: ['Blue Dream', 'OG Kush', 'Granddaddy Purple'] }
      ],
      energy: [
        { name: 'Limonene', desc: 'Bright citrus aroma. Uplifting, mood-enhancing, stress relief.', percentage: '1-3%', strains: ['Super Lemon Haze', 'Durban Poison', 'Sour Diesel'] },
        { name: 'Pinene', desc: 'Pine forest aroma. Alertness, memory, anti-inflammatory.', percentage: '0.5-2%', strains: ['Jack Herer', 'Blue Dream', 'OG Kush'] }
      ],
      pain: [
        { name: 'Caryophyllene', desc: 'Peppery, spicy aroma. Anti-inflammatory, pain relief, unique cannabinoid-like effects.', percentage: '0.5-2%', strains: ['Girl Scout Cookies', 'Bubba Kush', 'Sour Diesel'] },
        { name: 'Myrcene', desc: 'Strong analgesic properties. Enhances other pain-relieving compounds.', percentage: '0.5-3%', strains: ['Blue Dream', 'White Widow', 'Special Kush'] }
      ],
      sleep: [
        { name: 'Myrcene', desc: 'Highly sedating. The "couch-lock" terpene. Promotes deep sleep.', percentage: '1-3%', strains: ['Granddaddy Purple', 'Northern Lights', 'Purple Kush'] },
        { name: 'Linalool', desc: 'Reduces anxiety that prevents sleep. Calming and soothing.', percentage: '0.5-1.5%', strains: ['LA Confidential', 'Zkittlez', 'Do-Si-Dos'] }
      ],
      mood: [
        { name: 'Limonene', desc: 'Mood elevation and stress relief. Increases serotonin and dopamine.', percentage: '1-3%', strains: ['Tangie', 'Wedding Cake', 'MAC'] },
        { name: 'Terpinolene', desc: 'Uplifting, happy effects. Less common but powerful.', percentage: '0.1-1%', strains: ['Jack Herer', 'Golden Goat', 'Dutch Treat'] }
      ],
      focus: [
        { name: 'Pinene', desc: 'Improves focus and memory retention. Counteracts THC memory impairment.', percentage: '0.5-2%', strains: ['Jack Herer', 'Trainwreck', 'Blue Dream'] },
        { name: 'Limonene', desc: 'Mental clarity and alertness. Reduces brain fog.', percentage: '1-3%', strains: ['Super Lemon Haze', 'Lemon Haze', 'Tangie'] }
      ],
      anxiety: [
        { name: 'Linalool', desc: 'Powerful anti-anxiety effects. Calms nervous system.', percentage: '0.5-1.5%', strains: ['Lavender', 'Zkittlez', 'Amnesia Haze'] },
        { name: 'Limonene', desc: 'Stress relief and mood stabilization.', percentage: '1-3%', strains: ['Lemon Haze', 'Berry White', 'Quantum Kush'] }
      ],
      inflammation: [
        { name: 'Caryophyllene', desc: 'Binds to CB2 receptors. Strong anti-inflammatory.', percentage: '0.5-2%', strains: ['Girl Scout Cookies', 'Bubba Kush', 'Chemdog'] },
        { name: 'Humulene', desc: 'Anti-inflammatory, appetite suppressant.', percentage: '0.2-1%', strains: ['White Widow', 'Headband', 'Sour Diesel'] }
      ]
    };

    const allTerpenes = new Map();
    const recommendedStrains = new Set();

    selected.forEach(effect => {
      const terpenes = terpeneDatabase[effect] || [];
      terpenes.forEach(terp => {
        if (!allTerpenes.has(terp.name)) {
          allTerpenes.set(terp.name, terp);
        }
        terp.strains.forEach(s => recommendedStrains.add(s));
      });
    });

    const terpHTML = Array.from(allTerpenes.values()).map(terp => `
      <div class="p-4 bg-white rounded-lg border-2 border-indigo-200">
        <div class="flex items-start gap-3">
          <div class="text-3xl">🌿</div>
          <div class="flex-1">
            <div class="font-bold text-lg">${terp.name}</div>
            <div class="text-sm text-gray-600 mb-2">${terp.desc}</div>
            <div class="text-xs text-gray-500">Look for: ${terp.percentage} content</div>
          </div>
        </div>
      </div>
    `).join('');

    const strainTags = Array.from(recommendedStrains).slice(0, 10).map(strain => `
      <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-medium">${strain}</span>
    `).join('');

    document.getElementById('terpene-recommendations').innerHTML = terpHTML;
    document.getElementById('terpene-strains').innerHTML = strainTags || '<span class="text-gray-500">No specific strains matched</span>';
    document.getElementById('terpene-profile-result').classList.remove('hidden');
  };

  // ===== TOLERANCE BREAK PLANNER =====
  window.planToleranceBreak = function() {
    const frequency = document.getElementById('usage-freq').value;
    const amount = document.getElementById('daily-amount').value;
    const budget = parseFloat(document.getElementById('t-break-budget').value);

    const breakLengths = {
      'daily-heavy': 21,
      'daily': 14,
      'frequent': 10,
      'weekly': 7,
      'occasional': 3
    };

    const breakDays = breakLengths[frequency];
    const moneySaved = budget * (breakDays / 7);

    const plan = document.getElementById('t-break-plan');
    plan.innerHTML = `
      <div class="text-center p-6 bg-white rounded-lg border-2 border-teal-300">
        <div class="text-sm text-gray-600 mb-1">Recommended Break Length:</div>
        <div class="text-5xl font-bold text-teal-600 mb-2">${breakDays} Days</div>
        <div class="text-sm text-gray-600">Money Saved: <strong class="text-green-600">$${Math.round(moneySaved)}</strong></div>
      </div>

      <div class="space-y-2">
        <div class="font-semibold">What to Expect:</div>
        ${getToleranceBreakTimeline(breakDays)}
      </div>

      <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
        <div class="font-semibold mb-2">💡 Success Tips:</div>
        <ul class="text-sm space-y-1 list-disc list-inside">
          <li>Stay busy - pick up a hobby or project</li>
          <li>Exercise helps with sleep and mood</li>
          <li>Hydrate more than usual</li>
          <li>Expect vivid dreams to return</li>
          <li>Cravings peak days 2-4, then improve</li>
          <li>Consider CBD to ease transition</li>
        </ul>
      </div>

      <div class="p-4 bg-green-50 rounded-lg border border-green-200">
        <div class="font-semibold mb-1">✨ After Your Break:</div>
        <div class="text-sm">
          Your tolerance will be significantly reset! Start with 50% of your previous dose to avoid overwhelming effects.
        </div>
      </div>
    `;

    document.getElementById('t-break-result').classList.remove('hidden');
  };

  function getToleranceBreakTimeline(days) {
    return `
      <div class="text-sm space-y-2">
        <div class="flex items-start gap-2 p-2 bg-white rounded">
          <span class="font-semibold">Day 1-2:</span>
          <span class="text-gray-600">Mild irritability, possible sleep issues. Physical cravings peak.</span>
        </div>
        <div class="flex items-start gap-2 p-2 bg-white rounded">
          <span class="font-semibold">Day 3-5:</span>
          <span class="text-gray-600">Mental cravings highest. Stay busy! Sleep improving.</span>
        </div>
        <div class="flex items-start gap-2 p-2 bg-white rounded">
          <span class="font-semibold">Day 6-10:</span>
          <span class="text-gray-600">Cravings decreasing. Energy returning. Dreams very vivid.</span>
        </div>
        ${days > 10 ? `
        <div class="flex items-start gap-2 p-2 bg-white rounded">
          <span class="font-semibold">Day 11-${days}:</span>
          <span class="text-gray-600">Tolerance significantly reset. Feeling more clear-headed.</span>
        </div>
        ` : ''}
      </div>
    `;
  }

  // ===== PRODUCT TYPE RECOMMENDER =====
  window.recommendProductType = function() {
    const discretion = document.getElementById('discretion').value;
    const convenience = document.getElementById('convenience').value;
    const budget = document.getElementById('budget-pref').value;
    const lungHealth = document.getElementById('lung-health').value;

    const scores = {
      flower: 0,
      edibles: 0,
      vapes: 0,
      concentrates: 0,
      tinctures: 0
    };

    // Scoring algorithm
    if (discretion === 'very') {
      scores.edibles += 3;
      scores.vapes += 2;
      scores.tinctures += 3;
      scores.flower -= 2;
    }

    if (convenience === 'high') {
      scores.vapes += 3;
      scores.edibles += 2;
      scores.flower -= 1;
    }

    if (budget === 'budget') {
      scores.flower += 3;
      scores.edibles += 1;
      scores.concentrates -= 1;
    } else if (budget === 'premium') {
      scores.concentrates += 2;
      scores.vapes += 1;
    }

    if (lungHealth === 'yes') {
      scores.edibles += 3;
      scores.tinctures += 2;
      scores.flower -= 3;
      scores.vapes -= 1;
    } else {
      scores.flower += 1;
      scores.vapes += 1;
    }

    // Sort by score
    const sorted = Object.entries(scores).sort((a, b) => b[1] - a[1]);

    const productDescriptions = {
      flower: { icon: '🌸', pros: ['Most affordable', 'Full entourage effect', 'Wide strain selection'], cons: ['Requires rolling/grinding', 'Strong odor', 'Smoke inhalation'], cost: '$$' },
      edibles: { icon: '🍪', pros: ['Very discreet', 'Long-lasting effects', 'No smoking', 'Precise dosing'], cons: ['Delayed onset (1-2 hours)', 'Easy to overconsume', 'Effects last very long'], cost: '$$$' },
      vapes: { icon: '💨', pros: ['Very convenient', 'Discreet odor', 'Precise dosing', 'Portable'], cons: ['Battery dependency', 'Quality varies', 'Can be expensive'], cost: '$$$' },
      concentrates: { icon: '💎', pros: ['Very potent', 'Efficient', 'Great flavor', 'Longer-lasting'], cons: ['Requires equipment', 'Higher tolerance buildup', 'Expensive'], cost: '$$$$' },
      tinctures: { icon: '💧', pros: ['Extremely discreet', 'Precise dosing', 'Fast-acting (sublingual)', 'No equipment needed'], cons: ['Can taste bad', 'More expensive', 'Less variety'], cost: '$$$' }
    };

    const resultsHTML = sorted.slice(0, 3).map(([product, score], index) => {
      const info = productDescriptions[product];
      return `
        <div class="p-5 bg-white rounded-lg border-2 ${index === 0 ? 'border-orange-400 shadow-lg' : 'border-gray-200'}">
          <div class="flex items-center gap-3 mb-3">
            <div class="text-4xl">${info.icon}</div>
            <div>
              <div class="font-bold text-xl capitalize">${product}</div>
              <div class="text-sm text-gray-500">${index === 0 ? '🏆 Best Match' : index === 1 ? '🥈 Second Best' : '🥉 Also Good'} - Match Score: ${score}/10</div>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4 mb-3">
            <div>
              <div class="text-xs font-semibold text-green-700 mb-1">Pros:</div>
              <ul class="text-xs space-y-0.5">
                ${info.pros.map(pro => `<li class="flex items-start gap-1"><span class="text-green-600">✓</span>${pro}</li>`).join('')}
              </ul>
            </div>
            <div>
              <div class="text-xs font-semibold text-red-700 mb-1">Cons:</div>
              <ul class="text-xs space-y-0.5">
                ${info.cons.map(con => `<li class="flex items-start gap-1"><span class="text-red-600">✗</span>${con}</li>`).join('')}
              </ul>
            </div>
          </div>
          <div class="flex items-center justify-between text-xs pt-2 border-t">
            <span>Typical Cost: <strong>${info.cost}</strong></span>
            <a href="/" class="text-blue-600 hover:underline">Find dispensaries →</a>
          </div>
        </div>
      `;
    }).join('');

    document.getElementById('product-recommendations').innerHTML = resultsHTML;
    document.getElementById('product-type-result').classList.remove('hidden');
  };

  // ===== EFFECTS TIMER =====
  window.startEffectsTimer = function() {
    const productType = document.getElementById('timer-product').value;
    const dose = parseInt(document.getElementById('timer-dose').value);

    sessionStartTime = new Date();

    const durations = {
      'smoking': 180, // 3 hours in minutes
      'edible': 480,  // 8 hours
      'tincture': 300, // 5 hours
      'topical': 180   // 3 hours
    };

    const totalDuration = durations[productType];

    document.getElementById('effects-timer-display').classList.remove('hidden');
    document.getElementById('timer-start-btn').disabled = true;
    document.getElementById('timer-start-btn').classList.add('opacity-50');

    // Update timer every second
    timerInterval = setInterval(() => {
      const elapsed = Math.floor((new Date() - sessionStartTime) / 1000 / 60); // minutes
      const hours = Math.floor(elapsed / 60);
      const minutes = elapsed % 60;

      document.getElementById('timer-display').textContent =
        String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');

      // Update status
      let status = '';
      const percentage = (elapsed / totalDuration) * 100;

      if (percentage < 10) status = '🟢 Effects beginning...';
      else if (percentage < 40) status = '📈 Building to peak...';
      else if (percentage < 70) status = '🔥 Peak effects!';
      else if (percentage < 90) status = '📉 Effects declining...';
      else status = '🌙 Wearing off...';

      document.getElementById('timer-status').textContent = status;

      // Milestones
      updateTimerMilestones(elapsed, productType);

      // Auto-stop after duration
      if (elapsed >= totalDuration) {
        stopEffectsTimer();
      }
    }, 1000);
  };

  function updateTimerMilestones(elapsed, product) {
    const milestones = [];

    if (product === 'edible') {
      if (elapsed >= 30) milestones.push('✓ Onset period (30+ min)');
      if (elapsed >= 90) milestones.push('✓ Effects should be noticeable');
      if (elapsed >= 150) milestones.push('✓ Approaching peak (2.5 hours)');
      if (elapsed >= 300) milestones.push('⚠️ Past peak, do not take more');
    } else {
      if (elapsed >= 5) milestones.push('✓ Effects started');
      if (elapsed >= 15) milestones.push('✓ Peak effects reached');
      if (elapsed >= 90) milestones.push('⚠️ Effects declining');
    }

    document.getElementById('timer-milestones').innerHTML =
      milestones.map(m => `<div class="text-sm">${m}</div>`).join('');
  }

  window.stopEffectsTimer = function() {
    if (timerInterval) {
      clearInterval(timerInterval);
      timerInterval = null;
    }

    document.getElementById('timer-start-btn').disabled = false;
    document.getElementById('timer-start-btn').classList.remove('opacity-50');
    document.getElementById('timer-status').textContent = '✅ Session ended';
  };

  // ===== HELPER FUNCTIONS =====
  function destroyAllCharts() {
    Object.values(charts).forEach(chart => {
      if (chart) chart.destroy();
    });
    charts = {};
  }

  // Initialize
  document.addEventListener('DOMContentLoaded', () => {
    console.log('✅ Cannabis Tools Enhanced - All systems ready');
  });

})();
