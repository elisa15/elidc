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

	/* ---------- Motion extras (skipped when reduced motion is on) ---------- */
	if (!reduceMotion.matches) {
		// Bridge statement: one line at a time.
		$$('.bridge p').forEach(function (p) {
			var parts = p.innerHTML.split(/<br\s*\/?>/i);
			if (parts.length < 2) { return; }
			p.innerHTML = parts.map(function (html, i) { return '<span class="line" style="--d:' + i + '">' + html + '</span>'; }).join('<br>');
			var bridge = p.closest('.bridge');
			if (bridge) { bridge.setAttribute('data-reveal', ''); }
		});

		// Stagger siblings that reveal together (cards, roles, testimonials).
		$$('[data-reveal]').forEach(function (el) {
			var sibs = Array.prototype.filter.call(el.parentNode.children, function (c) { return c.hasAttribute('data-reveal'); });
			var i = sibs.indexOf(el);
			if (sibs.length > 1 && i > 0) { el.style.setProperty('--d', String(Math.min(i, 7))); }
		});

		// Thin scroll progress bar.
		var bar = document.createElement('div');
		bar.className = 'scroll-progress';
		bar.setAttribute('aria-hidden', 'true');
		document.body.appendChild(bar);
		var ticking = false;
		var paint = function () {
			var max = document.documentElement.scrollHeight - window.innerHeight;
			bar.style.transform = 'scaleX(' + (max > 0 ? Math.min(1, window.scrollY / max) : 0) + ')';
			ticking = false;
		};
		window.addEventListener('scroll', function () {
			if (!ticking) { ticking = true; window.requestAnimationFrame(paint); }
		}, { passive: true });
		paint();

		// Stats count up once when they come into view ("13+" counts 0 to 13, then keeps the "+").
		var stats = $$('.stat b');
		if (stats.length && 'IntersectionObserver' in window) {
			var countIo = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					if (!entry.isIntersecting) { return; }
					countIo.unobserve(entry.target);
					var el = entry.target;
					var m = el.textContent.match(/^(\D*)(\d[\d,]*)(.*)$/);
					if (!m) { return; }
					var end = parseInt(m[2].replace(/,/g, ''), 10);
					var start = null;
					var step = function (t) {
						if (start === null) { start = t; }
						var k = Math.min(1, (t - start) / 1200);
						el.textContent = m[1] + Math.round(end * (1 - Math.pow(1 - k, 3))).toLocaleString() + m[3];
						if (k < 1) { window.requestAnimationFrame(step); }
					};
					window.requestAnimationFrame(step);
				});
			}, { threshold: 0.6 });
			stats.forEach(function (el) { countIo.observe(el); });
		}
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

	/* ---------- Projects archive filter (no reload; links still work without JS) ---------- */
	var cmd = $('.cmd-filter');
	var archive = cmd && cmd.closest('.archive-work');
	if (cmd && archive && window.history && window.URLSearchParams) {
		var cmdItems = $$('[data-type]', archive);
		var args = $$('.cmd-arg', cmd);
		var valid = { type: [''], platform: [''] };
		args.forEach(function (a) { valid[a.getAttribute('data-param')].push(a.getAttribute('data-value')); });

		var readState = function () {
			var q = new URLSearchParams(window.location.search);
			var s = {};
			Object.keys(valid).forEach(function (p) {
				var v = (q.get(p) || '').toLowerCase();
				s[p] = valid[p].indexOf(v) > -1 ? v : '';
			});
			return s;
		};
		var cmdMatches = function (el, s) {
			return Object.keys(s).every(function (p) {
				if (!s[p]) { return true; }
				var attr = p === 'type' ? 'data-type' : 'data-platform';
				return (' ' + (el.getAttribute(attr) || '') + ' ').indexOf(' ' + s[p] + ' ') > -1;
			});
		};
		var urlFor = function (s) {
			var q = new URLSearchParams();
			Object.keys(s).forEach(function (p) { if (s[p]) { q.set(p, s[p]); } });
			var qs = q.toString();
			return cmd.getAttribute('data-archive') + (qs ? '?' + qs : '');
		};

		var state = readState();
		var apply = function (s, animate) {
			var shown = 0;
			var fi = 0;
			cmdItems.forEach(function (el) {
				var on = cmdMatches(el, s);
				var was = !el.hidden;
				el.hidden = !on;
				el.classList.remove('filter-in');
				if (on) {
					shown++;
					el.classList.add('in');
					if (animate && !was) {
						el.style.setProperty('--fi', String(Math.min(fi++, 12)));
						void el.offsetWidth;
						el.classList.add('filter-in');
					}
				}
			});
			$$('.tier', archive).forEach(function (t) { t.hidden = !$$('[data-type]:not([hidden])', t).length; });
			var empty = $('.empty-note', archive);
			if (empty) { empty.hidden = shown > 0; }

			args.forEach(function (a) {
				var p = a.getAttribute('data-param');
				var v = a.getAttribute('data-value');
				var next = {};
				Object.keys(s).forEach(function (k) { next[k] = s[k]; });
				next[p] = v;
				if (s[p] === v) { a.setAttribute('aria-current', 'true'); } else { a.removeAttribute('aria-current'); }
				a.setAttribute('href', urlFor(next));
				var count = $('[data-count]', a);
				if (count) {
					var n = cmdItems.filter(function (el) { return cmdMatches(el, next); }).length;
					count.textContent = String(n);
					a.classList.toggle('empty', n === 0);
				}
			});

			var out = $('.cmd-out', cmd);
			$('[data-total]', cmd).textContent = String(shown);
			$('[data-noun]', cmd).textContent = shown === 1 ? 'project' : 'projects';
			if (out && animate) { out.classList.remove('tick'); void out.offsetWidth; out.classList.add('tick'); }
		};

		cmd.addEventListener('click', function (e) {
			var a = e.target.closest('.cmd-arg');
			if (!a || e.metaKey || e.ctrlKey || e.shiftKey || e.button > 0) { return; }
			e.preventDefault();
			state[a.getAttribute('data-param')] = a.getAttribute('data-value');
			apply(state, !reduceMotion.matches);
			window.history.pushState({ edcFilter: true }, '', urlFor(state));
		});
		archive.addEventListener('click', function (e) {
			var reset = e.target.closest('[data-reset]');
			if (!reset) { return; }
			e.preventDefault();
			state = { type: '', platform: '' };
			apply(state, !reduceMotion.matches);
			window.history.pushState({ edcFilter: true }, '', urlFor(state));
		});
		window.addEventListener('popstate', function () {
			state = readState();
			apply(state, !reduceMotion.matches);
		});
		apply(state, false);
	}

	/* ---------- Fixes shop: "Add to order" via the WooCommerce Store API (no reload) ---------- */
	var shop = window.edcShop;
	var orderBar = $('.order-bar');
	if (shop && orderBar && window.fetch) {
		var paintOrder = function (cart) {
			var count = cart.items_count || 0;
			var minor = Math.pow(10, (cart.totals && cart.totals.currency_minor_unit) || 0);
			var total = cart.totals ? parseInt(cart.totals.total_items, 10) / minor : 0;
			$('.order-count', orderBar).textContent = String(count);
			$('.order-noun', orderBar).textContent = count === 1 ? 'item' : 'items';
			$('.order-total', orderBar).textContent = (cart.totals && cart.totals.currency_symbol || shop.currency) + Math.round(total).toLocaleString();
			orderBar.hidden = count === 0;
			if (count && !reduceMotion.matches) { orderBar.classList.remove('bump'); void orderBar.offsetWidth; orderBar.classList.add('bump'); }
		};
		$$('.fix-buy').forEach(function (form) {
			var add = $('.add-to-order', form);
			if (!add) { return; }
			add.hidden = false;
			add.addEventListener('click', function () {
				var qtyField = form.querySelector('[name="quantity"]');
				var label = add.innerHTML;
				add.disabled = true;
				fetch(shop.api + 'cart/add-item', {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/json', 'Nonce': shop.nonce },
					body: JSON.stringify({ id: parseInt(form.getAttribute('data-product'), 10), quantity: parseInt(qtyField ? qtyField.value : '1', 10) || 1 })
				})
					.then(function (r) {
						var nonce = r.headers.get('Nonce');
						if (nonce) { shop.nonce = nonce; }
						return r.json().then(function (data) { return { ok: r.ok, data: data }; });
					})
					.then(function (res) {
						if (!res.ok) { throw new Error((res.data && res.data.message) || 'Could not add that.'); }
						paintOrder(res.data);
						add.innerHTML = 'Added ✓';
						setTimeout(function () { add.innerHTML = label; add.disabled = false; }, 1600);
					})
					.catch(function (err) {
						add.disabled = false;
						add.innerHTML = label;
						var msg = document.createElement('p');
						msg.className = 'fix-error';
						msg.setAttribute('role', 'alert');
						msg.textContent = String(err.message || err).replace(/<[^>]+>/g, '');
						var old = $('.fix-error', form);
						if (old) { old.remove(); }
						form.appendChild(msg);
					});
			});
		});
	}

	/* ---------- Contact modal ---------- */
	var modal = $('#contactModal');
	var lastFocus = null;

	function modalIsOpen() { return !!modal && modal.classList.contains('open'); }

	function modalFocusables() {
		return $$('a[href], button:not([disabled]), input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])', modal)
			.filter(function (el) { return el.offsetParent !== null && el.getAttribute('tabindex') !== '-1' && !el.closest('[aria-hidden="true"]'); });
	}

	function fillCf7Challenge(scope) {
		if (!scope || !$('input[name="edc_js_ts"]', scope) || !window.fetch) { return; }
		fetch(scope.getAttribute('data-challenge') || '/wp-json/edc/v1/cf7-challenge', { credentials: 'same-origin' })
			.then(function (r) { return r.ok ? r.json() : null; })
			.then(function (data) {
				if (!data || !data.ts || !data.token) { return; }
				$$('input[name="edc_js_ts"]').forEach(function (el) { el.value = String(data.ts); });
				$$('input[name="edc_js_token"]').forEach(function (el) { el.value = String(data.token); });
			})
			.catch(function () {});
	}

	// The Contact page form sits in the page: fill its challenge on load and refresh it
	// on interaction once it is half an hour old (the server accepts tokens up to 2h).
	var inlineForm = $('.contact-form');
	if (inlineForm) {
		var challengeAt = 0;
		var refreshChallenge = function () {
			if (Date.now() - challengeAt < 30 * 60 * 1000) { return; }
			challengeAt = Date.now();
			fillCf7Challenge(inlineForm);
		};
		refreshChallenge();
		// Booking a fixed-price package: start the message with it.
		var pkgLabel = inlineForm.getAttribute('data-package');
		var pkgField = pkgLabel ? $('textarea', inlineForm) : null;
		if (pkgField && !pkgField.value) { pkgField.value = 'Package: ' + pkgLabel + '\n\n'; }
		['focusin', 'pointerdown', 'input'].forEach(function (type) { inlineForm.addEventListener(type, refreshChallenge); });
	}

	function openModal() {
		lastFocus = document.activeElement;
		setMenu(false);
		fillCf7Challenge(modal);
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
