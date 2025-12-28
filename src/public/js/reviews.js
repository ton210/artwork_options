// Reviews functionality for dispensary pages
(function() {
  // Get dispensary ID from data attribute on reviews section
  const reviewsSection = document.getElementById('reviews-section');
  const dispensaryId = reviewsSection ? reviewsSection.dataset.dispensaryId : null;

  if (!dispensaryId) {
    console.warn('No dispensary ID found for reviews');
    return;
  }

  let currentSort = 'recent';
  let currentOffset = 0;
  const LIMIT = 10;

  // Initialize reviews on page load (only once)
  let initialized = false;
  document.addEventListener('DOMContentLoaded', function() {
    if (!initialized) {
      initialized = true;
      loadReviews();
      setupEventListeners();
    }
  });

  function setupEventListeners() {
    // Sort change
    const sortSelect = document.getElementById('sort-reviews');
    if (sortSelect) {
      sortSelect.addEventListener('change', function() {
        currentSort = this.value;
        currentOffset = 0;
        loadReviews(true);
      });
    }

    // Load more button
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
      loadMoreBtn.addEventListener('click', function() {
        currentOffset += LIMIT;
        loadReviews(false);
      });
    }

    // Review form submission
    const reviewForm = document.getElementById('review-form');
    if (reviewForm) {
      reviewForm.addEventListener('submit', submitReview);
    }

    // Star rating interaction
    setupStarRating();
  }

  async function loadReviews(replace = true) {
    try {
      const response = await fetch(`/api/reviews/dispensary/${dispensaryId}?sortBy=${currentSort}&offset=${currentOffset}&limit=${LIMIT}`);
      const data = await response.json();

      if (replace) {
        displayReviews(data.reviews);
      } else {
        appendReviews(data.reviews);
      }

      updateStats(data.stats);
      updateLoadMoreButton(data.hasMore);
      updateNoReviewsMessage(data.total === 0);
    } catch (error) {
      console.error('Error loading reviews:', error);
    }
  }

  function displayReviews(reviews) {
    const container = document.getElementById('reviews-list');
    if (!container) return;

    if (reviews.length === 0) {
      container.innerHTML = '';
      return;
    }

    container.innerHTML = reviews.map(review => createReviewHTML(review)).join('');
    attachHelpfulListeners();
  }

  function appendReviews(reviews) {
    const container = document.getElementById('reviews-list');
    if (!container) return;

    const reviewsHTML = reviews.map(review => createReviewHTML(review)).join('');
    container.insertAdjacentHTML('beforeend', reviewsHTML);
    attachHelpfulListeners();
  }

  function createReviewHTML(review) {
    const date = new Date(review.created_at).toLocaleDateString();
    const stars = '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating);

    return `
      <div class="review-item border-b border-gray-200 pb-6">
        <div class="flex items-start justify-between mb-2">
          <div>
            <div class="font-semibold text-gray-900">${escapeHtml(review.author_name)}</div>
            <div class="text-yellow-500 text-lg">${stars}</div>
          </div>
          <div class="text-sm text-gray-500">${date}</div>
        </div>
        <p class="text-gray-700 mb-3">${escapeHtml(review.review_text)}</p>
        <div class="flex items-center gap-4 text-sm">
          <button class="helpful-btn flex items-center gap-1 text-gray-600 hover:text-green-600 transition"
                  data-review-id="${review.id}" data-helpful="true">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
            </svg>
            Helpful (${review.helpful_count || 0})
          </button>
          <button class="helpful-btn flex items-center gap-1 text-gray-600 hover:text-red-600 transition"
                  data-review-id="${review.id}" data-helpful="false">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018c.163 0 .326.02.485.06L17 4m-7 10v2a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path>
            </svg>
            (${review.not_helpful_count || 0})
          </button>
        </div>
      </div>
    `;
  }

  function updateStats(stats) {
    const avgRatingEl = document.getElementById('average-rating');
    const totalReviewsEl = document.getElementById('total-reviews');

    if (avgRatingEl) {
      avgRatingEl.textContent = stats.average_rating ? stats.average_rating.toFixed(1) : '-';
    }

    if (totalReviewsEl) {
      totalReviewsEl.textContent = stats.total_reviews || 0;
    }

    // Update rating distribution
    updateRatingDistribution(stats.rating_distribution);
  }

  function updateRatingDistribution(distribution) {
    const container = document.getElementById('rating-distribution');
    if (!container || !distribution) return;

    const total = Object.values(distribution).reduce((a, b) => a + b, 0);

    const html = [5, 4, 3, 2, 1].map(rating => {
      const count = distribution[rating] || 0;
      const percentage = total > 0 ? (count / total * 100).toFixed(0) : 0;

      return `
        <div class="flex items-center gap-2">
          <span class="text-sm w-12">${rating} stars</span>
          <div class="flex-1 bg-gray-200 rounded-full h-2">
            <div class="bg-yellow-400 h-2 rounded-full" style="width: ${percentage}%"></div>
          </div>
          <span class="text-sm w-8 text-right">${count}</span>
        </div>
      `;
    }).join('');

    container.innerHTML = html;
  }

  function updateLoadMoreButton(hasMore) {
    const container = document.getElementById('load-more-container');
    if (container) {
      container.classList.toggle('hidden', !hasMore);
    }
  }

  function updateNoReviewsMessage(show) {
    const message = document.getElementById('no-reviews-message');
    if (message) {
      message.classList.toggle('hidden', !show);
    }
  }

  function attachHelpfulListeners() {
    document.querySelectorAll('.helpful-btn').forEach(btn => {
      btn.addEventListener('click', async function() {
        const reviewId = this.dataset.reviewId;
        const helpful = this.dataset.helpful === 'true';

        try {
          const response = await fetch(`/api/reviews/${reviewId}/helpful`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ helpful })
          });

          if (response.ok) {
            loadReviews();
          } else {
            const data = await response.json();
            alert(data.error || 'Could not record vote');
          }
        } catch (error) {
          console.error('Error marking helpful:', error);
        }
      });
    });
  }

  function setupStarRating() {
    const stars = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('rating-value');

    stars.forEach(star => {
      star.addEventListener('click', function() {
        const rating = this.dataset.rating;
        if (ratingInput) {
          ratingInput.value = rating;
        }

        stars.forEach(s => {
          s.classList.toggle('text-yellow-400', s.dataset.rating <= rating);
          s.classList.toggle('text-gray-300', s.dataset.rating > rating);
        });
      });
    });
  }

  async function submitReview(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = {
      dispensaryId: parseInt(dispensaryId),
      authorName: formData.get('authorName'),
      authorEmail: formData.get('authorEmail'),
      rating: parseInt(formData.get('rating')),
      reviewText: formData.get('reviewText')
    };

    try {
      const response = await fetch('/api/reviews/submit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (response.ok) {
        alert('Review submitted successfully!');
        e.target.reset();
        loadReviews(true);
      } else {
        alert(result.error || 'Failed to submit review');
      }
    } catch (error) {
      console.error('Error submitting review:', error);
      alert('Failed to submit review');
    }
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
})();
