/**
 * Shri Krishna Dental Hospital - Master JavaScript
 * Vanilla JS for navigation, sticky states, smooth scroll reveal, and form validation
 */

document.addEventListener('DOMContentLoaded', () => {
  initHomePreloader();
  initStickyNavbar();
  initActiveNavLink();
  initScrollReveal();
  initBackToTop();
  initFormValidation();
  initMobileMenu();
  initTestimonialsSlider();
});

/* --------------------------------------------------------------------------
   1. STICKY NAVBAR & NAVBAR BACKGROUND TRANSITION
   -------------------------------------------------------------------------- */
function initStickyNavbar() {
  const navbar = document.querySelector('.navbar-custom');
  if (!navbar) return;

  const handleScroll = () => {
    if (window.scrollY > 30) {
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
      { threshold: 0.02, rootMargin: '150px 0px 150px 0px' }
    );

    reveals.forEach((el) => {
      // If already within or above the viewport on initial render, activate immediately
      const rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight + 150) {
        el.classList.add('active');
      } else {
        observer.observe(el);
      }
    });
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
   6. CLIENT-SIDE FORM VALIDATION & REDIRECTION TO THANK YOU PAGE
   -------------------------------------------------------------------------- */
function initFormValidation() {
  const forms = document.querySelectorAll('form, .needs-validation, #appointmentForm, #contactInquiryForm, #lpAppointmentForm');

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

        // Provide immediate visual loading state on submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
        }

        // Redirect to thank you page
        setTimeout(() => {
          window.location.href = 'thank-you.html';
        }, 400);
      },
      false
    );
  });
}

/* --------------------------------------------------------------------------
   7. 3-SECOND HOME LOGO INTRO & EHS BANNER ANIMATION
   -------------------------------------------------------------------------- */
function initHomePreloader() {
  const preloader = document.getElementById('homePreloader');
  const ehsStrip = document.querySelector('.hero-ehs-strip');

  if (preloader) {
    // 3-second animated logo intro
    setTimeout(() => {
      preloader.classList.add('preloader-hidden');
      setTimeout(() => {
        preloader.style.display = 'none';
        // Trigger EHS animated entrance after preloader fades out
        if (ehsStrip) {
          ehsStrip.classList.add('ehs-animate-in');
        }
      }, 550);
    }, 2600);
  } else {
    // If loaded without preloader, animate EHS strip shortly after DOM is ready
    if (ehsStrip) {
      setTimeout(() => {
        ehsStrip.classList.add('ehs-animate-in');
      }, 350);
    }
  }
}

/* --------------------------------------------------------------------------
   8. TESTIMONIALS SLIDER (SEAMLESS INFINITE HARDWARE-ACCELERATED SLIDER)
   -------------------------------------------------------------------------- */
function initTestimonialsSlider() {
  const wrapper = document.getElementById('testimonialsTrackWrapper');
  const track = document.getElementById('testimonialsTrack');
  const prevBtn = document.getElementById('testimonialPrev');
  const nextBtn = document.getElementById('testimonialNext');
  if (!wrapper || !track) return;

  // Clone all cards for a mathematically seamless infinite loop
  const originalCards = Array.from(track.children);
  if (!originalCards.length) return;

  originalCards.forEach((card) => {
    const clone = card.cloneNode(true);
    clone.setAttribute('aria-hidden', 'true');
    track.appendChild(clone);
  });

  let singleSetWidth = 0;
  function calculateWidth() {
    singleSetWidth = 0;
    const gap = parseFloat(window.getComputedStyle(track).gap) || 24;
    originalCards.forEach((card) => {
      singleSetWidth += card.offsetWidth + gap;
    });
  }

  calculateWidth();
  window.addEventListener('resize', calculateWidth);

  let position = 0;
  const speed = 0.75; // Smooth sub-pixel progression at 60fps
  let isPaused = false;
  let isDragging = false;
  let startX = 0;
  let dragStartPos = 0;
  let isTransitioning = false;

  function step() {
    if (!isPaused && !isDragging && !isTransitioning) {
      position -= speed;
      if (Math.abs(position) >= singleSetWidth) {
        position += singleSetWidth;
      }
      track.style.transform = `translateX(${position}px)`;
    }
    requestAnimationFrame(step);
  }

  requestAnimationFrame(step);

  // Hover Pause
  wrapper.addEventListener('mouseenter', () => { isPaused = true; });
  wrapper.addEventListener('mouseleave', () => {
    if (!isDragging) isPaused = false;
  });

  // Next Button Step
  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      if (isTransitioning) return;
      isTransitioning = true;
      const cardWidth = (originalCards[0]?.offsetWidth || 320) + (parseFloat(window.getComputedStyle(track).gap) || 24);
      position -= cardWidth;
      
      track.style.transition = 'transform 0.45s cubic-bezier(0.25, 1, 0.5, 1)';
      track.style.transform = `translateX(${position}px)`;

      setTimeout(() => {
        if (Math.abs(position) >= singleSetWidth) {
          position += singleSetWidth;
        }
        track.style.transition = 'none';
        track.style.transform = `translateX(${position}px)`;
        isTransitioning = false;
      }, 450);
    });
  }

  // Prev Button Step
  if (prevBtn) {
    prevBtn.addEventListener('click', () => {
      if (isTransitioning) return;
      isTransitioning = true;
      const cardWidth = (originalCards[0]?.offsetWidth || 320) + (parseFloat(window.getComputedStyle(track).gap) || 24);
      position += cardWidth;

      track.style.transition = 'transform 0.45s cubic-bezier(0.25, 1, 0.5, 1)';
      track.style.transform = `translateX(${position}px)`;

      setTimeout(() => {
        if (position > 0) {
          position -= singleSetWidth;
        }
        track.style.transition = 'none';
        track.style.transform = `translateX(${position}px)`;
        isTransitioning = false;
      }, 450);
    });
  }

  // Mouse Drag Events
  wrapper.addEventListener('mousedown', (e) => {
    isDragging = true;
    isPaused = true;
    startX = e.pageX;
    dragStartPos = position;
    track.style.transition = 'none';
  });

  window.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    const delta = e.pageX - startX;
    position = dragStartPos + delta;

    if (Math.abs(position) >= singleSetWidth) {
      position += singleSetWidth;
      dragStartPos += singleSetWidth;
    } else if (position > 0) {
      position -= singleSetWidth;
      dragStartPos -= singleSetWidth;
    }
    track.style.transform = `translateX(${position}px)`;
  });

  window.addEventListener('mouseup', () => {
    if (isDragging) {
      isDragging = false;
      isPaused = false;
    }
  });

  // Touch Drag Events
  wrapper.addEventListener('touchstart', (e) => {
    if (!e.touches.length) return;
    isDragging = true;
    isPaused = true;
    startX = e.touches[0].pageX;
    dragStartPos = position;
    track.style.transition = 'none';
  }, { passive: true });

  wrapper.addEventListener('touchmove', (e) => {
    if (!isDragging || !e.touches.length) return;
    const delta = e.touches[0].pageX - startX;
    position = dragStartPos + delta;

    if (Math.abs(position) >= singleSetWidth) {
      position += singleSetWidth;
      dragStartPos += singleSetWidth;
    } else if (position > 0) {
      position -= singleSetWidth;
      dragStartPos -= singleSetWidth;
    }
    track.style.transform = `translateX(${position}px)`;
  }, { passive: true });

  wrapper.addEventListener('touchend', () => {
    isDragging = false;
    isPaused = false;
  });
}
