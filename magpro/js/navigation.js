/**
 * MagPro Navigation
 *
 * Mobile menu toggle, dropdown keyboard navigation, sticky header.
 */

(function () {
	'use strict';

	// Mobile menu toggle.
	var menuToggle = document.getElementById('magpro-menu-toggle');
	var primaryMenu = document.getElementById('primary-menu');

	if (menuToggle && primaryMenu) {
		menuToggle.addEventListener('click', function () {
			var expanded = this.getAttribute('aria-expanded') === 'true';
			this.setAttribute('aria-expanded', String(!expanded));
			this.setAttribute('aria-label', expanded ? 'Open menu' : 'Close menu');
			primaryMenu.classList.toggle('toggled');

			// Toggle hamburger animation.
			this.querySelector('.magpro-hamburger').classList.toggle('is-active');
		});

		// Close menu when clicking outside.
		document.addEventListener('click', function (e) {
			if (!menuToggle.contains(e.target) && !primaryMenu.contains(e.target)) {
				menuToggle.setAttribute('aria-expanded', 'false');
				primaryMenu.classList.remove('toggled');
			}
		});

		// Close menu on Escape.
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && primaryMenu.classList.contains('toggled')) {
				menuToggle.setAttribute('aria-expanded', 'false');
				primaryMenu.classList.remove('toggled');
				menuToggle.focus();
			}
		});
	}

	// Dropdown keyboard navigation.
	var menuItems = document.querySelectorAll('.magpro-primary-menu > li');
	menuItems.forEach(function (item) {
		var link = item.querySelector('a');
		var submenu = item.querySelector('.sub-menu');

		if (!submenu) return;

		// Show submenu on focus within.
		item.addEventListener('focusin', function () {
			submenu.style.display = 'block';
		});

		item.addEventListener('focusout', function (e) {
			if (!item.contains(e.relatedTarget)) {
				submenu.style.display = '';
			}
		});

		// Arrow key navigation.
		link.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowDown') {
				e.preventDefault();
				var firstLink = submenu.querySelector('a');
				if (firstLink) firstLink.focus();
			}
		});

		var subLinks = submenu.querySelectorAll('a');
		subLinks.forEach(function (subLink, idx) {
			subLink.addEventListener('keydown', function (e) {
				if (e.key === 'ArrowDown') {
					e.preventDefault();
					if (subLinks[idx + 1]) subLinks[idx + 1].focus();
				} else if (e.key === 'ArrowUp') {
					e.preventDefault();
					if (idx > 0) {
						subLinks[idx - 1].focus();
					} else {
						link.focus();
					}
				} else if (e.key === 'Escape') {
					submenu.style.display = '';
					link.focus();
				}
			});
		});
	});

	// Search toggle.
	var searchToggle = document.getElementById('magpro-search-toggle');
	var searchOverlay = document.getElementById('magpro-search-overlay');

	if (searchToggle && searchOverlay) {
		var searchClose = searchOverlay.querySelector('.magpro-search-close');
		var searchField = searchOverlay.querySelector('.search-field');

		searchToggle.addEventListener('click', function () {
			var expanded = this.getAttribute('aria-expanded') === 'true';
			this.setAttribute('aria-expanded', String(!expanded));

			if (expanded) {
				searchOverlay.hidden = true;
				document.body.style.overflow = '';
			} else {
				searchOverlay.hidden = false;
				document.body.style.overflow = 'hidden';
				if (searchField) searchField.focus();
			}
		});

		if (searchClose) {
			searchClose.addEventListener('click', function () {
				searchOverlay.hidden = true;
				document.body.style.overflow = '';
				searchToggle.setAttribute('aria-expanded', 'false');
				searchToggle.focus();
			});
		}

		// Close on Escape.
		searchOverlay.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				searchOverlay.hidden = true;
				document.body.style.overflow = '';
				searchToggle.setAttribute('aria-expanded', 'false');
				searchToggle.focus();
			}
		});

		// Close on background click.
		searchOverlay.addEventListener('click', function (e) {
			if (e.target === searchOverlay) {
				searchOverlay.hidden = true;
				document.body.style.overflow = '';
				searchToggle.setAttribute('aria-expanded', 'false');
			}
		});
	}

	// Sticky header shadow on scroll.
	var mainNav = document.querySelector('.magpro-main-nav');
	if (mainNav && document.body.classList.contains('has-sticky-header')) {
		var scrollHandler = function () {
			if (window.scrollY > 10) {
				mainNav.classList.add('is-scrolled');
			} else {
				mainNav.classList.remove('is-scrolled');
			}
		};

		window.addEventListener('scroll', scrollHandler, { passive: true });
		scrollHandler();
	}
})();
