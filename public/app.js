// Newsletter form handler
function handleNewsletter(event) {
    event.preventDefault();
    const email = event.target.querySelector('input[type="email"]').value;
    alert('Thank you for subscribing with: ' + email);
    event.target.reset();
}

// Category Filter Function (fallback - also defined inline in homepage)
function filterArticles(category, button) {
    // Update active button
    document.querySelectorAll('.category-filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    if (button) {
        button.classList.add('active');
    }

    // Filter articles
    const articles = document.querySelectorAll('.blog-card-premium');
    articles.forEach(article => {
        const articleCategory = article.dataset.category;

        if (category === 'all') {
            article.style.display = 'block';
            article.style.animation = 'fadeIn 0.3s ease';
        } else if (articleCategory && articleCategory.toLowerCase().includes(category.toLowerCase())) {
            article.style.display = 'block';
            article.style.animation = 'fadeIn 0.3s ease';
        } else {
            article.style.display = 'none';
        }
    });
}

// Scroll animations for blog cards
document.addEventListener('DOMContentLoaded', function () {
    // Add scroll animation observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });

    // Observe all blog cards and article cards
    document.querySelectorAll('.blog-card-premium, .article-card-modern').forEach(card => {
        observer.observe(card);
    });

    // Mac OS Style Smooth Typewriter - Sequential
    const typeWriterElements = document.querySelectorAll('.typewriter-text');
    const typeWriterDelayElements = document.querySelectorAll('.typewriter-text-delay');

    function typeWriter(element, speed = 60) {
        return new Promise(resolve => {
            if (!element) {
                resolve();
                return;
            }

            const text = element.textContent.trim();
            element.textContent = ''; // Clear
            element.style.width = 'auto';
            element.style.borderRight = '3px solid var(--accent-color)'; // Show cursor
            element.style.opacity = '1';

            let i = 0;
            function type() {
                if (i < text.length) {
                    element.textContent += text.charAt(i);
                    i++;
                    // Slight random variance for "human" feel if desired, but "Mac OS" usually means smooth machine
                    setTimeout(type, speed);
                } else {
                    // Finished
                    setTimeout(() => {
                        element.style.borderRight = '3px solid transparent'; // Hide cursor
                        resolve();
                    }, 500); // Keep cursor blinking briefly before hiding? Or hide immediately? User said "remove after finishing"
                }
            }
            type();
        });
    }

    // Execute Sequentially
    async function runAnimations() {
        // Main Title
        for (const el of typeWriterElements) {
            await typeWriter(el, 80); // Title slightly slower/bolder
        }

        // Subtitle (Subtitle starts after Title finishes)
        for (const el of typeWriterDelayElements) {
            await typeWriter(el, 50); // Subtitle faster
        }
    }

    // Start
    setTimeout(runAnimations, 500);

    /* ========== INTERACTIVE JS FEATURES ========== */

    // 1. Reading Progress Bar
    const progressBar = document.getElementById('reading-progress-bar');

    function updateProgressBar() {
        if (!progressBar) return;

        const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight || document.body.scrollHeight;
        const clientHeight = document.documentElement.clientHeight || document.body.clientHeight;

        const scrollRange = scrollHeight - clientHeight;
        if (scrollRange <= 0) {
            progressBar.style.width = '0%';
            return;
        }

        const scrolled = (scrollTop / scrollRange) * 100;
        progressBar.style.width = scrolled + '%';
    }

    // 2. Scroll To Top Button
    const scrollTopBtn = document.getElementById('scroll-top-btn');

    function toggleScrollTopBtn() {
        if (!scrollTopBtn) return;

        if (window.scrollY > 300) {
            scrollTopBtn.classList.add('visible');
        } else {
            scrollTopBtn.classList.remove('visible');
        }
    }

    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    if (scrollTopBtn) {
        scrollTopBtn.addEventListener('click', scrollToTop);
    }

    // Global Scroll Listener
    window.addEventListener('scroll', () => {
        requestAnimationFrame(() => {
            updateProgressBar();
            toggleScrollTopBtn();
        });
    });

    // 3. AJAX Likes/Dislikes
    const engagementBtns = document.querySelectorAll('.js-engagement-btn');

    engagementBtns.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';

            const url = btn.href;
            const type = btn.dataset.type;

            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (response.ok) {
                    const data = await response.json();

                    // Update all buttons in the section
                    const parent = btn.closest('.engagement-buttons');
                    const likeBtn = parent.querySelector('.btn-like');
                    const dislikeBtn = parent.querySelector('.btn-dislike');

                    likeBtn.querySelector('.engagement-count').textContent = data.totalLikes;
                    dislikeBtn.querySelector('.engagement-count').textContent = data.totalDislikes;

                    // Toggle active classes
                    if (type === 'like') {
                        likeBtn.classList.toggle('active');
                        dislikeBtn.classList.remove('active');
                    } else {
                        dislikeBtn.classList.toggle('active');
                        likeBtn.classList.remove('active');
                    }
                } else if (response.status === 403) {
                    alert('Please log in to like/dislike.');
                } else {
                    console.error('Action failed');
                }
            } catch (error) {
                console.error('Like failed:', error);
            } finally {
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
            }
        });
    });

    // 4. Share / Copy Link
    const shareBtn = document.querySelector('.js-share-btn');
    if (shareBtn) {
        shareBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(window.location.href).then(() => {
                const originalLabel = shareBtn.querySelector('.engagement-label').textContent;
                shareBtn.querySelector('.engagement-label').textContent = 'Copied!';
                shareBtn.style.color = '#22c55e';

                setTimeout(() => {
                    shareBtn.querySelector('.engagement-label').textContent = originalLabel;
                    shareBtn.style.color = '';
                }, 2000);
            });
        });
    }
});
