/**
 * MagPro Main JavaScript
 *
 * Back to top, dark mode toggle, breaking news ticker,
 * lazy loading fallback, and smooth scroll.
 */

(function () {
	'use strict';

	// Back to top button.
	var backToTop = document.getElementById('magpro-back-to-top');
	if (backToTop) {
		backToTop.hidden = false;

		window.addEventListener('scroll', function () {
			if (window.scrollY > 400) {
				backToTop.classList.add('visible');
			} else {
				backToTop.classList.remove('visible');
			}
		}, { passive: true });

		backToTop.addEventListener('click', function () {
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	}

	// Dark mode toggle.
	var darkToggle = document.getElementById('magpro-dark-toggle');
	if (darkToggle) {
		var savedTheme = localStorage.getItem('magpro-theme');
		var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

		if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
			document.documentElement.setAttribute('data-theme', 'dark');
		}

		darkToggle.addEventListener('click', function () {
			var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
			if (isDark) {
				document.documentElement.removeAttribute('data-theme');
				localStorage.setItem('magpro-theme', 'light');
			} else {
				document.documentElement.setAttribute('data-theme', 'dark');
				localStorage.setItem('magpro-theme', 'dark');
			}
		});
	}

	// Breaking news ticker.
	var ticker = document.getElementById('magpro-ticker');
	if (ticker) {
		var tickerList = ticker.querySelector('.magpro-ticker-list');
		var items = ticker.querySelectorAll('.magpro-ticker-item');
		var prevBtn = document.querySelector('.magpro-ticker-prev');
		var nextBtn = document.querySelector('.magpro-ticker-next');
		var currentIndex = 0;
		var autoInterval;

		function showTickerItem(index) {
			if (items.length === 0) return;
			currentIndex = ((index % items.length) + items.length) % items.length;
			var offset = items[currentIndex].offsetLeft;
			tickerList.style.transform = 'translateX(-' + offset + 'px)';
		}

		function nextTicker() {
			showTickerItem(currentIndex + 1);
		}

		function prevTicker() {
			showTickerItem(currentIndex - 1);
		}

		function startAutoTicker() {
			autoInterval = setInterval(nextTicker, 4000);
		}

		function stopAutoTicker() {
			clearInterval(autoInterval);
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				stopAutoTicker();
				nextTicker();
				startAutoTicker();
			});
		}

		if (prevBtn) {
			prevBtn.addEventListener('click', function () {
				stopAutoTicker();
				prevTicker();
				startAutoTicker();
			});
		}

		startAutoTicker();

		// Pause on hover.
		ticker.addEventListener('mouseenter', stopAutoTicker);
		ticker.addEventListener('mouseleave', startAutoTicker);
	}

	// IntersectionObserver lazy loading fallback.
	if (!('loading' in HTMLImageElement.prototype)) {
		var lazyImages = document.querySelectorAll('img[loading="lazy"]');

		if ('IntersectionObserver' in window) {
			var imageObserver = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						var img = entry.target;
						if (img.dataset.src) {
							img.src = img.dataset.src;
							img.removeAttribute('data-src');
						}
						imageObserver.unobserve(img);
					}
				});
			}, {
				rootMargin: '200px'
			});

			lazyImages.forEach(function (img) {
				imageObserver.observe(img);
			});
		}
	}

	// Smooth scroll for anchor links.
	document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
		anchor.addEventListener('click', function (e) {
			var targetId = this.getAttribute('href');
			if (targetId === '#') return;

			var target = document.querySelector(targetId);
			if (target) {
				e.preventDefault();
				target.scrollIntoView({ behavior: 'smooth', block: 'start' });

				// Update focus for accessibility.
				target.setAttribute('tabindex', '-1');
				target.focus({ preventScroll: true });
			}
		});
	});

	// External links: add target="_blank" and rel="noopener".
	document.querySelectorAll('.entry-content a[href^="http"]').forEach(function (link) {
		if (link.hostname !== window.location.hostname) {
			link.setAttribute('target', '_blank');
			link.setAttribute('rel', 'noopener noreferrer');
		}
	});
})();
