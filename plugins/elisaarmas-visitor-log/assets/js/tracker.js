(function () {
  'use strict';

  if (!window.eaVlTrack || !eaVlTrack.ajaxUrl) return;

  var SESSION_KEY = 'ea_vl_sid';
  var sessionId = '';

  try {
    sessionId = localStorage.getItem(SESSION_KEY) || '';
    if (!sessionId || sessionId.length < 8) {
      sessionId = 's_' + Math.random().toString(36).slice(2) + Date.now().toString(36);
      localStorage.setItem(SESSION_KEY, sessionId);
    }
  } catch (e) {
    sessionId = 's_' + String(Date.now());
  }

  function trimText(el) {
    if (!el) return '';
    var t = (el.innerText || el.textContent || el.value || el.getAttribute('aria-label') || '').replace(/\s+/g, ' ').trim();
    return t.slice(0, 120);
  }

  function send(eventType, payload) {
    payload = payload || {};
    var body = new FormData();
    body.append('action', 'ea_vl_track');
    body.append('nonce', eaVlTrack.nonce);
    body.append('session_id', sessionId);
    body.append('event_type', eventType);
    body.append('event_label', payload.event_label || '');
    body.append('element_text', payload.element_text || '');
    body.append('element_href', payload.element_href || '');
    body.append('page_url', payload.page_url || window.location.href);

    try {
      if (navigator.sendBeacon) {
        navigator.sendBeacon(eaVlTrack.ajaxUrl, body);
        return;
      }
    } catch (e) {}

    fetch(eaVlTrack.ajaxUrl, {
      method: 'POST',
      body: body,
      credentials: 'same-origin',
      keepalive: true
    }).catch(function () {});
  }

  // Page view once per load
  send('page_view', {
    event_label: document.title || 'Page view',
    page_url: window.location.href
  });

  document.addEventListener(
    'click',
    function (e) {
      var el = e.target.closest(
        'a, button, .btn, .js-open-contact, .tab-btn, [data-ga-event], input[type="submit"]'
      );
      if (!el) return;

      // Contact modal open
      if (el.classList.contains('js-open-contact') || el.closest('.js-open-contact')) {
        var openEl = el.classList.contains('js-open-contact') ? el : el.closest('.js-open-contact');
        send('open_contact_modal', {
          event_label: openEl.getAttribute('data-ga-label') || trimText(openEl) || 'Open contact',
          element_text: trimText(openEl),
          element_href: openEl.getAttribute('href') || ''
        });
        return;
      }

      // Modal close
      if (el.id === 'modalClose') {
        send('close_contact_modal', {
          event_label: 'Close contact modal',
          element_text: '×'
        });
        return;
      }

      // Tabs
      if (el.classList.contains('tab-btn')) {
        send('tab_click', {
          event_label: el.getAttribute('data-tab') || trimText(el),
          element_text: trimText(el)
        });
        return;
      }

      // Mailto
      var href = el.getAttribute('href') || '';
      if (href.indexOf('mailto:') === 0) {
        send('mailto_click', {
          event_label: el.getAttribute('data-ga-label') || trimText(el) || 'Email',
          element_text: trimText(el),
          element_href: href
        });
        return;
      }

      // Buttons / CTAs with data-ga-event
      if (el.classList.contains('btn') || el.hasAttribute('data-ga-event') || el.matches('button, input[type="submit"]')) {
        var label = el.getAttribute('data-ga-label') || el.getAttribute('data-ga-event') || trimText(el) || 'Button';
        send('click_button', {
          event_label: label,
          element_text: trimText(el),
          element_href: href
        });
        return;
      }

      // Nav / other links
      if (el.tagName === 'A' && href) {
        var inNav = !!el.closest('nav, .site-nav, header, footer, .nav-links');
        send(inNav ? 'nav_click' : 'click_link', {
          event_label: el.getAttribute('data-ga-label') || trimText(el) || href,
          element_text: trimText(el),
          element_href: href
        });
      }
    },
    true
  );

  // Contact form submit attempt (CF7)
  document.addEventListener(
    'submit',
    function (e) {
      var form = e.target;
      if (!form || !form.classList || !form.classList.contains('wpcf7-form')) return;
      send('form_submit_attempt', {
        event_label: 'Contact form submit',
        element_text: 'Send message',
        page_url: window.location.href
      });
    },
    true
  );

  // CF7 DOM events
  document.addEventListener('wpcf7mailsent', function () {
    send('form_submit_success', {
      event_label: 'Contact form sent',
      element_text: 'Send message'
    });
  });
  document.addEventListener('wpcf7mailfailed', function () {
    send('form_submit_failed', {
      event_label: 'Contact form failed',
      element_text: 'Send message'
    });
  });
  document.addEventListener('wpcf7invalid', function () {
    send('form_submit_failed', {
      event_label: 'Contact form invalid',
      element_text: 'Send message'
    });
  });
})();
