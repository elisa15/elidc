/* Prototype behaviour. Everything is progressive: the page is complete without this file. */
(function () {
  document.documentElement.classList.add('js');
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Nav: solid bar after scrolling, mobile menu
  const nav = document.querySelector('.nav');
  const onScroll = () => nav && nav.classList.toggle('scrolled', scrollY > 30);
  addEventListener('scroll', onScroll, { passive: true }); onScroll();
  const menuBtn = document.querySelector('.menu-btn');
  if (menuBtn) {
    menuBtn.addEventListener('click', () => {
      const open = nav.classList.toggle('open');
      menuBtn.setAttribute('aria-expanded', open);
      menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && nav.classList.contains('open')) { menuBtn.click(); menuBtn.focus(); } });
  }

  // Reveal on scroll
  if ('IntersectionObserver' in window && !reduced) {
    const io = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { threshold: .12 });
    document.querySelectorAll('[data-reveal]').forEach(el => io.observe(el));
  } else document.querySelectorAll('[data-reveal]').forEach(el => el.classList.add('in'));

  // Work filter (links work without JS via ?type=)
  const filters = document.querySelectorAll('.filters a');
  const live = document.getElementById('filter-status');
  function applyFilter(type) {
    let n = 0;
    document.querySelectorAll('.work-grid .card').forEach(c => {
      const show = type === 'all' || c.dataset.type.split(' ').includes(type);
      c.hidden = !show; if (show) n++;
    });
    filters.forEach(a => a.dataset.type === type ? a.setAttribute('aria-current', 'true') : a.removeAttribute('aria-current'));
    if (live) live.textContent = `Showing ${n} ${type === 'all' ? '' : type + ' '}project${n === 1 ? '' : 's'}`;
  }
  filters.forEach(a => a.addEventListener('click', e => {
    e.preventDefault();
    const go = () => applyFilter(a.dataset.type);
    document.startViewTransition && !reduced ? document.startViewTransition(go) : go();
    history.replaceState(null, '', a.getAttribute('href'));
  }));
  const qType = new URLSearchParams(location.search).get('type');
  if (qType && filters.length) applyFilter(qType);

  // B−: "what's behind it" toggles (touch / small screens)
  document.querySelectorAll('.behind-toggle').forEach(btn => {
    const card = btn.closest('.card');
    const panel = card.querySelector('.behind');
    const close = panel.querySelector('.behind-close');
    btn.addEventListener('click', () => {
      const open = card.classList.toggle('show-behind');
      btn.setAttribute('aria-expanded', open);
      if (open && close) close.focus();
    });
    if (close) close.addEventListener('click', () => { card.classList.remove('show-behind'); btn.setAttribute('aria-expanded', 'false'); btn.focus(); });
  });

  // Contact modal with focus trap and focus return
  const modal = document.getElementById('contactModal');
  let lastTrigger = null;
  function openModal(t) {
    lastTrigger = t; modal.classList.remove('done'); modal.classList.add('open');
    modal.querySelector('input').focus();
  }
  function closeModal() { modal.classList.remove('open'); lastTrigger && lastTrigger.focus(); }
  document.querySelectorAll('[data-open-contact]').forEach(b => b.addEventListener('click', e => { e.preventDefault(); openModal(b); }));
  if (modal) {
    modal.querySelector('.close').addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    modal.addEventListener('keydown', e => {
      if (e.key === 'Escape') closeModal();
      if (e.key !== 'Tab') return;
      const f = [...modal.querySelectorAll('button,input,textarea,a[href]')].filter(el => el.offsetParent);
      if (e.shiftKey && document.activeElement === f[0]) { e.preventDefault(); f.at(-1).focus(); }
      else if (!e.shiftKey && document.activeElement === f.at(-1)) { e.preventDefault(); f[0].focus(); }
    });
    modal.querySelector('form').addEventListener('submit', e => {
      e.preventDefault(); modal.classList.add('done');
      modal.querySelector('.sent').focus();
    });
  }

  // Copy email
  document.querySelectorAll('[data-copy]').forEach(b => b.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(b.dataset.copy); } catch (e) {}
    const s = document.getElementById('copy-status'); const old = b.textContent;
    b.textContent = 'Copied ✓'; if (s) s.textContent = 'Email address copied';
    setTimeout(() => { b.textContent = old; }, 1600);
  }));

  // Review notes toggle (prototype only)
  const notesBtn = document.getElementById('notesToggle');
  if (notesBtn) notesBtn.addEventListener('click', () => {
    const off = document.body.classList.toggle('notes-off');
    notesBtn.setAttribute('aria-pressed', !off);
    notesBtn.textContent = off ? 'Show review notes' : 'Hide review notes';
  });
})();

// Timeline: show the 5 most recent entries; older history one click away (all visible without JS)
(function () {
  const log = document.querySelector('.log'), more = document.querySelector('.log-more');
  if (!log || !more) return;
  log.classList.add('collapsed'); more.hidden = false;
  more.addEventListener('click', () => {
    const open = log.classList.toggle('collapsed') === false;
    more.setAttribute('aria-expanded', open);
    more.querySelector('span').textContent = open ? '(hide 2011–2015)' : '(show 2011–2015)';
    if (open) log.querySelector('li.older summary').focus();
  });
})();
