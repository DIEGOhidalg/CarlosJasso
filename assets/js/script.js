/* ============================================================
   JASO Y ASOCIADOS — script.js
   ============================================================ */

(() => {
  'use strict';

  /* ── Navbar sticky ──────────────────────────────────────── */
  const navbar = document.getElementById('navbar');
  if (navbar) {
    const onScroll = () => {
      navbar.classList.toggle('scrolled', window.scrollY > 40);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ── Smooth scroll para anclas ─────────────────────────── */
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
      const target = document.querySelector(link.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      const offset = navbar ? navbar.offsetHeight + 12 : 80;
      window.scrollTo({ top: target.offsetTop - offset, behavior: 'smooth' });

      // Cerrar menú mobile si está abierto
      const toggler = document.querySelector('.navbar-collapse');
      if (toggler && toggler.classList.contains('show')) {
        const bsCollapse = bootstrap.Collapse.getInstance(toggler);
        if (bsCollapse) bsCollapse.hide();
      }
    });
  });

  /* ── Animación de aparición al hacer scroll ────────────── */
  const revealEls = document.querySelectorAll('.reveal');
  if (revealEls.length) {
    const observer = new IntersectionObserver(
      entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );
    revealEls.forEach(el => observer.observe(el));
  }

  /* ── Validación del formulario ─────────────────────────── */
  const form = document.getElementById('contactForm');
  if (form) {
    const alertBox = document.getElementById('formAlert');

    const showAlert = msg => {
      if (!alertBox) return;
      alertBox.textContent = msg;
      alertBox.classList.add('show');
      alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    const hideAlert = () => {
      if (alertBox) alertBox.classList.remove('show');
    };

    const isValidEmail = email => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    const isValidPhone = phone => /^[\d\s\+\-\(\)]{8,15}$/.test(phone.trim());

    form.addEventListener('submit', e => {
      hideAlert();
      let valid = true;

      // Limpiar estados previos
      form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

      const nombre  = form.querySelector('#nombre');
      const telefono = form.querySelector('#telefono');
      const correo  = form.querySelector('#correo');
      const tipo    = form.querySelector('#tipo');
      const mensaje = form.querySelector('#mensaje');

      if (!nombre.value.trim()) { nombre.classList.add('is-invalid'); valid = false; }
      if (!isValidPhone(telefono.value)) { telefono.classList.add('is-invalid'); valid = false; }
      if (!isValidEmail(correo.value))  { correo.classList.add('is-invalid');  valid = false; }
      if (!tipo.value)                  { tipo.classList.add('is-invalid');    valid = false; }
      if (!mensaje.value.trim())        { mensaje.classList.add('is-invalid'); valid = false; }

      if (!valid) {
        e.preventDefault();
        showAlert('Por favor completa correctamente todos los campos requeridos.');
        return;
      }

      // Botón de carga
      const btn = form.querySelector('.btn-submit');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Enviando…';
      }
    });

    // Limpiar validación al escribir
    form.querySelectorAll('.form-control, .form-select').forEach(input => {
      input.addEventListener('input', () => {
        input.classList.remove('is-invalid');
        hideAlert();
      });
    });
  }

  /* ── Nav link activo al hacer scroll ───────────────────── */
  const sections = document.querySelectorAll('section[id]');
  const navLinks  = document.querySelectorAll('.navbar-jaso .nav-link[href^="#"]');
  if (sections.length && navLinks.length) {
    const activateLink = () => {
      let current = '';
      sections.forEach(sec => {
        if (window.scrollY >= sec.offsetTop - 100) current = sec.id;
      });
      navLinks.forEach(link => {
        link.classList.toggle('active', link.getAttribute('href') === `#${current}`);
      });
    };
    window.addEventListener('scroll', activateLink, { passive: true });
  }

  /* ── WhatsApp float pulse ──────────────────────────────── */
  const waFloat = document.querySelector('.wa-float');
  if (waFloat) {
    let shown = false;
    window.addEventListener('scroll', () => {
      if (!shown && window.scrollY > 300) {
        waFloat.style.animation = 'none';
        shown = true;
      }
    }, { passive: true });
  }

})();
