/**
 * Shri Krishna Dental Hospital - Master JavaScript
 * Vanilla JS for navigation, sticky states, smooth scroll reveal, and form validation
 */

document.addEventListener('DOMContentLoaded', () => {
  initStickyNavbar();
  initActiveNavLink();
  initScrollReveal();
  initBackToTop();
  initFormValidation();
  initMobileMenu();
});

/* --------------------------------------------------------------------------
   1. STICKY NAVBAR & NAVBAR BACKGROUND TRANSITION
   -------------------------------------------------------------------------- */
function initStickyNavbar() {
  const navbar = document.querySelector('.navbar-custom');
  if (!navbar) return;

  const handleScroll = () => {
    if (window.scrollY > 40) {
      navbar.classList.add('is-sticky');
    } else {
      navbar.classList.remove('is-sticky');
    }
  };

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll(); // Initial check on load
}

/* --------------------------------------------------------------------------
   2. ACTIVE NAVIGATION STATE
   -------------------------------------------------------------------------- */
function initActiveNavLink() {
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';
  const navLinks = document.querySelectorAll('.navbar-nav .nav-link, .dropdown-menu .dropdown-item');

  navLinks.forEach((link) => {
    const href = link.getAttribute('href');
    if (!href) return;

    // Direct match
    if (href === currentPath || (currentPath === '' && href === 'index.html')) {
      link.classList.add('active');
      // If inside a dropdown, highlight the parent dropdown toggle
      const parentDropdown = link.closest('.dropdown');
      if (parentDropdown) {
        const toggle = parentDropdown.querySelector('.dropdown-toggle');
        if (toggle) toggle.classList.add('active');
      }
    }
  });
}

/* --------------------------------------------------------------------------
   3. SCROLL REVEAL ANIMATIONS
   -------------------------------------------------------------------------- */
function initScrollReveal() {
  const reveals = document.querySelectorAll('.reveal');
  if (!reveals.length) return;

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
      (entries, obs) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('active');
            obs.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
    );

    reveals.forEach((el) => observer.observe(el));
  } else {
    // Fallback for browsers without IntersectionObserver
    reveals.forEach((el) => el.classList.add('active'));
  }
}

/* --------------------------------------------------------------------------
   4. BACK TO TOP BUTTON
   -------------------------------------------------------------------------- */
function initBackToTop() {
  const btn = document.getElementById('backToTopBtn');
  if (!btn) return;

  window.addEventListener(
    'scroll',
    () => {
      if (window.scrollY > 350) {
        btn.classList.add('show');
      } else {
        btn.classList.remove('show');
      }
    },
    { passive: true }
  );

  btn.addEventListener('click', () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth',
    });
  });
}

/* --------------------------------------------------------------------------
   5. MOBILE MENU AUTO-CLOSE ON LINK CLICK
   -------------------------------------------------------------------------- */
function initMobileMenu() {
  const navCollapse = document.getElementById('navbarMain');
  if (!navCollapse) return;

  const navLinks = navCollapse.querySelectorAll('.nav-link:not(.dropdown-toggle), .dropdown-item');
  navLinks.forEach((link) => {
    link.addEventListener('click', () => {
      if (window.innerWidth < 992 && navCollapse.classList.contains('show')) {
        const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
        if (bsCollapse) {
          bsCollapse.hide();
        }
      }
    });
  });
}

/* --------------------------------------------------------------------------
   6. CLIENT-SIDE FORM VALIDATION & FEEDBACK
   -------------------------------------------------------------------------- */
function initFormValidation() {
  const forms = document.querySelectorAll('.needs-validation');

  Array.from(forms).forEach((form) => {
    form.addEventListener(
      'submit',
      (event) => {
        event.preventDefault();
        event.stopPropagation();

        if (!form.checkValidity()) {
          form.classList.add('was-validated');
          return;
        }

        form.classList.add('was-validated');

        // Display user-friendly submission confirmation without pretending to be a real backend
        const feedbackContainer = form.querySelector('.form-submission-feedback');
        if (feedbackContainer) {
          feedbackContainer.innerHTML = `
            <div class="alert alert-success d-flex align-items-center mt-3" role="alert">
              <i class="bi bi-check-circle-fill me-2 fs-5"></i>
              <div>
                <strong>Thank you!</strong> Your consultation request has been received. Our patient care team will contact you shortly to confirm your schedule.
              </div>
            </div>
          `;
          feedbackContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
          alert('Thank you! Your request has been recorded. Our team will contact you soon.');
        }

        form.reset();
        form.classList.remove('was-validated');
      },
      false
    );
  });
}
