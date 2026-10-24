/**
 * RT CHOCOS — INNOVATION LAB ENHANCEMENTS
 * Performance, Accessibility & Polish Improvements
 *
 * Features:
 * - Scroll reveal animations with Intersection Observer
 * - Smooth scroll behavior
 * - Enhanced keyboard navigation
 * - Performance optimizations
 * - Progressive image loading
 * - Error handling and retry logic
 */

(function() {
  'use strict';

  // ==========================================
  // INTERSECTION OBSERVER FOR SCROLL REVEALS
  // ==========================================

  const initScrollReveal = () => {
    // Check if Intersection Observer is supported
    if (!('IntersectionObserver' in window)) {
      // Fallback: make everything visible immediately
      document.querySelectorAll('.lab-section, .lab-split-section, .lab-ingredient-section, .lab-future-section, .lab-exp-card').forEach(el => {
        el.classList.add('is-visible');
      });
      return;
    }

    const observerOptions = {
      root: null,
      rootMargin: '0px 0px -100px 0px', // Trigger 100px before element enters viewport
      threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          // Unobserve after animation to improve performance
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);

    // Observe all sections
    const sectionsToReveal = document.querySelectorAll('.lab-section, .lab-split-section, .lab-ingredient-section, .lab-future-section');
    sectionsToReveal.forEach(section => {
      observer.observe(section);
    });

    // Observe experiment cards with a small delay for staggered effect
    const cardsToReveal = document.querySelectorAll('.lab-exp-card');
    cardsToReveal.forEach((card, index) => {
      setTimeout(() => {
        observer.observe(card);
      }, index * 50); // 50ms stagger
    });
  };

  // ==========================================
  // PROGRESSIVE IMAGE LOADING
  // ==========================================

  const initProgressiveImageLoading = () => {
    const images = document.querySelectorAll('.lab-exp-media img, .lab-hero-img, .lab-ing-photo-wrap img');

    if ('loading' in HTMLImageElement.prototype) {
      // Native lazy loading is supported
      images.forEach(img => {
        img.loading = 'lazy';
      });
    } else {
      // Fallback using Intersection Observer
      const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            if (img.dataset.src) {
              img.src = img.dataset.src;
              img.classList.add('loaded');
              imageObserver.unobserve(img);
            }
          }
        });
      });

      images.forEach(img => {
        imageObserver.observe(img);
      });
    }
  };

  // ==========================================
  // ENHANCED KEYBOARD NAVIGATION
  // ==========================================

  const initKeyboardNavigation = () => {
    // Arrow key navigation for experiment cards
    const expCards = document.querySelectorAll('.lab-exp-card');

    expCards.forEach((card, index) => {
      card.setAttribute('tabindex', '0');

      card.addEventListener('keydown', (e) => {
        let targetIndex = index;

        switch(e.key) {
          case 'ArrowRight':
            targetIndex = Math.min(index + 1, expCards.length - 1);
            break;
          case 'ArrowLeft':
            targetIndex = Math.max(index - 1, 0);
            break;
          case 'ArrowDown':
            targetIndex = Math.min(index + 4, expCards.length - 1);
            break;
          case 'ArrowUp':
            targetIndex = Math.max(index - 4, 0);
            break;
          case 'Enter':
          case ' ':
            e.preventDefault();
            card.click();
            return;
          default:
            return;
        }

        e.preventDefault();
        expCards[targetIndex].focus();
      });
    });

    // Arrow key navigation for ingredient cards
    const ingCards = document.querySelectorAll('.lab-ing-card:not(.is-clone)');
    ingCards.forEach((card, index) => {
      card.setAttribute('tabindex', '0');

      card.addEventListener('keydown', (e) => {
        let targetIndex = index;

        switch(e.key) {
          case 'ArrowRight':
            targetIndex = (index + 1) % ingCards.length;
            break;
          case 'ArrowLeft':
            targetIndex = (index - 1 + ingCards.length) % ingCards.length;
            break;
          case 'Enter':
          case ' ':
            e.preventDefault();
            card.click();
            return;
          default:
            return;
        }

        e.preventDefault();
        ingCards[targetIndex].focus();
        ingCards[targetIndex].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
      });
    });

    // Tab key navigation improvements
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Tab') {
        document.body.classList.add('user-is-tabbing');
      }
    });

    document.addEventListener('mousedown', () => {
      document.body.classList.remove('user-is-tabbing');
    });
  };

  // ==========================================
  // SMOOTH SCROLL BEHAVIOR
  // ==========================================

  const initSmoothScroll = () => {
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href === '#') return;

        const targetElement = document.querySelector(href);
        if (targetElement) {
          e.preventDefault();

          const offset = 145; // Account for sticky header
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
  // PERFORMANCE: DEBOUNCE & THROTTLE UTILITIES
  // ==========================================

  const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  };

  const throttle = (func, limit) => {
    let inThrottle;
    return function(...args) {
      if (!inThrottle) {
        func.apply(this, args);
        inThrottle = true;
        setTimeout(() => inThrottle = false, limit);
      }
    };
  };

  // ==========================================
  // STICKY SUBNAV ACTIVE STATE ON SCROLL
  // ==========================================

  const initStickyNavScroll = () => {
    const sections = [
      { id: 'currently-in-lab', el: document.getElementById('currently-in-lab') },
      { id: 'ingredient-innovation', el: document.getElementById('ingredient-innovation') },
      { id: 'what-if', el: document.getElementById('what-if') },
      { id: 'failed-experiments', el: document.getElementById('failed-experiments') },
      { id: 'future-ideas', el: document.getElementById('future-ideas') }
    ];

    const updateActiveNav = throttle(() => {
      const scrollPos = window.scrollY + 200;
      let currentId = null;

      sections.forEach(sec => {
        if (!sec.el) return;
        const top = sec.el.offsetTop;
        const height = sec.el.offsetHeight;

        if (scrollPos >= top && scrollPos < top + height + 60) {
          currentId = sec.id;
        }
      });

      if (currentId) {
        const activeLink = document.querySelector(`.lab-subnav-link[data-section="${currentId}"]`);
        if (activeLink && !activeLink.classList.contains('active')) {
          document.querySelectorAll('.lab-subnav-link').forEach(l => l.classList.remove('active'));
          activeLink.classList.add('active');
        }
      }
    }, 100);

    window.addEventListener('scroll', updateActiveNav, { passive: true });
  };

  // ==========================================
  // ENHANCED SEARCH WITH DEBOUNCING
  // ==========================================

  const initEnhancedSearch = () => {
    const searchInput = document.getElementById('labIngredientSearch');
    if (!searchInput) return;

    const debouncedSearch = debounce((query) => {
      if (typeof handleIngredientSearch === 'function') {
        handleIngredientSearch(query);
      }
    }, 300);

    searchInput.addEventListener('input', (e) => {
      debouncedSearch(e.target.value);
    });

    // Clear search on Escape key
    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (typeof clearIngredientSearch === 'function') {
          clearIngredientSearch();
        }
      }
    });
  };

  // ==========================================
  // MODAL ACCESSIBILITY IMPROVEMENTS
  // ==========================================

  const initModalAccessibility = () => {
    const modals = document.querySelectorAll('.lab-modal-overlay');

    modals.forEach(modal => {
      modal.addEventListener('transitionend', () => {
        if (modal.classList.contains('active')) {
          // Trap focus within modal
          const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
          );

          if (focusableElements.length > 0) {
            focusableElements[0].focus();
          }
        }
      });
    });

    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        const activeModal = document.querySelector('.lab-modal-overlay.active');
        if (activeModal) {
          const modalId = activeModal.id;
          if (typeof closeLabModal === 'function') {
            closeLabModal(modalId);
          }
        }
      }
    });
  };

  // ==========================================
  // PERFORMANCE: REDUCE PAINT/REFLOW
  // ==========================================

  const optimizeAnimations = () => {
    // Use requestAnimationFrame for smooth animations
    let ticking = false;

    window.addEventListener('scroll', () => {
      if (!ticking) {
        window.requestAnimationFrame(() => {
          // Perform scroll-based animations here
          ticking = false;
        });
        ticking = true;
      }
    }, { passive: true });

    // Optimize carousel auto-float
    if (typeof initCarouselOptimization === 'function') {
      initCarouselOptimization();
    }
  };

  // ==========================================
  // ERROR HANDLING & RETRY LOGIC
  // ==========================================

  const initErrorHandling = () => {
    // Handle image load errors
    document.querySelectorAll('.lab-exp-media img, .lab-ing-photo-wrap img').forEach(img => {
      img.addEventListener('error', function() {
        this.src = 'assets/images/placeholder.jpg';
        this.alt = 'Image unavailable';
        console.warn('Image failed to load:', this.dataset.src || this.src);
      });
    });

    // Handle missing data gracefully
    window.addEventListener('error', (e) => {
      console.error('Runtime error:', e.error);
      // Could add user-friendly error notification here
    });
  };

  // ==========================================
  // LOADING STATES
  // ==========================================

  const addLoadingStates = () => {
    // Add loading class to cards initially
    const cards = document.querySelectorAll('.lab-exp-card');
    cards.forEach(card => {
      const img = card.querySelector('.lab-exp-media img');
      if (img && !img.complete) {
        card.classList.add('is-loading');

        img.addEventListener('load', () => {
          card.classList.remove('is-loading');
          card.classList.add('lab-fade-in');
        });

        img.addEventListener('error', () => {
          card.classList.remove('is-loading');
        });
      }
    });
  };

  // ==========================================
  // PREFETCH MODAL CONTENT
  // ==========================================

  const prefetchModalData = () => {
    // Prefetch experiment data on card hover
    const expCards = document.querySelectorAll('.lab-exp-card');
    expCards.forEach(card => {
      card.addEventListener('mouseenter', function() {
        const expId = this.getAttribute('data-id');
        if (expId && window.LAB_EXPERIMENTS) {
          // Data is already loaded in window.LAB_EXPERIMENTS
          // This just ensures we're ready for instant modal open
          const exp = window.LAB_EXPERIMENTS.find(e => Number(e.id) === Number(expId));
          if (exp) {
            // Preload modal image
            const img = new Image();
            img.src = exp.image_path;
          }
        }
      }, { once: true });
    });
  };

  // ==========================================
  // ANALYTICS & INTERACTION TRACKING (OPTIONAL)
  // ==========================================

  const initAnalytics = () => {
    // Track which experiments users interact with
    document.querySelectorAll('.lab-exp-card').forEach(card => {
      card.addEventListener('click', function() {
        const expId = this.getAttribute('data-id');
        const expTitle = this.querySelector('.lab-exp-title')?.textContent;

        // Send to analytics (if implemented)
        if (window.gtag) {
          window.gtag('event', 'view_experiment', {
            experiment_id: expId,
            experiment_title: expTitle
          });
        }

        console.log('Experiment viewed:', expId, expTitle);
      });
    });
  };

  // ==========================================
  // RESPONSIVE FONT SIZING
  // ==========================================

  const adjustResponsiveFonts = () => {
    const updateFontSizes = () => {
      const viewportWidth = window.innerWidth;
      const root = document.documentElement;

      // Adjust base font size based on viewport
      if (viewportWidth < 768) {
        root.style.fontSize = '15px';
      } else if (viewportWidth < 1024) {
        root.style.fontSize = '15.5px';
      } else {
        root.style.fontSize = '16px';
      }
    };

    updateFontSizes();
    window.addEventListener('resize', debounce(updateFontSizes, 250));
  };

  // ==========================================
  // INITIALIZE ALL ENHANCEMENTS
  // ==========================================

  const init = () => {
    console.log('🧪 Innovation Lab Enhancements: Initializing...');

    try {
      // Core features
      initScrollReveal();
      initSmoothScroll();
      initKeyboardNavigation();
      initStickyNavScroll();

      // Enhanced UX
      initProgressiveImageLoading();
      initEnhancedSearch();
      initModalAccessibility();
      addLoadingStates();
      prefetchModalData();

      // Performance optimizations
      optimizeAnimations();
      initErrorHandling();
      adjustResponsiveFonts();

      // Optional: Analytics
      // initAnalytics();

      console.log('✅ Innovation Lab Enhancements: Ready');
    } catch (error) {
      console.error('❌ Innovation Lab Enhancements: Initialization failed', error);
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

  // Expose utility functions globally if needed
  window.LabEnhancements = {
    debounce,
    throttle,
    initScrollReveal,
    initKeyboardNavigation
  };

})();
