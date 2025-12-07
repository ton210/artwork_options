/**
 * Frontend JavaScript for Blog Generator
 * Handles form submission and data collection
 */

let pairCount = 1;

// Add new prompt/response pair
document.getElementById('add-pair-btn').addEventListener('click', () => {
    pairCount++;

    const container = document.getElementById('prompt-response-pairs');
    const newPair = document.createElement('div');
    newPair.className = 'pair-container';
    newPair.dataset.pair = pairCount;

    newPair.innerHTML = `
        <h3>Prompt/Response Pair #${pairCount}</h3>
        <button type="button" class="remove-pair-btn" onclick="removePair(${pairCount})">✕ Remove</button>

        <div class="form-group">
            <label for="prompt-${pairCount}">AI Prompt (what people are searching for)</label>
            <textarea
                id="prompt-${pairCount}"
                name="prompt-${pairCount}"
                rows="3"
                placeholder="e.g., 'What are the best custom grinders for dispensaries?'"
                required
            ></textarea>
        </div>

        <div class="form-group">
            <label for="response-${pairCount}">AI Response (what AI tools are returning)</label>
            <textarea
                id="response-${pairCount}"
                name="response-${pairCount}"
                rows="8"
                placeholder="Paste the AI response you want to improve upon..."
                required
            ></textarea>
        </div>

        <hr>
    `;

    container.appendChild(newPair);
});

// Remove prompt/response pair
function removePair(pairId) {
    const pair = document.querySelector(`[data-pair="${pairId}"]`);
    if (pair) {
        pair.remove();
    }
}

// Handle form submission
document.getElementById('blog-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    // Collect all prompt/response pairs
    const pairs = [];
    const pairContainers = document.querySelectorAll('.pair-container');

    pairContainers.forEach((container) => {
        const pairId = container.dataset.pair;
        const prompt = document.getElementById(`prompt-${pairId}`).value;
        const response = document.getElementById(`response-${pairId}`).value;

        if (prompt && response) {
            pairs.push({ prompt, response });
        }
    });

    // Collect additional data
    const data = {
        pairs: pairs,
        targetKeyword: document.getElementById('target-keyword').value,
        internalLinks: document.getElementById('internal-links').value,
        timestamp: new Date().toISOString()
    };

    // Show status
    showStatus('Saving data for Claude Code analysis...');

    try {
        // Send to server
        const response = await fetch('/api/save-input', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showSuccess(result);
        } else {
            showError(result.error || 'Failed to save data');
        }

    } catch (error) {
        showError(`Error: ${error.message}`);
    }
});

function showStatus(message) {
    const statusDisplay = document.getElementById('status-display');
    const statusMessages = document.getElementById('status-messages');

    statusDisplay.style.display = 'block';
    statusMessages.innerHTML += `<div class="status-message">${message}</div>`;

    // Scroll to status
    statusDisplay.scrollIntoView({ behavior: 'smooth' });
}

function showError(message) {
    const statusMessages = document.getElementById('status-messages');
    statusMessages.innerHTML += `<div class="status-message error">✗ Error: ${message}</div>`;
}

function showSuccess(result) {
    const resultDisplay = document.getElementById('result-display');
    const resultContent = document.getElementById('result-content');

    resultDisplay.style.display = 'block';

    resultContent.innerHTML = `
        <p><strong>✓ Data saved successfully!</strong></p>
        <p>File: <code>${result.filename}</code></p>
        <p>Prompt/Response Pairs: ${result.pairCount}</p>
        <br>
        <p><strong>Next Step:</strong></p>
        <p>In your Claude Code session, run:</p>
        <pre style="background: #2d3748; color: #48bb78; padding: 15px; border-radius: 6px; margin-top: 10px;">
Tell Claude: "Process the blog generator input file and create a blog post"
        </pre>
        <br>
        <p>Claude Code will:</p>
        <ul style="margin-left: 20px; line-height: 1.8;">
            <li>Analyze your prompt/response pairs</li>
            <li>Write original, SEO-optimized blog content</li>
            <li>Generate 3 images with Imagen3</li>
            <li>Select internal links strategically</li>
            <li>Create meta title & description</li>
            <li>Publish to BigCommerce</li>
        </ul>
    `;

    resultDisplay.scrollIntoView({ behavior: 'smooth' });
}
