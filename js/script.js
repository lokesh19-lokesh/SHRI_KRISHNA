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
  initCountUpStats();
  initTestimonialFilters();
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
   6. CLIENT-SIDE FORM VALIDATION & PHP BACKEND SUBMISSION
   -------------------------------------------------------------------------- */
function initFormValidation() {
  const forms = document.querySelectorAll('form.needs-validation, #appointmentForm, #contactInquiryForm, #lpAppointmentForm');

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
        // Store original button HTML on data attribute if not already stored
        if (!submitBtn.dataset.originalHtml) {
          submitBtn.dataset.originalHtml = submitBtn.innerHTML;
        }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...';

        const formData = new FormData(form);
        let actionUrl = form.getAttribute('action') || 'https://formsubmit.co/shrikrishnadental2@gmail.com';
        
        // Use FormSubmit AJAX endpoint for seamless in-page dispatch
        if (actionUrl.includes('formsubmit.co') && !actionUrl.includes('/ajax/')) {
          actionUrl = actionUrl.replace('formsubmit.co/', 'formsubmit.co/ajax/');
        }

        // Submit via AJAX Fetch
        fetch(actionUrl, {
          method: 'POST',
          body: formData,
          headers: {
            'Accept': 'application/json'
          }
        })
        .then((response) => response.json().catch(() => ({ success: true })))
        .then((data) => {
          // If the form has an in-page feedback container and wants to stay on page
          const inPageFeedback = form.querySelector('#lpFormFeedback');
          if (inPageFeedback && form.id === 'lpAppointmentForm') {
            inPageFeedback.classList.remove('d-none');
            form.reset();
            form.classList.remove('was-validated');
            if (submitBtn && submitBtn.dataset.originalHtml) {
              submitBtn.disabled = false;
              submitBtn.innerHTML = submitBtn.dataset.originalHtml;
            }
            setTimeout(() => {
              window.location.href = 'thank-you.html';
            }, 800);
          } else {
            // Redirect to thank you page
            window.location.href = 'thank-you.html';
          }
        })
        .catch((err) => {
          console.warn('AJAX fetch encountered issue, submitting form natively:', err);
          // Fallback to direct native form submit
          form.submit();
        });
      },
      false
    );
  });

  // Handle browser Back button (BFCache) to instantly reset form submit buttons
  window.addEventListener('pageshow', (event) => {
    forms.forEach((form) => {
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn && submitBtn.dataset.originalHtml) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = submitBtn.dataset.originalHtml;
      }
      form.classList.remove('was-validated');
    });
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

/* --------------------------------------------------------------------------
   9. COUNT-UP NUMERICAL ANIMATIONS
   -------------------------------------------------------------------------- */
function initCountUpStats() {
  const statElements = document.querySelectorAll('.count-up-stat');
  if (!statElements.length) return;

  const animateCount = (el) => {
    const target = parseInt(el.getAttribute('data-target'), 10);
    const suffix = el.getAttribute('data-suffix') || '';
    if (isNaN(target)) return;

    const duration = 1800; // 1.8 seconds smooth animation
    const startTime = performance.now();

    const updateCounter = (currentTime) => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      // Smooth deceleration curve (easeOutCubic)
      const easeOut = 1 - Math.pow(1 - progress, 3);
      const currentVal = Math.floor(easeOut * target);

      el.textContent = currentVal.toLocaleString() + suffix;

      if (progress < 1) {
        requestAnimationFrame(updateCounter);
      } else {
        el.textContent = target.toLocaleString() + suffix;
      }
    };

    requestAnimationFrame(updateCounter);
  };

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCount(entry.target);
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.2 });

    statElements.forEach((el) => observer.observe(el));
  } else {
    statElements.forEach((el) => animateCount(el));
  }
}

/* --------------------------------------------------------------------------
   10. TESTIMONIALS CATEGORY FILTERING & EXPANSION
   -------------------------------------------------------------------------- */
function initTestimonialFilters() {
  const filterBtns = document.querySelectorAll('.filter-tab-btn');
  const cards = document.querySelectorAll('.testimonial-filter-item');

  if (!filterBtns.length || !cards.length) return;

  filterBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      filterBtns.forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');

      const filterValue = btn.getAttribute('data-filter');

      cards.forEach((card) => {
        const category = card.getAttribute('data-category');
        if (filterValue === 'all' || category === filterValue || (category && category.includes(filterValue))) {
          card.style.display = 'block';
          setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
          }, 50);
        } else {
          card.style.opacity = '0';
          card.style.transform = 'translateY(15px)';
          setTimeout(() => {
            card.style.display = 'none';
          }, 250);
        }
      });
    });
  });

  // Testimonial readmore toggle if present
  document.querySelectorAll('.testimonial-readmore-toggle').forEach((toggleBtn) => {
    toggleBtn.addEventListener('click', () => {
      const parent = toggleBtn.closest('.testimonial-grid-card') || toggleBtn.closest('.testimonial-card');
      if (!parent) return;
      const textEl = parent.querySelector('.testimonial-text-full');
      const shortEl = parent.querySelector('.testimonial-text-short');
      if (textEl && shortEl) {
        if (textEl.style.display === 'none' || !textEl.style.display) {
          textEl.style.display = 'block';
          shortEl.style.display = 'none';
          toggleBtn.textContent = 'Show less';
        } else {
          textEl.style.display = 'none';
          shortEl.style.display = 'block';
          toggleBtn.textContent = 'Read more';
        }
      }
    });
  });
}

