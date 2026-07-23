/* ═══════════════════════════════════════════════════════════
   FK EPICURE FOODS — Main Scripts
   Sticky header · mobile menu · scroll reveal · counters ·
   nav scroll-spy · contact form (AJAX → mailer.php)
   ═══════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ---------- sticky header ---------- */
  var header = document.getElementById('header');
  window.addEventListener('scroll', function () {
    header.classList.toggle('scrolled', window.scrollY > 40);
  }, { passive: true });

  /* ---------- mobile menu ---------- */
  var burger = document.getElementById('burger');
  var navLinks = document.getElementById('navLinks');
  burger.addEventListener('click', function () {
    burger.classList.toggle('open');
    navLinks.classList.toggle('open');
  });
  navLinks.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', function () {
      burger.classList.remove('open');
      navLinks.classList.remove('open');
    });
  });

  /* ---------- reveal on scroll ---------- */
  var revealIO = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) {
        e.target.classList.add('in');
        revealIO.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal').forEach(function (el) { revealIO.observe(el); });

  /* ---------- nav scroll-spy ---------- */
  var spyLinks = navLinks.querySelectorAll('a[href^="#"]');
  var spyTargets = [];
  spyLinks.forEach(function (a) {
    var t = document.querySelector(a.getAttribute('href'));
    if (t) spyTargets.push({ link: a, el: t });
  });
  if (spyTargets.length) {
    var spy = function () {
      var line = window.scrollY + 140;
      var current = null;
      spyTargets.forEach(function (t) {
        if (t.el.offsetTop <= line) current = t.link;
      });
      spyLinks.forEach(function (a) { a.classList.toggle('active', a === current); });
    };
    window.addEventListener('scroll', spy, { passive: true });
    spy();
  }

  /* ---------- animated counters ---------- */
  var counterIO = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (!e.isIntersecting) return;
      var el = e.target;
      var target = parseInt(el.dataset.count, 10);
      var suffix = el.dataset.suffix || '';
      var t0 = performance.now();
      var dur = 1600;
      var tick = function (now) {
        var p = Math.min((now - t0) / dur, 1);
        el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))) + suffix;
        if (p < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
      counterIO.unobserve(el);
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('[data-count]').forEach(function (el) { counterIO.observe(el); });

  /* ---------- back to top ---------- */
  var toTop = document.getElementById('toTop');
  if (toTop) {
    window.addEventListener('scroll', function () {
      toTop.classList.toggle('show', window.scrollY > 700);
    }, { passive: true });
    toTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  /* ---------- enquiry forms → mailer.php ---------- */
  document.querySelectorAll('form.js-enquiry, #contactForm').forEach(function (form) {
    var statusBox = form.querySelector('.form-status');
    var btn = form.querySelector('.form-btn') || form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      if (statusBox) statusBox.className = 'form-status';
      var original = btn ? btn.textContent : '';
      if (btn) { btn.disabled = true; btn.textContent = 'Sending…'; }

      fetch('mailer.php', {
        method: 'POST',
        body: new FormData(form)
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data.success) {
            statusBox.textContent = data.message || 'Thank you — your message has been sent. We’ll be in touch shortly.';
            statusBox.classList.add('ok');
            form.reset();
          } else {
            statusBox.textContent = data.message || 'Something went wrong. Please try again or email us directly.';
            statusBox.classList.add('err');
          }
        })
        .catch(function () {
          statusBox.textContent = 'Could not reach the server. Please email us at info@fkepicurefoods.in.';
          statusBox.classList.add('err');
        })
        .finally(function () {
          if (btn) { btn.disabled = false; btn.textContent = original; }
        });
    });
  });
})();
