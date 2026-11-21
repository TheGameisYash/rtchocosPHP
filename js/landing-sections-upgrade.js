/**
 * RT CHOCOS — LANDING PAGE SECTION ENHANCEMENTS
 * JavaScript for Trust Strip, Chocolate Table, Workshops, and other sections
 * (Hero section remains untouched)
 */

(function() {
  'use strict';

  // ==========================================
  // CONFIGURATION
  // ==========================================

  const CONFIG = {
    observerOptions: {
      threshold: 0.15,
      rootMargin: '0px 0px -80px 0px'
    }
  };

  // ==========================================
  // INTERSECTION OBSERVER FOR SCROLL REVEALS
  // ==========================================

  const initScrollReveal = () => {
    if (!('IntersectionObserver' in window)) {
      // Fallback: show everything immediately
      document.querySelectorAll('.trust-col, .table-hotspot, .workshop-card').forEach(el => {
        el.classList.add('is-visible');
      });
      document.querySelectorAll('.home-trust-strip-sec, .chocolate-table-sec, #featured-workshops, .luxury-flavor-wheel-sec, .blog-journal-sec').forEach(el => {
        el.classList.add('is-visible');
      });
      return;
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');

          // Trigger child animations for specific sections
          if (entry.target.classList.contains('home-trust-strip-sec')) {
            setTimeout(() => {
              entry.target.querySelectorAll('.trust-col').forEach((col, index) => {
                setTimeout(() => col.classList.add('is-visible'), index * 100);
              });
            }, 200);
          }

          if (entry.target.classList.contains('chocolate-table-sec')) {
            setTimeout(() => {
              entry.target.querySelectorAll('.table-hotspot').forEach((hotspot, index) => {
                setTimeout(() => hotspot.classList.add('is-visible'), index * 100);
              });
            }, 300);
          }

          if (entry.target.id === 'featured-workshops') {
            setTimeout(() => {
              entry.target.querySelectorAll('.workshop-card').forEach((card, index) => {
                setTimeout(() => card.classList.add('is-visible'), index * 150);
              });
            }, 200);
          }

          // Unobserve after animation
          observer.unobserve(entry.target);
        }
      });
    }, CONFIG.observerOptions);

    // Observe all major sections
    const sectionsToObserve = [
      '.home-trust-strip-sec',
      '.chocolate-table-sec',
      '#featured-workshops',
      '.luxury-flavor-wheel-sec',
      '.blog-journal-sec'
    ];

    sectionsToObserve.forEach(selector => {
      const element = document.querySelector(selector);
      if (element) {
        observer.observe(element);
      }
    });
  };

  // ==========================================
  // CHOCOLATE TABLE HOTSPOT ENHANCEMENTS
  // ==========================================

  const initTableHotspots = () => {
    const hotspots = document.querySelectorAll('.table-hotspot');

    hotspots.forEach((hotspot, index) => {
      // Add keyboard navigation
      hotspot.setAttribute('tabindex', '0');
      hotspot.setAttribute('role', 'button');
      hotspot.setAttribute('aria-label', `Interactive hotspot ${index + 1}`);

      // Enter/Space key support
      hotspot.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          hotspot.click();
        }

        // Arrow key navigation between hotspots
        let targetIndex = index;
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
          targetIndex = (index + 1) % hotspots.length;
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
          targetIndex = (index - 1 + hotspots.length) % hotspots.length;
        } else {
          return;
        }

        e.preventDefault();
        hotspots[targetIndex].focus();
      });

      // Add ripple effect on click
      hotspot.addEventListener('click', function(e) {
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;

        ripple.style.cssText = `
          position: absolute;
          width: ${size}px;
          height: ${size}px;
          border-radius: 50%;
          background: rgba(184, 134, 11, 0.4);
          left: ${x}px;
          top: ${y}px;
          pointer-events: none;
          animation: ripple-expand 0.6s ease-out;
        `;

        this.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
      });
    });

    // Add ripple animation CSS if not exists
    if (!document.getElementById('hotspot-ripple-styles')) {
      const style = document.createElement('style');
      style.id = 'hotspot-ripple-styles';
      style.textContent = `
        @keyframes ripple-expand {
          to {
            transform: scale(2.5);
            opacity: 0;
          }
        }
      `;
      document.head.appendChild(style);
    }
  };

  // ==========================================
  // WORKSHOP CARDS ENHANCEMENT
  // ==========================================

  const initWorkshopCards = () => {
    const cards = document.querySelectorAll('.workshop-card');

    cards.forEach(card => {
      // Add keyboard navigation
      if (card.tagName !== 'A') {
        card.setAttribute('tabindex', '0');
      }

      // Hover effect for card images
      const img = card.querySelector('img');
      if (img) {
        img.style.transition = 'transform 0.5s ease, filter 0.3s ease';
      }
    });
  };

  // ==========================================
  // TRUST COLUMNS INTERACTION
  // ==========================================

  const initTrustColumns = () => {
    const cols = document.querySelectorAll('.trust-col');

    cols.forEach(col => {
      col.setAttribute('tabindex', '0');
      col.setAttribute('role', 'article');

      // Add focus event for keyboard users
      col.addEventListener('focus', function() {
        this.style.transform = 'translateY(-6px)';
      });

      col.addEventListener('blur', function() {
        this.style.transform = '';
      });
    });
  };

  // ==========================================
  // SMOOTH ANCHOR SCROLLING
  // ==========================================

  const initSmoothScroll = () => {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href === '#' || !href) return;

        const targetElement = document.querySelector(href);
        if (targetElement) {
          e.preventDefault();

          const offset = 100; // Account for header
          const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - offset;

          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
          });

          // Update URL without jumping
          if (history.pushState) {
            history.pushState(null, null, href);
          }
        }
      });
    });
  };

  // ==========================================
  // PROGRESSIVE IMAGE LOADING
  // ==========================================

  const initProgressiveImages = () => {
    const images = document.querySelectorAll('.chocolate-table-bg-img, .workshop-card img, .journal-card img');

    if ('loading' in HTMLImageElement.prototype) {
      // Native lazy loading
      images.forEach(img => {
        img.loading = 'lazy';
      });
    } else {
      // Fallback with Intersection Observer
      const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            if (img.dataset.src) {
              img.src = img.dataset.src;
              img.classList.add('loaded');
            }
            imageObserver.unobserve(img);
          }
        });
      }, { rootMargin: '50px' });

      images.forEach(img => imageObserver.observe(img));
    }
  };

  // ==========================================
  // KEYBOARD NAVIGATION HINTS
  // ==========================================

  const initKeyboardHints = () => {
    let isTabbing = false;

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Tab') {
        isTabbing = true;
        document.body.classList.add('user-is-tabbing');
      }
    });

    document.addEventListener('mousedown', () => {
      if (isTabbing) {
        isTabbing = false;
        document.body.classList.remove('user-is-tabbing');
      }
    });

    // Add focus visible styles
    const style = document.createElement('style');
    style.textContent = `
      body:not(.user-is-tabbing) *:focus {
        outline: none;
      }
    `;
    document.head.appendChild(style);
  };

  // ==========================================
  // FLAVOR WHEEL INTERACTION HINTS
  // ==========================================

  const initFlavorWheelHints = () => {
    const wheelSec = document.querySelector('.luxury-flavor-wheel-sec');
    if (!wheelSec) return;

    const interactiveHint = wheelSec.querySelector('.wheel-interactive-hint');
    if (!interactiveHint) return;

    // Fade out hint after first interaction
    let interacted = false;
    const fadeHint = () => {
      if (!interacted) {
        interacted = true;
        interactiveHint.style.opacity = '0.3';
        setTimeout(() => {
          interactiveHint.style.opacity = '1';
        }, 3000);
      }
    };

    wheelSec.addEventListener('click', fadeHint, { once: true });
  };

  // ==========================================
  // ERROR HANDLING
  // ==========================================

  const initErrorHandling = () => {
    window.addEventListener('error', (e) => {
      console.warn('Landing sections error:', e.error);
    });

    // Handle image load errors
    document.querySelectorAll('img').forEach(img => {
      img.addEventListener('error', function() {
        this.style.opacity = '0.5';
        console.warn('Image failed to load:', this.src);
      });
    });
  };

  // ==========================================
  // PERFORMANCE: REDUCE ANIMATIONS ON SCROLL
  // ==========================================

  const optimizeScrollPerformance = () => {
    let scrollTimeout;
    let isScrolling = false;

    window.addEventListener('scroll', () => {
      if (!isScrolling) {
        isScrolling = true;
        document.body.classList.add('is-scrolling');
      }

      clearTimeout(scrollTimeout);
      scrollTimeout = setTimeout(() => {
        isScrolling = false;
        document.body.classList.remove('is-scrolling');
      }, 150);
    }, { passive: true });

    // Reduce animations during scroll
    const style = document.createElement('style');
    style.textContent = `
      body.is-scrolling .trust-col:hover,
      body.is-scrolling .workshop-card:hover {
        transform: none !important;
      }
    `;
    document.head.appendChild(style);
  };

  // ==========================================
  // INITIALIZE ALL ENHANCEMENTS
  // ==========================================

  const init = () => {
    console.log('🎨 Landing Sections Enhancement: Initializing...');

    try {
      // Core features
      initScrollReveal();
      initSmoothScroll();
      initProgressiveImages();

      // Interactive enhancements
      initTableHotspots();
      initWorkshopCards();
      initTrustColumns();
      initFlavorWheelHints();

      // Accessibility
      initKeyboardHints();

      // Performance
      optimizeScrollPerformance();
      initErrorHandling();

      console.log('✅ Landing Sections Enhancement: Ready');
    } catch (error) {
      console.error('❌ Landing Sections Enhancement: Initialization failed', error);
    }
  };

  // ==========================================
  // START WHEN DOM IS READY
  // ==========================================

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Expose for debugging if needed
  window.LandingSectionsEnhancement = {
    reinit: init
  };

})();
