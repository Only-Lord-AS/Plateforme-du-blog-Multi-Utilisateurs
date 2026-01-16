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
});
