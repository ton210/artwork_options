// Cannabis Tools & Calculators JavaScript
(function() {
  'use strict';

  // Dosage Calculator
  window.calculateDosage = function() {
    const experience = document.getElementById('experience').value;
    const weight = parseInt(document.getElementById('weight').value);
    const effect = document.getElementById('effect').value;

    const baseDosages = {
      beginner: { mild: 2.5, moderate: 5, strong: 7.5 },
      occasional: { mild: 5, moderate: 10, strong: 15 },
      regular: { mild: 10, moderate: 20, strong: 30 },
      experienced: { mild: 20, moderate: 40, strong: 60 }
    };

    let dosage = baseDosages[experience][effect];

    // Adjust for weight
    if (weight < 120) dosage *= 0.8;
    if (weight > 200) dosage *= 1.2;

    dosage = Math.round(dosage * 10) / 10;

    const dosageResult = document.getElementById('dosage-result');
    const dosageAmount = document.getElementById('dosage-amount');
    const dosageAdvice = document.getElementById('dosage-advice');

    if (dosageAmount) {
      dosageAmount.textContent = dosage + ' mg THC';
    }

    if (dosageAdvice) {
      let advice = '';
      if (experience === 'beginner') {
        advice = '⚠️ Start with half this dose and wait 2 hours before taking more. You can always take more, but you cannot take less!';
      } else if (effect === 'strong') {
        advice = '⚠️ This is a high dose. Make sure you are in a comfortable environment with no responsibilities.';
      } else {
        advice = '✅ This dosage should provide a pleasant experience. Wait 1-2 hours for full effects.';
      }
      dosageAdvice.textContent = advice;
    }

    if (dosageResult) {
      dosageResult.classList.remove('hidden');
    }
  };

  // Strain Finder
  window.findStrain = function() {
    const timeOfUse = document.getElementById('time-of-use').value;
    const goal = document.getElementById('goal').value;
    const intensity = document.getElementById('intensity').value;

    const recommendations = {
      'morning-energy': {
        type: 'Sativa',
        strains: ['Sour Diesel', 'Green Crack', 'Durban Poison'],
        desc: 'Energizing sativas perfect for daytime productivity'
      },
      'morning-creative': {
        type: 'Sativa',
        strains: ['Jack Herer', 'Super Lemon Haze', 'Tangie'],
        desc: 'Uplifting strains that enhance creativity'
      },
      'evening-relax': {
        type: 'Indica',
        strains: ['Granddaddy Purple', 'Bubba Kush', 'Purple Punch'],
        desc: 'Relaxing indicas perfect for unwinding'
      },
      'evening-sleep': {
        type: 'Indica',
        strains: ['Northern Lights', 'Gods Gift', 'Tahoe OG'],
        desc: 'Sedating strains for deep sleep'
      },
      'anytime-pain': {
        type: 'Hybrid',
        strains: ['Blue Dream', 'ACDC', 'Harlequin'],
        desc: 'Balanced strains for pain relief without sedation'
      },
      'anytime-social': {
        type: 'Hybrid',
        strains: ['Wedding Cake', 'Gelato', 'Girl Scout Cookies'],
        desc: 'Social, uplifting hybrids perfect for gatherings'
      }
    };

    let key = '';
    const timePrefix = (timeOfUse === 'morning' || timeOfUse === 'afternoon') ? 'morning' : 'evening';
    const goalMap = {
      'energy': 'energy',
      'creative': 'creative',
      'relax': 'relax',
      'sleep': 'sleep',
      'pain': 'pain',
      'social': 'social'
    };

    if ((timeOfUse === 'morning' || timeOfUse === 'afternoon') && (goal === 'energy' || goal === 'creative')) {
      key = 'morning-' + goal;
    } else if (timeOfUse === 'evening' && (goal === 'relax' || goal === 'sleep')) {
      key = 'evening-' + goal;
    } else {
      key = 'anytime-' + (goalMap[goal] || 'social');
    }

    const rec = recommendations[key] || recommendations['anytime-social'];

    const strainRec = document.getElementById('strain-recommendation');
    if (strainRec) {
      const strainsList = rec.strains.map(s =>
        '<li class="flex items-center gap-2"><span class="text-green-600">✓</span><span class="font-medium">' + s + '</span></li>'
      ).join('');

      strainRec.innerHTML = '<div class="mb-3"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">' + rec.type + '</span></div>' +
        '<div class="font-bold text-xl mb-2">Try These Strains:</div>' +
        '<ul class="space-y-2 mb-3">' + strainsList + '</ul>' +
        '<p class="text-sm text-gray-600">' + rec.desc + '</p>' +
        '<a href="/" class="inline-block mt-4 text-blue-600 hover:underline font-medium">Find dispensaries with these strains →</a>';
    }

    const strainResult = document.getElementById('strain-result');
    if (strainResult) {
      strainResult.classList.remove('hidden');
    }
  };

  // Savings Calculator
  window.calculateSavings = function() {
    const weeklyBudget = parseFloat(document.getElementById('weekly-budget').value);
    const discountPercent = parseFloat(document.getElementById('discount-percent').value);
    const loyaltyPercent = parseFloat(document.getElementById('loyalty-percent').value);

    const annualSpending = weeklyBudget * 52;
    const discountSavings = annualSpending * (discountPercent / 100);
    const loyaltySavings = annualSpending * (loyaltyPercent / 100);
    const totalSavings = discountSavings + loyaltySavings;

    const annualSpendingEl = document.getElementById('annual-spending');
    const annualSavingsEl = document.getElementById('annual-savings');
    const savingsBreakdownEl = document.getElementById('savings-breakdown');

    if (annualSpendingEl) {
      annualSpendingEl.textContent = '$' + annualSpending.toLocaleString();
    }

    if (annualSavingsEl) {
      annualSavingsEl.textContent = '$' + Math.round(totalSavings).toLocaleString();
    }

    if (savingsBreakdownEl) {
      savingsBreakdownEl.innerHTML =
        '💚 Discounts: $' + Math.round(discountSavings).toLocaleString() + '<br>' +
        '🎁 Loyalty Points: $' + Math.round(loyaltySavings).toLocaleString() + '<br>' +
        '<strong>That is ' + Math.round((totalSavings/annualSpending)*100) + '% savings!</strong>';
    }

    const savingsResult = document.getElementById('savings-result');
    if (savingsResult) {
      savingsResult.classList.remove('hidden');
    }
  };

  // Terpene Matcher
  window.matchTerpenes = function() {
    const terpeneMap = {
      relaxation: [
        { name: 'Linalool', desc: 'Lavender scent, calming and anti-anxiety' },
        { name: 'Myrcene', desc: 'Earthy aroma, sedating and muscle relaxant' }
      ],
      energy: [
        { name: 'Limonene', desc: 'Citrus scent, uplifting and mood-enhancing' },
        { name: 'Pinene', desc: 'Pine scent, alertness and memory retention' }
      ],
      pain: [
        { name: 'Caryophyllene', desc: 'Peppery, anti-inflammatory and pain relief' },
        { name: 'Myrcene', desc: 'Strong pain-relieving properties' }
      ],
      sleep: [
        { name: 'Myrcene', desc: 'Sedating, promotes deep sleep' },
        { name: 'Linalool', desc: 'Calming, reduces anxiety for better sleep' }
      ],
      mood: [
        { name: 'Limonene', desc: 'Mood elevation and stress relief' },
        { name: 'Pinene', desc: 'Mental clarity and alertness' }
      ],
      appetite: [
        { name: 'Humulene', desc: 'Appetite suppressant, woody aroma' },
        { name: 'THCV', desc: 'Energizing cannabinoid, reduces hunger' }
      ]
    };

    const selected = Array.from(document.querySelectorAll('.terpene-effect:checked')).map(cb => cb.value);

    if (selected.length === 0) {
      alert('Please select at least one desired effect');
      return;
    }

    const terpenes = new Map();
    selected.forEach(effect => {
      const terps = terpeneMap[effect] || [];
      terps.forEach(terp => {
        if (!terpenes.has(terp.name)) {
          terpenes.set(terp.name, terp);
        }
      });
    });

    const terpeneListEl = document.getElementById('terpene-list');
    if (terpeneListEl) {
      const terpeneHTML = Array.from(terpenes.values()).map(terp =>
        '<div class="flex items-start gap-3 p-3 bg-white rounded border">' +
          '<div class="text-2xl">🌿</div>' +
          '<div>' +
            '<div class="font-bold text-lg">' + terp.name + '</div>' +
            '<div class="text-sm text-gray-600">' + terp.desc + '</div>' +
          '</div>' +
        '</div>'
      ).join('');

      terpeneListEl.innerHTML = terpeneHTML;
    }

    const terpeneResult = document.getElementById('terpene-result');
    if (terpeneResult) {
      terpeneResult.classList.remove('hidden');
    }
  };

})();
