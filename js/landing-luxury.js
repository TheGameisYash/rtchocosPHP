/**
 * ==========================================================================
 * RT CHOCOS — ULTRA-LUXURY LANDING PAGE SCRIPT (landing-luxury.js)
 * Silky 60fps parallax, 3D perspective tilt, holographic quick-peek tooltips,
 * ambient aura and scroll progress tracking — scoped exclusively to #page-home.
 * ==========================================================================
 */

(function() {
  'use strict';

  document.addEventListener('DOMContentLoaded', initLandingLuxury);

  function initLandingLuxury() {
    const pageHome = document.getElementById('page-home');
    if (!pageHome) return;

    // Detect touch / fine pointer capabilities
    const hasFinePointer = window.matchMedia('(pointer: fine)').matches;

    // ------------------------------------------------------------------------
    // 1. Scroll Progress Bar
    // ------------------------------------------------------------------------
    const progressBar = document.getElementById('landing-scroll-progress');
    let tickingScroll = false;

    function updateScrollProgress() {
      if (!progressBar) return;
      const docHeight = document.documentElement.scrollHeight - window.innerHeight;
      const scrolled = docHeight > 0 ? (window.scrollY / docHeight) * 100 : 0;
      progressBar.style.width = Math.min(100, Math.max(0, scrolled)) + '%';
      tickingScroll = false;
    }

    window.addEventListener('scroll', function() {
      if (!tickingScroll) {
        window.requestAnimationFrame(updateScrollProgress);
        tickingScroll = true;
      }
    }, { passive: true });
    updateScrollProgress();

    // ------------------------------------------------------------------------
    // 2. Ambient Cursor Glow Follower (Hero Section Only)
    // ------------------------------------------------------------------------
    const cursorGlow = document.getElementById('landing-cursor-glow');
    const heroSection = document.getElementById('hero');
    if (cursorGlow && heroSection && hasFinePointer) {
      let mouseX = window.innerWidth / 2;
      let mouseY = window.innerHeight / 2;
      let currentX = mouseX;
      let currentY = mouseY;
      let cursorActive = false;

      function deactivateCursorGlow() {
        cursorActive = false;
        cursorGlow.classList.remove('active');
      }

      heroSection.addEventListener('mouseenter', function(e) {
        cursorActive = true;
        mouseX = e.clientX;
        mouseY = e.clientY;
        currentX = mouseX;
        currentY = mouseY;
        cursorGlow.classList.add('active');
      });

      heroSection.addEventListener('mousemove', function(e) {
        cursorActive = true;
        mouseX = e.clientX;
        mouseY = e.clientY;
        if (!cursorGlow.classList.contains('active')) {
          cursorGlow.classList.add('active');
        }
      }, { passive: true });

      heroSection.addEventListener('mouseleave', deactivateCursorGlow);

      // When user scrolls down out of Hero section, deactivate immediately
      window.addEventListener('scroll', function() {
        if (cursorActive) {
          const rect = heroSection.getBoundingClientRect();
          if (rect.bottom <= 60 || rect.top >= window.innerHeight) {
            deactivateCursorGlow();
          }
        }
      }, { passive: true });

      function renderCursorGlow() {
        if (cursorActive && cursorGlow.classList.contains('active')) {
          currentX += (mouseX - currentX) * 0.15;
          currentY += (mouseY - currentY) * 0.15;
          cursorGlow.style.transform = `translate3d(${currentX.toFixed(1)}px, ${currentY.toFixed(1)}px, 0) translate(-50%, -50%)`;
        }
        window.requestAnimationFrame(renderCursorGlow);
      }
      renderCursorGlow();
    }

    // ------------------------------------------------------------------------
    // 3. Multi-Layer Botanical Scroll Parallax
    // ------------------------------------------------------------------------
    const parallaxLeaves = pageHome.querySelectorAll('.parallax-leaf');
    let tickingLeaves = false;

    function renderParallaxLeaves() {
      const scrollY = window.scrollY;
      parallaxLeaves.forEach((leaf) => {
        const speed = parseFloat(leaf.getAttribute('data-speed') || '0.15');
        const rot = parseFloat(leaf.getAttribute('data-base-rot') || '0');
        const offset = scrollY * speed;
        const sway = Math.sin(scrollY * 0.003 + rot) * 8;
        leaf.style.transform = `translate3d(0, ${offset.toFixed(1)}px, 0) rotate(${(rot + sway).toFixed(1)}deg)`;
      });
      tickingLeaves = false;
    }

    window.addEventListener('scroll', function() {
      if (parallaxLeaves.length && !tickingLeaves) {
        window.requestAnimationFrame(renderParallaxLeaves);
        tickingLeaves = true;
      }
    }, { passive: true });
    if (parallaxLeaves.length) renderParallaxLeaves();

    // ------------------------------------------------------------------------
    // 4. Hero Subtitle & Video Background Scroll Parallax
    // ------------------------------------------------------------------------
    const heroSec = document.getElementById('hero');
    const heroVideo = heroSec ? heroSec.querySelector('.hero-video-bg') : null;
    const heroContent = heroSec ? heroSec.querySelector('.split-hero-content') : null;

    if (heroSec && (heroVideo || heroContent)) {
      let heroTicking = false;
      function renderHeroParallax() {
        const scrollY = window.scrollY;
        const heroHeight = heroSec.offsetHeight || 800;
        if (scrollY <= heroHeight) {
          const progress = scrollY / heroHeight;
          if (heroVideo) {
            heroVideo.style.transform = `translateY(calc(-50% + ${(scrollY * 0.18).toFixed(1)}px))`;
          }
          if (heroContent) {
            heroContent.style.opacity = Math.max(0, 1 - progress * 1.3).toFixed(2);
            heroContent.style.transform = `translate3d(0, ${(scrollY * 0.12).toFixed(1)}px, 0)`;
          }
        }
        heroTicking = false;
      }

      window.addEventListener('scroll', function() {
        if (!heroTicking) {
          window.requestAnimationFrame(renderHeroParallax);
          heroTicking = true;
        }
      }, { passive: true });
    }

    // ------------------------------------------------------------------------
    // Ensure cursor glow is strictly inactive when mouse enters any section below hero
    if (cursorGlow) {
      const nonHeroSecs = pageHome.querySelectorAll('.chocolate-table-sec, #bean-to-bar-stepper, #featured-workshops, #flavor-wheel-sec');
      nonHeroSecs.forEach(sec => {
        sec.addEventListener('mouseenter', () => {
          cursorGlow.classList.remove('active');
        });
      });
    }

    // ------------------------------------------------------------------------
    // 5. Interactive 3D Cursor-Responsive Chocolate Table Workbench Movement
    // ------------------------------------------------------------------------
    const tableSec = pageHome.querySelector('.chocolate-table-sec');
    const tableWrapper = pageHome.querySelector('.chocolate-table-wrapper');

    if (tableSec && tableWrapper && hasFinePointer) {
      let targetRotX = 0;
      let targetRotY = 0;
      let currentRotX = 0;
      let currentRotY = 0;
      let isHoveringTable = false;
      let animFrameId = null;

      function onTableMouseMove(e) {
        const rect = tableWrapper.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        // Active across table plus generous comfortable margin
        if (x >= -40 && x <= rect.width + 40 && y >= -40 && y <= rect.height + 40) {
          isHoveringTable = true;
          const normX = (x / rect.width) * 2 - 1; // -1 to +1
          const normY = (y / rect.height) * 2 - 1; // -1 to +1
          targetRotY = normX * 6.5; // Smooth 6.5 deg max yaw
          targetRotX = -normY * 6.0; // Smooth 6.0 deg max pitch
        } else {
          isHoveringTable = false;
          targetRotX = 0;
          targetRotY = 0;
        }

        startTiltLoop();
      }

      function startTiltLoop() {
        if (!animFrameId) {
          animFrameId = window.requestAnimationFrame(renderTilt);
        }
      }

      function renderTilt() {
        // Smooth linear interpolation (lerp)
        currentRotX += (targetRotX - currentRotX) * 0.1;
        currentRotY += (targetRotY - currentRotY) * 0.1;

        const delta = Math.abs(targetRotX - currentRotX) + Math.abs(targetRotY - currentRotY);

        if (delta > 0.01 || isHoveringTable) {
          const scale = isHoveringTable ? 1.012 : 1;
          tableWrapper.style.transform = `perspective(1400px) rotateX(${currentRotX.toFixed(2)}deg) rotateY(${currentRotY.toFixed(2)}deg) scale3d(${scale}, ${scale}, 1)`;
          animFrameId = window.requestAnimationFrame(renderTilt);
        } else {
          currentRotX = 0;
          currentRotY = 0;
          tableWrapper.style.transform = 'perspective(1400px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
          animFrameId = null;
        }
      }

      tableSec.addEventListener('mousemove', onTableMouseMove, { passive: true });
      tableSec.addEventListener('mouseleave', () => {
        isHoveringTable = false;
        targetRotX = 0;
        targetRotY = 0;
        startTiltLoop();
      });
    }

    // ------------------------------------------------------------------------
    // 6. Hotspot Holographic Quick-Peek Tooltip Generator
    // ------------------------------------------------------------------------
    const hotspots = pageHome.querySelectorAll('.table-hotspot');
    const peekData = {
      'bean-to-bar': {
        badge: '🌿 Craft Process',
        title: 'Bean to Bar Craft',
        desc: 'Single-origin estate sourcing, fermentation thermodynamics & 72-hour stone conching.'
      },
      'knowledge-hub': {
        badge: '📚 Science Journal',
        title: 'Knowledge Hub',
        desc: 'Crystal polymorphism, Form V nucleation curves & bloom diagnostics.'
      },
      'chocolate-lab': {
        badge: '🧪 R&D Lab',
        title: 'Chocolate Lab',
        desc: 'AI formulation algorithms, water activity metrics & emulsion physics.'
      },
      'recipes-formulations': {
        badge: '🍫 Formulations',
        title: 'Recipes & Slabs',
        desc: 'Award-winning single origin formulas, glossy truffles & bonbon ganaches.'
      },
      'techniques': {
        badge: '👩‍🍳 Master Skills',
        title: 'Chocolatier Skills',
        desc: 'Precision marble slab tabling, airbrushing cocoa butter & glossy moulding.'
      },
      'origins-atlas': {
        badge: '🗺️ Terroir Atlas',
        title: 'Origins & Estates',
        desc: 'Explore Indian cacao estate terroirs across Kerala, Karnataka & Tamil Nadu.'
      },
      'workshops-academy': {
        badge: '🎓 Certification',
        title: 'Masterclasses',
        desc: 'Hands-on & online chocolate academy certification by Aarti Saluja Sahni.'
      }
    };

    hotspots.forEach(hotspot => {
      const onclickAttr = hotspot.getAttribute('onclick') || '';
      const match = onclickAttr.match(/openTableModal\(['"]([^'"]+)['"]\)/);
      const key = match ? match[1] : null;

      if (key && peekData[key]) {
        // Prevent duplicate insertion
        if (hotspot.querySelector('.hotspot-peek-card')) return;

        const data = peekData[key];
        const peekCard = document.createElement('div');

        // Smart directional positioning: Top pins open downwards, bottom open upwards
        const styleTop = parseFloat(hotspot.style.top || '50');
        const styleLeft = parseFloat(hotspot.style.left || '50');

        let posClass = 'peek-pos-top';
        if (styleTop < 42) {
          posClass = 'peek-pos-bottom'; // Bean to Bar (18%), Chocolate Lab (14%), Recipes (38%)
        }

        let alignClass = 'peek-align-center';
        if (styleLeft > 70) {
          alignClass = 'peek-align-right'; // Pins near right edge align inside
        } else if (styleLeft < 25) {
          alignClass = 'peek-align-left'; // Pins near left edge align inside
        }

        peekCard.className = `hotspot-peek-card ${posClass} ${alignClass}`;
        peekCard.innerHTML = `
          <span class="peek-badge">${data.badge}</span>
          <div class="peek-title">${data.title}</div>
          <div class="peek-desc">${data.desc}</div>
          <div class="peek-cta">Explore Dossier →</div>
        `;
        hotspot.appendChild(peekCard);
      }
    });

    // ------------------------------------------------------------------------
    // 7. Ambient Rotating Glow Backlight for Flavor Wheel
    // ------------------------------------------------------------------------
    const wheelSvgWrapper = pageHome.querySelector('.wheel-svg-wrapper');
    if (wheelSvgWrapper) {
      const aura = document.createElement('div');
      aura.className = 'wheel-ambient-aura';
      wheelSvgWrapper.insertBefore(aura, wheelSvgWrapper.firstChild);
    }

    // ------------------------------------------------------------------------
    // 8. Staggered Scroll Reveal for Workshop & Trust Strip Elements
    // ------------------------------------------------------------------------
    const revealTargets = pageHome.querySelectorAll('.workshop-card, .trust-col, .home-journal-card');
    if ('IntersectionObserver' in window) {
      const revealObserver = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
            obs.unobserve(entry.target);
          }
        });
      }, { threshold: 0.15 });

      revealTargets.forEach((el, idx) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(24px)';
        el.style.transition = `opacity 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) ${idx * 0.08}s, transform 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) ${idx * 0.08}s`;
        revealObserver.observe(el);
      });
    }

    // ------------------------------------------------------------------------
    // 9. Trust Pillars Animated Counter (Smooth Increment)
    // ------------------------------------------------------------------------
    const trustCard = pageHome.querySelector('.home-trust-card');
    if (trustCard && 'IntersectionObserver' in window) {
      let countStarted = false;
      const countObserver = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !countStarted) {
          countStarted = true;
          const yearsTitle = pageHome.querySelector('.trust-col:nth-child(1) .trust-stat-title');
          if (yearsTitle) {
            let count = 0;
            const target = 13;
            const timer = setInterval(() => {
              count++;
              yearsTitle.textContent = count + '+ YEARS';
              if (count >= target) clearInterval(timer);
            }, 55);
          }
          countObserver.disconnect();
        }
      }, { threshold: 0.3 });
      countObserver.observe(trustCard);
    }

    // ------------------------------------------------------------------------
    // 10. Interactive Tempering Lab (Six Crystals. Only one is worth having.)
    // ------------------------------------------------------------------------
    const chocoPillBtns = pageHome.querySelectorAll('.choco-pill-btn');
    const temperingLabSec = pageHome.querySelector('.tempering-lab-sec');

    if (chocoPillBtns.length && temperingLabSec) {
      const TEMPERING_DATA = {
        milk: {
          melt: 45,
          seed: 27,
          work: 29.5,
          meltLabel: 'MELT OUT 45°C',
          seedLabel: 'SEED 27°C',
          workLabel: 'WORK 29.5°C',
          desc: 'Milk fat is soft and disruptive: it slots between cocoa butter triglycerides and lowers the whole melting range. Everything drops roughly 2 °C, and the temper is less forgiving of overheating.'
        },
        dark: {
          melt: 50,
          seed: 28,
          work: 31.5,
          meltLabel: 'MELT OUT 50°C',
          seedLabel: 'SEED 28°C',
          workLabel: 'WORK 31.5°C',
          desc: 'Dark carries no milk fat, so the cocoa butter crystallises cleanly and tolerates the highest working temperature. Hold above 32.5 °C and Form V starts melting out – the bar will set dull and soft.'
        },
        white: {
          melt: 45,
          seed: 26,
          work: 28.5,
          meltLabel: 'MELT OUT 45°C',
          seedLabel: 'SEED 26°C',
          workLabel: 'WORK 28.5°C',
          desc: 'High cocoa butter, milk solids, and sugar without cocoa mass. Extremely delicate and scorch-sensitive. Keep melt below 45°C and working temperature strictly between 28°C and 29°C.'
        },
        ruby: {
          melt: 45,
          seed: 26.5,
          work: 29.0,
          meltLabel: 'MELT OUT 45°C',
          seedLabel: 'SEED 26.5°C',
          workLabel: 'WORK 29.0°C',
          desc: 'Naturally derived from ruby cocoa beans with citric acid nuances and zero added colorings. Sensitive to high heat and prolonged holding; strict working temperature around 29°C preserves vibrant pink hue and berry tang.'
        }
      };

      function tempToY(t) {
        // SVG coordinate system: y ranges from 24 (55°C) to 164 (20°C) -> 4px per 1°C
        return 24 + (55 - t) * 4;
      }

      function updateTemperingView(type) {
        const d = TEMPERING_DATA[type];
        if (!d) return;

        const yMelt = tempToY(d.melt);
        const ySeed = tempToY(d.seed);
        const yWork = tempToY(d.work);

        // Crisp plateau-trough-work polyline matching reference design exactly
        const pathD = `M 46 156 L 90 ${yMelt.toFixed(1)} L 140 ${yMelt.toFixed(1)} L 215 ${ySeed.toFixed(1)} L 260 ${ySeed.toFixed(1)} L 325 ${yWork.toFixed(1)} L 485 ${yWork.toFixed(1)}`;

        const linePath = document.getElementById('curveLinePath');
        if (linePath) linePath.setAttribute('d', pathD);

        // Position nodes
        const nodeMelt = document.getElementById('node-melt');
        const nodeSeed = document.getElementById('node-seed');
        const nodeWork = document.getElementById('node-work');

        if (nodeMelt) nodeMelt.setAttribute('transform', `translate(115, ${yMelt.toFixed(1)})`);
        if (nodeSeed) nodeSeed.setAttribute('transform', `translate(238, ${ySeed.toFixed(1)})`);
        if (nodeWork) nodeWork.setAttribute('transform', `translate(405, ${yWork.toFixed(1)})`);

        // Node labels
        const labelMelt = document.getElementById('label-melt');
        const labelSeed = document.getElementById('label-seed');
        const labelWork = document.getElementById('label-work');
        if (labelMelt) labelMelt.textContent = d.meltLabel;
        if (labelSeed) labelSeed.textContent = d.seedLabel;
        if (labelWork) labelWork.textContent = d.workLabel;

        // Metric boxes numbers
        const mMelt = document.getElementById('metric-melt');
        const mSeed = document.getElementById('metric-seed');
        const mWork = document.getElementById('metric-work');
        if (mMelt) mMelt.textContent = d.melt + '°';
        if (mSeed) mSeed.textContent = d.seed + '°';
        if (mWork) mWork.textContent = d.work + '°';

        // Science explanation text cross-fade
        const scienceText = document.getElementById('tempering-science-text');
        if (scienceText) {
          scienceText.style.opacity = '0';
          setTimeout(() => {
            scienceText.textContent = d.desc;
            scienceText.style.opacity = '1';
          }, 150);
        }
      }

      chocoPillBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          const type = this.getAttribute('data-type');
          if (!type) return;

          chocoPillBtns.forEach(b => {
            b.classList.remove('active');
            b.setAttribute('aria-selected', 'false');
          });

          this.classList.add('active');
          this.setAttribute('aria-selected', 'true');

          updateTemperingView(type);
        });
      });

      // Initialize default (Dark 70% matching user reference photo)
      updateTemperingView('dark');
    }
  }
})();
