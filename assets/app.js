import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

document.querySelectorAll('.like-btn, .dislike-btn').forEach(btn => {
	btn.addEventListener('click', () => {
		btn.classList.add('clicked');
		setTimeout(() => btn.classList.remove('clicked'), 300);
	});
});

// Animated background: create gradient orbs and particles
(function() {
	const container = document.querySelector('.animated-bg');
	if (!container) return;

	// create floating particles
	const particleCount = 36;
	for (let i = 0; i < particleCount; i++) {
		const p = document.createElement('div');
		p.className = 'particle';
		const size = Math.round(Math.random() * 40) + 8;
		p.style.width = size + 'px';
		p.style.height = size + 'px';
		p.style.left = Math.random() * 100 + '%';
		p.style.top = Math.random() * 100 + '%';
		p.style.opacity = (Math.random() * 0.5 + 0.05).toFixed(2);
		p.style.animationDuration = (12 + Math.random() * 16) + 's';
		p.style.animationDelay = (Math.random() * 8) + 's';
		container.appendChild(p);
	}

	// small performance note: particles are CSS-animated; keep count modest on mobile
})();
