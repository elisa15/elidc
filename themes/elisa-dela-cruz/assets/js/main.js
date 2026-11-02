/**
 * Elisa Dela Cruz theme — interactive behaviour.
 * Ported from the static mockup; terminal lines and testimonials are now
 * read from window.edcData (localized from PHP/ACF in functions.php) with
 * the original hardcoded content kept here as a hard fallback.
 */
(function () {
  function hideLoader(){ const l = document.getElementById('loader'); if (!l || l.classList.contains('hide')) return; l.classList.add('hide'); l.style.pointerEvents='none'; window.setTimeout(function(){ l.classList.add('is-gone'); }, 650); }
  document.addEventListener('DOMContentLoaded', () => setTimeout(hideLoader, 150));
  window.addEventListener('load', () => setTimeout(hideLoader, 100));
  setTimeout(hideLoader, 900);

  const glow = document.getElementById('cursorGlow');
  if (glow) {
    window.addEventListener('mousemove', e => {
      glow.style.left = e.clientX + 'px';
      glow.style.top = e.clientY + 'px';
    });
  }

  const heroText = document.getElementById('heroText');
  const heroTerminal = document.getElementById('heroTerminal');
  window.addEventListener('mousemove', e => {
    const x = (e.clientX / window.innerWidth - 0.5) * 14;
    const y = (e.clientY / window.innerHeight - 0.5) * 14;
    if (heroText) heroText.style.transform = `translate(${x * -0.4}px, ${y * -0.4}px)`;
    if (heroTerminal) heroTerminal.style.transform = `translate(${x}px, ${y}px)`;
  });

  const nav = document.getElementById('nav');
  if (nav) {
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 40);
    });
  }

  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15 });
  document.querySelectorAll('[data-reveal]').forEach(el => revealObserver.observe(el));

  const countObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const raw = el.textContent.trim();
        const suffix = raw.replace(/[0-9]/g,'');
        const target = parseInt(raw, 10);
        if (isNaN(target)) return;
        let cur = 0;
        const step = Math.max(1, Math.round(target / 50));
        const tick = () => {
          cur += step;
          if (cur >= target) { el.textContent = target + suffix; }
          else { el.textContent = cur + suffix; requestAnimationFrame(tick); }
        };
        el.classList.add('count-up');
        tick();
        countObserver.unobserve(el);
      }
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('.stat-inline .num, .metric .num').forEach(el => countObserver.observe(el));

  const typedBody = document.getElementById('typedBody');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const fallbackTermLines = [
    { p: '$ whoami', o: ['Elisa Dela Cruz', 'Senior Shopify Consultant'] },
    { p: '$ expertise', o: ['Shopify', 'WordPress', 'WooCommerce', 'Performance', 'SEO'] },
    { p: '$ current_status', o: ['Helping businesses launch better websites'] },
    { p: '$ recent_work', o: ['✔ Shopify migration', '✔ Website rescue', '✔ Core Web Vitals', '✔ Security hardening'], cls: 'accent' }
  ];
  const edcData = window.edcData || {};
  const termLines = (Array.isArray(edcData.terminalLines) && edcData.terminalLines.length)
    ? edcData.terminalLines
    : fallbackTermLines;

  let li = 0, oi = 0, ci = 0, lineEl = null;
  function typeStep(){
    if (!typedBody) return;
    if (li >= termLines.length){
      typedBody.insertAdjacentHTML('beforeend', '<span class="cursor-blink"></span>');
      setTimeout(restartTyping, 3200);
      return;
    }
    const block = termLines[li];
    if (oi === 0 && ci === 0){
      typedBody.insertAdjacentHTML('beforeend', `<div><span class="prompt">${block.p}</span></div>`);
    }
    if (oi >= block.o.length){ li++; oi = 0; ci = 0; setTimeout(typeStep, 260); return; }
    const word = block.o[oi];
    if (ci === 0) typedBody.insertAdjacentHTML('beforeend', `<div class="out ${block.cls||''}" data-l="${li}-${oi}"></div>`);
    lineEl = typedBody.querySelector(`[data-l="${li}-${oi}"]`);
    lineEl.textContent = word.slice(0, ci) + (ci < word.length ? '▌' : '');
    if (ci < word.length){ ci++; setTimeout(typeStep, 26); }
    else { ci = 0; oi++; setTimeout(typeStep, 160); }
  }
  function restartTyping(){
    typedBody.innerHTML = '';
    li = 0; oi = 0; ci = 0;
    typeStep();
  }
  if (typedBody) {
    if (reducedMotion){
      typedBody.innerHTML = termLines.map(b => `<div><span class="prompt">${b.p}</span></div>` + b.o.map(o => `<div class="out">${o}</div>`).join('')).join('');
    } else {
      const terminalEl = document.querySelector('.terminal');
      if (terminalEl) {
        const typeObserver = new IntersectionObserver((entries) => {
          entries.forEach(entry => { if (entry.isIntersecting){ typeStep(); typeObserver.disconnect(); } });
        }, { threshold: 0.3 });
        typeObserver.observe(terminalEl);
      }
    }
  }

  // Real ACF testimonials only; there is deliberately no hardcoded fallback.
  const testimonials = Array.isArray(edcData.testimonials) ? edcData.testimonials : [];

  let ti = 0;
  function renderTesti(i){
    const t = testimonials[i];
    if (!t) return;
    const quoteEl = document.getElementById('testiQuote');
    const nameEl = document.getElementById('testiName');
    const companyEl = document.getElementById('testiCompany');
    const starsEl = document.getElementById('testiStars');
    if (quoteEl) quoteEl.textContent = t.q;
    if (nameEl) nameEl.textContent = t.n;
    if (companyEl) companyEl.textContent = t.c;
    if (starsEl) starsEl.textContent = '★'.repeat(t.s) + '☆'.repeat(5 - t.s);
    document.querySelectorAll('.testi-dot').forEach(d => d.classList.toggle('active', +d.dataset.i === i));
  }
  if (testimonials.length) {
    renderTesti(0);
    document.querySelectorAll('.testi-dot').forEach(d => d.addEventListener('click', () => { ti = +d.dataset.i; renderTesti(ti); }));
    // Auto-advance only when there is something to rotate to (and motion is welcome).
    if (testimonials.length > 1 && !reducedMotion) {
      setInterval(() => { ti = (ti + 1) % testimonials.length; renderTesti(ti); }, 8000);
    }
  }

  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const tab = btn.dataset.tab;
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b === btn));
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.toggle('active', p.dataset.panel === tab));
    });
  });

  const caseTrack = document.getElementById('caseTrack');
  if (caseTrack) {
    const caseSlides = caseTrack.querySelectorAll('.case-slide');
    const caseDots = document.querySelectorAll('.case-dot');
    function goToCase(i){
      i = Math.max(0, Math.min(caseSlides.length - 1, i));
      caseTrack.scrollTo({ left: i * caseTrack.clientWidth, behavior: 'smooth' });
    }
    function currentCaseIndex(){
      return Math.round(caseTrack.scrollLeft / caseTrack.clientWidth);
    }
    const prevBtn = document.getElementById('casePrev');
    const nextBtn = document.getElementById('caseNext');
    if (prevBtn) prevBtn.addEventListener('click', () => goToCase(currentCaseIndex() - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goToCase(currentCaseIndex() + 1));
    caseDots.forEach(d => d.addEventListener('click', () => goToCase(+d.dataset.i)));
    let caseScrollTimer;
    caseTrack.addEventListener('scroll', () => {
      clearTimeout(caseScrollTimer);
      caseScrollTimer = setTimeout(() => {
        const i = currentCaseIndex();
        caseDots.forEach(d => d.classList.toggle('active', +d.dataset.i === i));
      }, 80);
    });
  }

  const contactModal = document.getElementById('contactModal');
  if (contactModal) {
    function isContactOpen(){
      return contactModal.style.visibility === 'visible';
    }
    function edcFillCf7Challenge(){
      const tsEl = document.getElementById('edc_js_ts');
      const tokEl = document.getElementById('edc_js_token');
      if (!tsEl || !tokEl) return;
      fetch('/wp-json/edc/v1/cf7-challenge', { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
          if (!data || !data.ts || !data.token) return;
          tsEl.value = String(data.ts);
          tokEl.value = String(data.token);
          document.querySelectorAll('input[name="edc_js_ts"]').forEach(el => { el.value = String(data.ts); });
          document.querySelectorAll('input[name="edc_js_token"]').forEach(el => { el.value = String(data.token); });
        })
        .catch(() => {});
    }
    function openContactModal(e){
      if (e) e.preventDefault();
      const nav = document.getElementById('nav');
      if (nav && nav.classList.contains('nav-open') && typeof window.edcSetNavOpen === 'function') {
        window.edcSetNavOpen(false);
      }
      edcFillCf7Challenge();
      contactModal.style.opacity = 1;
      contactModal.style.visibility = 'visible';
      contactModal.querySelector('.modal-box').style.transform = 'translateY(0) scale(1)';
      document.body.style.overflow = 'hidden';
    }
    function closeContactModal(){
      if (!isContactOpen()) return;
      contactModal.style.opacity = 0;
      contactModal.style.visibility = 'hidden';
      contactModal.querySelector('.modal-box').style.transform = 'translateY(24px) scale(.96)';
      if (!document.body.classList.contains('nav-locked')) {
        document.body.style.overflow = '';
      }
    }
    document.querySelectorAll('.js-open-contact').forEach(el => el.addEventListener('click', openContactModal));
    const modalCloseBtn = document.getElementById('modalClose');
    if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeContactModal);
    contactModal.addEventListener('click', (e) => { if (e.target === contactModal) closeContactModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeContactModal(); });
  }

  const heroAnimTargets = '.hero .eyebrow, .hero h1, .hero p, .hero .actions, .terminal';
  if (typeof gsap !== 'undefined' && !reducedMotion) {
    gsap.set(heroAnimTargets, { opacity: 0, y: 24 });
    gsap.timeline({ delay: 0.3 })
      .to('.hero .eyebrow', { opacity: 1, y: 0, duration: 0.7, ease: 'power3.out' })
      .to('.hero h1', { opacity: 1, y: 0, duration: 0.8, ease: 'power3.out' }, '-=0.5')
      .to('.hero p', { opacity: 1, y: 0, duration: 0.7, ease: 'power3.out' }, '-=0.5')
      .to('.hero .actions', { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' }, '-=0.45')
      .to('.terminal', { opacity: 1, y: 0, duration: 0.8, ease: 'power3.out' }, '-=0.6');

    if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
      document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mousemove', (e) => {
          const r = btn.getBoundingClientRect();
          const x = (e.clientX - r.left - r.width / 2) * 0.25;
          const y = (e.clientY - r.top - r.height / 2) * 0.35;
          gsap.to(btn, { x, y, duration: 0.3, ease: 'power2.out' });
        });
        btn.addEventListener('mouseleave', () => { gsap.to(btn, { x: 0, y: 0, duration: 0.4, ease: 'elastic.out(1,0.4)' }); });
      });
    }
  } else {
    document.querySelectorAll(heroAnimTargets).forEach(el => { el.style.opacity = 1; });
  }
})();

  /* Mobile burger menu */
  (function () {
    const nav = document.getElementById('nav');
    const toggle = document.getElementById('navToggle');
    const panel = document.getElementById('navPanel');
    if (!nav || !toggle || !panel) return;

    const mq = window.matchMedia('(max-width: 980px)');
    let lastToggleAt = 0;

    const setOpen = (open) => {
      const isOpen = !!open;
      if (isOpen === nav.classList.contains('nav-open')) return;

      nav.classList.toggle('nav-open', isOpen);
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      toggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
      document.documentElement.classList.toggle('nav-locked', isOpen);
      document.body.classList.toggle('nav-locked', isOpen);
    };

    window.edcSetNavOpen = setOpen;

    const onToggle = (e) => {
      // Respond on pointerdown so the menu appears before click delay / paint lag
      if (e.type === 'pointerdown' && e.pointerType === 'mouse' && e.button !== 0) return;
      e.preventDefault();
      e.stopPropagation();
      const now = Date.now();
      if (now - lastToggleAt < 400) return;
      lastToggleAt = now;
      setOpen(!nav.classList.contains('nav-open'));
    };

    toggle.addEventListener('pointerdown', onToggle);
    // Fallback for older browsers without Pointer Events
    toggle.addEventListener('click', onToggle);

    panel.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && nav.classList.contains('nav-open')) setOpen(false);
    });

    const onBreakpoint = (e) => {
      if (!e.matches && nav.classList.contains('nav-open')) setOpen(false);
    };
    if (typeof mq.addEventListener === 'function') mq.addEventListener('change', onBreakpoint);
    else if (typeof mq.addListener === 'function') mq.addListener(onBreakpoint);
  })();

  /* Mobile: dark burger when nav sits over a .light section */
  (function () {
    const nav = document.getElementById('nav');
    if (!nav) return;

    const mq = window.matchMedia('(max-width: 980px)');
    let ticking = false;

    const update = () => {
      ticking = false;
      if (!mq.matches || nav.classList.contains('nav-open')) {
        nav.classList.remove('nav-on-light');
        return;
      }

      const probeY = Math.max(12, Math.round((nav.getBoundingClientRect().bottom || 64) * 0.55));
      const sections = document.querySelectorAll('section.light, .light');
      let onLight = false;

      for (let i = 0; i < sections.length; i++) {
        const r = sections[i].getBoundingClientRect();
        if (r.top <= probeY && r.bottom >= probeY) {
          onLight = true;
          break;
        }
      }

      nav.classList.toggle('nav-on-light', onLight);
    };

    const onScroll = () => {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(update);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    if (typeof mq.addEventListener === 'function') mq.addEventListener('change', update);
    else if (typeof mq.addListener === 'function') mq.addListener(update);

    // Re-check when menu opens/closes
    const prev = window.edcSetNavOpen;
    if (typeof prev === 'function') {
      window.edcSetNavOpen = function (open) {
        prev(open);
        window.requestAnimationFrame(update);
      };
    }

    update();
  })();


  /* Back to top */
  (function () {
    const btn = document.getElementById('backToTop');
    if (!btn) return;

    const toggle = () => {
      btn.classList.toggle('is-visible', window.scrollY > 320);
    };

    window.addEventListener('scroll', toggle, { passive: true });
    toggle();

    btn.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  })();

  /* In-page section scrolling for nav/footer hash links */
  (function () {
    const getOffset = () => {
      const nav = document.getElementById('nav');
      return (nav ? nav.offsetHeight : 72) + 12;
    };

    const scrollToHash = (hash) => {
      if (!hash || hash === '#') return false;
      const id = hash.replace(/^#/, '');
      const el = document.getElementById(id);
      if (!el) return false;
      const top = el.getBoundingClientRect().top + window.scrollY - getOffset();
      window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
      return true;
    };

    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href*="#"]');
      if (!link) return;
      if (link.classList.contains('js-open-contact')) return;

      let url;
      try { url = new URL(link.href, window.location.href); } catch (err) { return; }

      const pagePath = window.location.pathname.replace(/\/$/, '') || '/';
      const linkPath = url.pathname.replace(/\/$/, '') || '/';
      const onHome = pagePath === '/' || /index\.php$/.test(pagePath);
      const linkHome = linkPath === '/' || /index\.php$/.test(linkPath);
      if (!(onHome && linkHome) && pagePath !== linkPath) return;
      if (!url.hash) return;

      if (scrollToHash(url.hash)) {
        e.preventDefault();
        history.pushState(null, '', url.hash);
        // Menu close is handled by the burger link handler.
      }
    });

    if (window.location.hash) {
      window.setTimeout(() => scrollToHash(window.location.hash), 150);
    }
  })();

  /* GA4 button / CTA click tracking */
  (function () {
    const send = (name, params) => {
      if (typeof window.gtag !== 'function') return;
      window.gtag('event', name, params || {});
    };

    document.addEventListener('click', (e) => {
      const el = e.target.closest('a.btn, button.btn, a.js-open-contact, a.work-link, a.back-to-top, .wpcf7-submit, [data-ga-event]');
      if (!el) return;

      let label = (el.getAttribute('data-ga-label') || el.value || el.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 80);
      let eventName = el.getAttribute('data-ga-event') || 'cta_click';
      if (el.classList.contains('wpcf7-submit')) {
        eventName = el.getAttribute('data-ga-event') || 'contact_form_submit';
        label = label || 'Send message';
      }
      const href = el.getAttribute('href') || '';

      send(eventName, {
        button_label: label || 'unknown',
        link_url: href,
        page_path: window.location.pathname
      });
    }, true);
  })();
