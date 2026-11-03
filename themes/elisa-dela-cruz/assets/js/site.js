/**
 * Elisa Dela Cruz theme: progressive enhancements. Every page is complete without this file.
 * See docs/redesign/04-build-spec.md §6.
 */
(function () {
	'use strict';

	document.documentElement.classList.add('js');

	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
	var $ = function (sel, root) { return (root || document).querySelector(sel); };
	var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
	var onMedia = function (mq, fn) {
		if (mq.addEventListener) { mq.addEventListener('change', fn); } else if (mq.addListener) { mq.addListener(fn); }
	};

	/* ---------- GA4 ---------- */
	function track(name, params) {
		if (typeof window.gtag !== 'function') { return; }
		var p = params || {};
		p.page_path = window.location.pathname;
		window.gtag('event', name, p);
	}

	document.addEventListener('click', function (e) {
		var el = e.target.closest('[data-ga-event], .js-open-contact, a.btn, button.btn');
		if (!el) { return; }
		var name = el.getAttribute('data-ga-event') || (el.classList.contains('js-open-contact') ? 'cta_start_project' : 'cta_click');
		track(name, {
			button_label: (el.getAttribute('data-ga-label') || el.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 80),
			link_url: el.getAttribute('href') || ''
		});
	}, true);

	document.addEventListener('wpcf7mailsent', function () {
		track('contact_form_submit', { form: 'contact' });
	});

	/* ---------- Nav ---------- */
	var nav = $('.nav');
	var menuBtn = $('.menu-btn');
	var menu = $('#menu');

	if (nav) {
		var onScroll = function () { nav.classList.toggle('scrolled', window.scrollY > 30); };
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	function setMenu(open, returnFocus) {
		if (!nav || !menuBtn) { return; }
		nav.classList.toggle('open', open);
		menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
		menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
		if (!open && returnFocus) { menuBtn.focus(); }
	}

	if (nav && menuBtn && menu) {
		menuBtn.addEventListener('click', function () { setMenu(!nav.classList.contains('open')); });
		menu.addEventListener('click', function (e) {
			if (e.target.closest('a')) { setMenu(false); }
		});
		var wide = window.matchMedia('(min-width: 861px)');
		onMedia(wide, function () { if (wide.matches) { setMenu(false); } });
	}

	/* ---------- Reveal ---------- */
	var reveals = $$('[data-reveal]');
	if (reveals.length) {
		if (reduceMotion.matches || !('IntersectionObserver' in window)) {
			reveals.forEach(function (el) { el.classList.add('in'); });
		} else {
			var io = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					if (!entry.isIntersecting) { return; }
					entry.target.classList.add('in');
					io.unobserve(entry.target);
				});
			}, { threshold: 0.12 });
			reveals.forEach(function (el) { io.observe(el); });
		}
	}

	/* ---------- Work filter (homepage; the archive filters server-side) ---------- */
	var filters = $('.filters');
	var grid = $('.work-grid');
	if (filters && grid && !document.body.classList.contains('post-type-archive-project')) {
		var filterLinks = $$('a[data-type]', filters);
		var filterStatus = $('#filter-status');
		var matches = function (card, type) {
			if (card.classList.contains('more-tile')) { return type === 'all'; }
			return type === 'all' || (' ' + (card.getAttribute('data-type') || '') + ' ').indexOf(' ' + type + ' ') !== -1;
		};
		var applyFilter = function (type, announce) {
			var link = filterLinks.filter(function (a) { return a.getAttribute('data-type') === type; })[0];
			if (!link) { return; }
			var cards = $$('.card', grid);
			var count = cards.filter(function (card) { return !card.classList.contains('more-tile') && matches(card, type); }).length;
			var run = function () {
				cards.forEach(function (card) { card.hidden = !matches(card, type); });
				filterLinks.forEach(function (a) {
					if (a === link) { a.setAttribute('aria-current', 'true'); } else { a.removeAttribute('aria-current'); }
				});
			};
			if (document.startViewTransition && !reduceMotion.matches) { document.startViewTransition(run); } else { run(); }
			if (announce) {
				var url = new URL(window.location.href);
				if (type === 'all') { url.searchParams.delete('type'); } else { url.searchParams.set('type', type); }
				history.replaceState(null, '', url.pathname + url.search + url.hash);
				if (filterStatus) {
					filterStatus.textContent = type === 'all'
						? 'Showing all ' + count + ' projects'
						: 'Showing ' + count + ' ' + type + ' project' + (count === 1 ? '' : 's');
				}
				track('filter_change', { type: type });
			}
		};
		filters.addEventListener('click', function (e) {
			var a = e.target.closest('a[data-type]');
			if (!a) { return; }
			e.preventDefault();
			applyFilter(a.getAttribute('data-type'), true);
		});
		var initial = new URLSearchParams(window.location.search).get('type');
		if (initial && initial !== 'all') { applyFilter(initial, false); }
	}

	/* ---------- Behind panels (touch / small screens) ---------- */
	function setBehind(card, open, moveFocus) {
		var toggle = card && $('.behind-toggle', card);
		if (!toggle) { return; }
		card.classList.toggle('show-behind', open);
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		if (!moveFocus) { return; }
		var close = $('.behind-close', card);
		if (open && close) { close.focus(); } else if (!open) { toggle.focus(); }
	}

	document.addEventListener('click', function (e) {
		var toggle = e.target.closest('.behind-toggle');
		if (toggle) {
			var card = toggle.closest('.card');
			setBehind(card, !card.classList.contains('show-behind'), true);
			return;
		}
		var close = e.target.closest('.behind-close');
		if (close) { setBehind(close.closest('.card'), false, true); }
	});

	/* ---------- Career timeline ---------- */
	$$('.log[data-visible]').forEach(function (log) {
		var visible = parseInt(log.getAttribute('data-visible'), 10);
		var more = log.nextElementSibling;
		if (!more || !more.classList.contains('log-more') || !visible || $$(':scope > li', log).length <= visible) { return; }
		log.classList.add('collapsed');
		more.hidden = false;
		more.addEventListener('click', function () {
			var collapsed = log.classList.toggle('collapsed');
			more.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
			var label = $('span', more);
			if (label) { label.textContent = label.textContent.replace(collapsed ? /^\(hide/ : /^\(show/, collapsed ? '(show' : '(hide'); }
			if (!collapsed) {
				var first = $('li.older summary', log);
				if (first) { first.focus(); }
			}
		});
	});

	/* ---------- Copy email ---------- */
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-copy]');
		if (!btn) { return; }
		var value = btn.getAttribute('data-copy');
		if (!value) { return; }
		e.preventDefault();
		if (!navigator.clipboard) {
			window.location.href = 'mailto:' + value;
			return;
		}
		var status = $('#copy-status');
		navigator.clipboard.writeText(value).then(function () {
			if (!btn.hasAttribute('data-label')) { btn.setAttribute('data-label', btn.innerHTML); }
			btn.textContent = 'Copied ✓';
			if (status) { status.textContent = 'Email address copied'; }
			window.setTimeout(function () {
				btn.innerHTML = btn.getAttribute('data-label');
				if (status) { status.textContent = ''; }
			}, 1600);
		}).catch(function () {
			window.location.href = 'mailto:' + value;
		});
	});

	/* ---------- Contact modal ---------- */
	var modal = $('#contactModal');
	var lastFocus = null;

	function modalIsOpen() { return !!modal && modal.classList.contains('open'); }

	function modalFocusables() {
		return $$('a[href], button:not([disabled]), input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])', modal)
			.filter(function (el) { return el.offsetParent !== null && el.getAttribute('tabindex') !== '-1' && !el.closest('[aria-hidden="true"]'); });
	}

	function fillCf7Challenge() {
		if (!$('#edc_js_ts', modal) || !window.fetch) { return; }
		fetch(modal.getAttribute('data-challenge') || '/wp-json/edc/v1/cf7-challenge', { credentials: 'same-origin' })
			.then(function (r) { return r.ok ? r.json() : null; })
			.then(function (data) {
				if (!data || !data.ts || !data.token) { return; }
				$$('input[name="edc_js_ts"]').forEach(function (el) { el.value = String(data.ts); });
				$$('input[name="edc_js_token"]').forEach(function (el) { el.value = String(data.token); });
			})
			.catch(function () {});
	}

	function openModal() {
		lastFocus = document.activeElement;
		setMenu(false);
		fillCf7Challenge();
		modal.classList.add('open');
		document.body.style.overflow = 'hidden';
		var first = modalFocusables()[0];
		if (first) { first.focus(); }
	}

	function closeModal() {
		if (!modalIsOpen()) { return; }
		modal.classList.remove('open');
		document.body.style.overflow = '';
		// The trigger may sit in the now-closed mobile menu; fall back to the menu button.
		var target = lastFocus && lastFocus.offsetParent !== null ? lastFocus : menuBtn;
		if (target && typeof target.focus === 'function') { target.focus(); }
		lastFocus = null;
	}

	if (modal) {
		document.addEventListener('click', function (e) {
			var trigger = e.target.closest('.js-open-contact');
			if (!trigger) { return; }
			e.preventDefault();
			openModal();
		});
		modal.addEventListener('click', function (e) {
			if (e.target === modal || e.target.closest('.close')) { closeModal(); }
		});
	}

	/* ---------- Keyboard: Escape and the modal focus trap ---------- */
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			if (modalIsOpen()) { closeModal(); return; }
			if (nav && nav.classList.contains('open')) { setMenu(false, true); return; }
			var openCard = document.activeElement && document.activeElement.closest && document.activeElement.closest('.card.show-behind');
			if (openCard) { setBehind(openCard, false, true); }
			return;
		}
		if (e.key !== 'Tab' || !modalIsOpen()) { return; }
		var items = modalFocusables();
		if (!items.length) { return; }
		var first = items[0];
		var last = items[items.length - 1];
		if (!modal.contains(document.activeElement)) {
			e.preventDefault();
			first.focus();
		} else if (e.shiftKey && document.activeElement === first) {
			e.preventDefault();
			last.focus();
		} else if (!e.shiftKey && document.activeElement === last) {
			e.preventDefault();
			first.focus();
		}
	});

	/* ---------- Back to top ---------- */
	var toTop = $('#backToTop');
	if (toTop) {
		var toggleTop = function () { toTop.classList.toggle('is-visible', window.scrollY > 320); };
		window.addEventListener('scroll', toggleTop, { passive: true });
		toggleTop();
	}
})();
