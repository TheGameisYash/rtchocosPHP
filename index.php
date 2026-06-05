<?php
  $pageTitle = "RT Chocos — India's Chocolate Blog | Bean to Bar Learning";
  $pageDescription = "India's chocolate blog for makers, learners, and enthusiasts. Bean-to-bar making, cocoa science, recipes, and workshops.";
  $pathPrefix = "";
  include $pathPrefix . 'includes/header.php';
?>

<!-- --- HOME PAGE --- -->
<div id="page-home" class="page active" style="margin-top:80px;">

  <!-- Hero -->
  <section id="hero">
    <div class="deco-circle-1"></div>
    <div class="deco-circle-2"></div>
    <div class="deco-radial"></div>
    <div class="hero-content">
      <h1 class="fade-up" style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:clamp(52px,7vw,88px);line-height:1.05;font-weight:700;letter-spacing:0.01em;">The Cacao Journal</h1>
      <p class="fade-up-d1" style="font-family:'Cormorant Garamond',serif;font-size:clamp(22px,2.8vw,36px);font-weight:600;line-height:1.25;color:#ffffff;">From Cacao farm to Commercial Kitchen- Every story worth knowing about Chocolate</p>
      <div class="hero-btns fade-up-d2">
        <a href="workshops.php" class="btn-hero-primary">Explore Workshops</a>
        <a href="blog.php" class="btn-hero-outline">Read the Blog</a>
      </div>
    </div>
  </section>

  <!-- Credibility Strip -->
  <div id="cred-strip">
    <div class="cred-item"><div class="cred-num">10+</div><div class="cred-label">Years of Experience</div></div>
    <div class="cred-item"><div class="cred-num">2,000+</div><div class="cred-label">Students Trained</div></div>
    <div class="cred-item"><div class="cred-num">50+</div><div class="cred-label">Workshops Conducted</div></div>
    <div class="cred-item"><div class="cred-num">100%</div><div class="cred-label">Bean-to-Bar Crafted</div></div>
  </div>

  <!-- Why RT Chocos -->
  <section id="why-us">
    <div class="section" style="text-align:center;">
      <div class="section-label">Why RT Chocos</div>
      <h2 class="section-title">Craftsmanship Meets Education</h2>
      <div class="divider" style="margin:20px auto;"></div>
      <p class="section-subtitle" style="margin:0 auto 48px;text-align:center;">We don't just make chocolate — we teach you the science, art and business behind every bar.</p>
      <div class="why-grid">
        <div class="why-card">
          <div class="why-card-img-wrapper">
            <img src="assets/cocoabeans.png.jpg" alt="Bean-to-Bar">
          </div>
          <div class="why-card-text">
            <h4>Bean-to-Bar Expertise</h4>
            <p>Direct cacao procurement from Kerala &amp; Karnataka. We control every step — from roast to wrap.</p>
          </div>
        </div>
        <div class="why-card">
          <div class="why-card-img-wrapper">
            <img src="assets/temepring.jpg" alt="Science-First Approach">
          </div>
          <div class="why-card-text">
            <h4>Science-First Approach</h4>
            <p>Tempering curves, water activity, crystal polymorphism — we teach the why, not just the how.</p>
          </div>
        </div>
        <div class="why-card">
          <div class="why-card-img-wrapper">
            <img src="assets/bonbons.png" alt="10+ Years Teaching">
          </div>
          <div class="why-card-text">
            <h4>10+ Years Teaching</h4>
            <p>From curious home bakers to aspiring entrepreneurs, our workshops transform skill and confidence.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured Workshops -->
  <section style="background:var(--green-50);">
    <div class="section">
      <div class="workshops-section-header">
        <p class="section-label" style="margin-bottom:12px;">Learn With Us</p>
        <h2 class="section-title">Workshops Coming Soon</h2>
        <div class="divider"></div>
        <p style="max-width: 540px; font-size: 16px; line-height: 1.75; color: var(--brown-light); margin-top: 18px; font-weight: 300; font-style: italic;">
          I'm currently designing a collection of premium chocolate workshops and learning experiences. This space will be updated soon — registrations may open anytime.
        </p>
      </div>
      
      <div class="card" style="max-width: 680px; margin: 40px auto;">
        <div style="height: 200px; background: linear-gradient(160deg, #f9f5ec 0%, #f0ece2 100%); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
          <span style="position: absolute; top: 14px; right: 14px; font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; padding: 5px 12px; border-radius: 3px; background: var(--green-700); color: #fff; z-index: 10;">Coming Soon</span>
          <img src="assets/workshop.png.png" alt="Workshop" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <div style="padding: 36px 32px 32px; text-align: center;">
          <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.25rem; font-weight: 500; color: var(--brown); margin-bottom: 12px;">Something Special is Coming</h3>
          <p style="font-size: 14px; font-weight: 300; line-height: 1.75; color: var(--brown-light); max-width: 420px; margin: 0 auto;">New chocolate workshops, masterclasses and tasting sessions are currently being prepared.</p>
        </div>
        <div style="display: block; width: 100%; padding: 16px; font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; text-align: center; border: none; border-radius: 0 0 6px 6px; background: var(--green-700); color: rgba(255,255,255,0.45); cursor: default; pointer-events: none; user-select: none;">Coming Soon</div>
      </div>

      <p style="text-align: center; font-size: 13px; font-weight: 300; font-style: italic; color: var(--brown-light);">Check back soon or follow along for upcoming workshop announcements.</p>
    </div>
  </section>


  <!-- Interactive Flavor Wheel Section -->
  <section id="flavor-wheel-sec">
    <div class="wheel-layout">
      <!-- Title Column -->
      <div class="wheel-title-col">
        <h2><span>Chocolate</span>Flavor Wheel</h2>
        <div class="gold-divider"></div>
        <p>Explore the intricate dimensions of bean-to-bar chocolate. Click on the main sectors of the wheel or the cards on the right to discover how cacao origin, farm processing, and taste profiles shape the final bar's character.</p>
      </div>

      <!-- Wheel Column -->
      <div class="wheel-svg-col">
        <div class="wheel-svg-wrapper">
          <svg class="wheel-svg" viewBox="0 0 500 500" id="interactive-wheel">
            <!-- Dynamic SVG content will be injected here by JS -->
          </svg>
        </div>
      </div>

      <!-- Details Column -->
      <div class="wheel-details-col">
        <!-- Flavor Notes Card -->
        <div class="wheel-detail-card" data-sector="flavor" id="card-flavor">
          <div class="card-header-row">
            <div class="card-icon-container">
              <!-- Inline SVG cup icon -->
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ff5252" stroke-width="2">
                <path d="M18 8h1a4 4 0 0 1 0 8h-1" />
                <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" />
                <line x1="6" y1="1" x2="6" y2="4" />
                <line x1="10" y1="1" x2="10" y2="4" />
                <line x1="14" y1="1" x2="14" y2="4" />
              </svg>
            </div>
            <span class="card-title-text">1. Flavor Notes</span>
          </div>
          <p>Aromas and flavors derived from the ingredients, soil, climate (terroir), and natural chemical compounds in the cacao beans.</p>
        </div>

        <!-- Process Card -->
        <div class="wheel-detail-card" data-sector="process" id="card-process">
          <div class="card-header-row">
            <div class="card-icon-container">
              <!-- Inline SVG gear icon -->
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f39c12" stroke-width="2">
                <circle cx="12" cy="12" r="3" />
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
              </svg>
            </div>
            <span class="card-title-text">2. Process</span>
          </div>
          <p>The crucial post-harvest and production steps—fermenting, drying, roasting, conching, and tempering—that shape chocolate's character.</p>
        </div>

        <!-- Taste Profile Card -->
        <div class="wheel-detail-card" data-sector="taste" id="card-taste">
          <div class="card-header-row">
            <div class="card-icon-container">
              <!-- Inline SVG tongue icon -->
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#82c91e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 10h20" />
                <path d="M21.5 10c-.5 5-4.5 9-9.5 9s-9-4-9-9" />
                <path d="M12 10v9" />
                <path d="M12 14c1.5 0 2.5.5 2.5 1" />
              </svg>
            </div>
            <span class="card-title-text">3. Taste Profile</span>
          </div>
          <p>The tactile and basic taste sensations perceived on the palate—sweetness, acidity, bitterness, saltiness, melt rate, and texture.</p>
        </div>
      </div>

      <!-- CTA Button beneath -->
      <div class="wheel-cta-container">
        <a href="blog.php" class="btn-outline" style="text-decoration:none; background: rgba(38,15,6,0.35); backdrop-filter: blur(2px);">Read Latest Articles</a>
      </div>
    </div>

    <!-- Inline Script for interactive wheel logic -->
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        const cx = 250;
        const cy = 250;
        const R0 = 62; // Center hub radius
        const R1 = 62;
        const R2 = 138; // Main sector outer radius
        const R3 = 220; // Subsector outer radius
        
        const WHEEL_DATA = [
          {
            id: "flavor",
            label: "1. FLAVOR NOTES",
            color: "#6e1d28", // Rich deep red-brown
            accentColor: "#b82e46",
            textLight: "#ffd1d6",
            startAngle: 180,
            endAngle: 300,
            icon: `<path d="M-6 -2c0-3.3 2.7-6 6-6s6 2.7 6 6v2c0 2.2-1.8 4-4 4h-4c-2.2 0-4-1.8-4-4v-2zm12 6h2v-2h-2v2zM0 -14v2M-3 -13v1.5M3 -13v1.5" stroke="#ff8797" fill="none" stroke-width="1.2"/>`,
            subsectors: [
              { label: "Earthy", startAngle: 180, endAngle: 200, icon: `<path d="M0 -6 C4 -2 4 4 0 6 C-4 4 -4 -2 0 -6 Z M0 -6 L0 6" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Spicy", startAngle: 200, endAngle: 220, icon: `<path d="M0 -6 L1.5 -2 L6 -2 L2.5 1 L4 5 L0 2.5 L-4 5 L-2.5 1 L-6 -2 L-1.5 -2 Z" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Sweet", startAngle: 220, endAngle: 240, icon: `<path d="M-5 -5 L5 5 M-5 5 L5 -5 M-2.5 0 A 2.5 2.5 0 1 0 2.5 0 A 2.5 2.5 0 1 0 -2.5 0 Z" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Nutty", startAngle: 240, endAngle: 260, icon: `<path d="M-3 -2 C-3 2 3 2 3 -2 C3 -4 -3 -4 -3 -2 Z M-3 -2 Q 0 -6 3 -2" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Floral", startAngle: 260, endAngle: 280, icon: `<circle cx="0" cy="0" r="2" fill="#f6f2ea"/><circle cx="0" cy="-4" r="1.8" fill="none" stroke="#f6f2ea" stroke-width="1.2"/><circle cx="4" cy="0" r="1.8" fill="none" stroke="#f6f2ea" stroke-width="1.2"/><circle cx="0" cy="4" r="1.8" fill="none" stroke="#f6f2ea" stroke-width="1.2"/><circle cx="-4" cy="0" r="1.8" fill="none" stroke="#f6f2ea" stroke-width="1.2"/>` },
              { label: "Fruity", startAngle: 280, endAngle: 300, icon: `<path d="M-1.5 -4 A 2.5 2.5 0 1 0 -1.5 1 A 2.5 2.5 0 1 0 -1.5 -4 Z M1.5 -1 A 2.5 2.5 0 1 0 1.5 4 A 2.5 2.5 0 1 0 1.5 -1 Z M-1.5 -1 Q 0 -6 3 -5" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` }
            ]
          },
          {
            id: "process",
            label: "2. PROCESS",
            color: "#843d0e", // Rich deep orange-brown
            accentColor: "#c85a17",
            textLight: "#ffe0cc",
            startAngle: 300,
            endAngle: 420,
            icon: `<path d="M-3 0 a3 3 0 1 0 6 0 a3 3 0 1 0 -6 0 M7 0 a1.6 1.6 0 0 0 .3 1.8 l.1.1 a2 2 0 1 1 -2.8 2.8 l-.1-.1 a1.6 1.6 0 0 0 -1.8-.3 a1.6 1.6 0 0 0 -1 1.5 v.1 a2 2 0 0 1 -4 0 v-.1 a1.6 1.6 0 0 0 -1-1.5 a1.6 1.6 0 0 0 -1.8.3 l-.1.1 a2 2 0 1 1 -2.8 -2.8 l.1-.1 a1.6 1.6 0 0 0 .3 -1.8 a1.6 1.6 0 0 0 -1.5 -1 h-.1 a2 2 0 0 1 0 -4 h.1 a1.6 1.6 0 0 0 1.5 -1 a1.6 1.6 0 0 0 -.3 -1.8 l-.1-.1 a2 2 0 1 1 2.8 -2.8 l.1.1 a1.6 1.6 0 0 0 1.8 .3 a1.6 1.6 0 0 0 1 -1.5 v-.1 a2 2 0 0 1 4 0 v.1 a1.6 1.6 0 0 0 1 1.5 a1.6 1.6 0 0 0 1.8 -.3 l.1-.1 a2 2 0 1 1 2.8 2.8 l-.1.1 a1.6 1.6 0 0 0 -.3 1.8 a1.6 1.6 0 0 0 1.5 1 h.1 a2 2 0 0 1 0 4 h-.1 a1.6 1.6 0 0 0 -1.5 1 z" stroke="#ffb88c" fill="none" stroke-width="1.2"/>`,
            subsectors: [
              { label: "Roasted", startAngle: 300, endAngle: 324, icon: `<path d="M0 5 C-3.5 5 -4 2.5 -2.5 0 C-3 -1.5 -1.5 -5 0 -7 C1.5 -5 3 -1.5 2.5 0 C4 2.5 3.5 5 0 5 Z" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Fermented", startAngle: 324, endAngle: 348, icon: `<path d="M-2.5 -5 H2.5 V-3 H-2.5 Z M-3 -3 H3 V4 C3 5 2.5 5.5 -3 5.5 H-3 Z" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Dried", startAngle: 348, endAngle: 372, icon: `<circle cx="0" cy="0" r="3" stroke="#f6f2ea" fill="none" stroke-width="1.2"/><path d="M0 -5 V-7 M0 5 V7 M-5 0 H-7 M5 0 H7" stroke="#f6f2ea" stroke-width="1.2"/>` },
              { label: "Conched", startAngle: 372, endAngle: 396, icon: `<path d="M-4 4 V-4 L-1.5 -1.5 V-4 L1 -1.5 V-4 L4 -0.5 V4 Z M-1.5 4 V1.5 H1.5 V4" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Tempered", startAngle: 396, endAngle: 420, icon: `<path d="M-1.5 -6 H1.5 V2.5 A 2.5 2.5 0 1 1 -1.5 2.5 Z M0 -3.5 V1" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` }
            ]
          },
          {
            id: "taste",
            label: "3. TASTE PROFILE",
            color: "#2a4224", // Rich deep green-brown
            accentColor: "#476b3c",
            textLight: "#d0ffd0",
            startAngle: 60,
            endAngle: 180,
            icon: `<path d="M-6 0c0 0 3 3 6 3s6-3 6-3M-5 0c0 4 2.5 7 5 7s5-3 5-7" stroke="#a9e39a" fill="none" stroke-width="1.2"/>`,
            subsectors: [
              { label: "Smooth", startAngle: 60, endAngle: 84, icon: `<path d="M-5 -1.5 Q-2.5 -4 0 -1.5 T5 -1.5 M-5 1.5 Q-2.5 -1 0 1.5 T5 1.5" stroke="#f6f2ea" fill="none" stroke-width="1.2" stroke-linecap="round"/>` },
              { label: "Creamy", startAngle: 84, endAngle: 108, icon: `<path d="M0 -6 C3 -2.5 4 1 2.5 3.5 C0 6 -2.5 6 -2.5 3.5 C-4 1 -3 -2.5 0 -6 Z" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Salty", startAngle: 108, endAngle: 132, icon: `<path d="M-2.5 -3.5 H2.5 V5 H-2.5 Z M-1.5 -5 H1.5 V-3.5 H-1.5 Z M-1 -1 H1 M-1 1.5 H1" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Sour", startAngle: 132, endAngle: 156, icon: `<path d="M0 -5 A 5 5 0 1 0 0 5 A 5 5 0 1 0 0 -5 Z M0 0 L4 0 M0 0 L-2 -3.5 M0 0 L-2 3.5" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Other", startAngle: 156, endAngle: 180, icon: `<circle cx="-4" cy="0" r="1.2" fill="#f6f2ea"/><circle cx="0" cy="0" r="1.2" fill="#f6f2ea"/><circle cx="4" cy="0" r="1.2" fill="#f6f2ea"/>` }
            ]
          }
        ];

        const svg = document.getElementById("interactive-wheel");
        let activeSectorId = null;
        let gRotation = 0;

        function polarToCartesian(centerX, centerY, radius, angleInDegrees) {
          const radians = (angleInDegrees * Math.PI) / 180.0;
          return {
            x: centerX + radius * Math.cos(radians),
            y: centerY + radius * Math.sin(radians)
          };
        }

        function getSectorPath(x, y, r1, r2, startAngle, endAngle) {
          const start = polarToCartesian(x, y, r2, startAngle);
          const end = polarToCartesian(x, y, r2, endAngle);
          const startInner = polarToCartesian(x, y, r1, endAngle);
          const endInner = polarToCartesian(x, y, r1, startAngle);
          const largeArc = (endAngle - startAngle) > 180 ? 1 : 0;
          return `M ${start.x} ${start.y} A ${r2} ${r2} 0 ${largeArc} 1 ${end.x} ${end.y} L ${startInner.x} ${startInner.y} A ${r1} ${r1} 0 ${largeArc} 0 ${endInner.x} ${endInner.y} Z`;
        }

        function getArcPath(x, y, r, startAngle, endAngle, isCounterClockwise) {
          const start = polarToCartesian(x, y, r, startAngle);
          const end = polarToCartesian(x, y, r, endAngle);
          const sweep = isCounterClockwise ? 0 : 1;
          return `M ${start.x} ${start.y} A ${r} ${r} 0 0 ${sweep} ${end.x} ${end.y}`;
        }

        // Generate the SVG contents dynamically
        let svgContent = "";
        
        svgContent += `
          <defs>
            <filter id="glow-flavor" x="-20%" y="-20%" width="140%" height="140%">
              <feGaussianBlur stdDeviation="8" result="blur" />
              <feComposite in="SourceGraphic" in2="blur" operator="over" />
            </filter>
            <filter id="glow-process" x="-20%" y="-20%" width="140%" height="140%">
              <feGaussianBlur stdDeviation="8" result="blur" />
              <feComposite in="SourceGraphic" in2="blur" operator="over" />
            </filter>
            <filter id="glow-taste" x="-20%" y="-20%" width="140%" height="140%">
              <feGaussianBlur stdDeviation="8" result="blur" />
              <feComposite in="SourceGraphic" in2="blur" operator="over" />
            </filter>
          </defs>
        `;

        svgContent += `<g id="rotating-group">`;

        WHEEL_DATA.forEach((category) => {
          // --- 1. MAIN SECTOR ---
          const mainPath = getSectorPath(cx, cy, R1, R2, category.startAngle, category.endAngle);
          svgContent += `
            <path class="wheel-sector wheel-main-sector" 
                  d="${mainPath}" 
                  fill="${category.color}" 
                  data-category="${category.id}"
                  style="color: ${category.accentColor};"
            />
          `;

          const midAngle = (category.startAngle + category.endAngle) / 2;
          const textR = (R1 + R2) / 2 - 3;
          const textSpan = 45;
          
          // Always draw clockwise so text is right-side up when rotated to the top (12 o'clock)
          const textPathD = getArcPath(cx, cy, textR, midAngle - textSpan, midAngle + textSpan, false);

          const textPathId = `textpath-${category.id}`;
          svgContent += `
            <path id="${textPathId}" d="${textPathD}" fill="none" stroke="none" />
            <text class="wheel-label-text" fill="${category.textLight}">
              <textPath href="#${textPathId}" startOffset="50%" text-anchor="middle">
                ${category.label}
              </textPath>
            </text>
          `;

          const iconR = (R1 + R2) / 2 - 22;
          const iconPos = polarToCartesian(cx, cy, iconR, midAngle);
          const iconRot = midAngle + 90;
          svgContent += `
            <g transform="translate(${iconPos.x}, ${iconPos.y}) rotate(${iconRot})">
              ${category.icon}
            </g>
          `;

          // --- 2. SUB-SECTORS (OUTER RING) ---
          category.subsectors.forEach((sub, subIdx) => {
            const subPath = getSectorPath(cx, cy, R2, R3, sub.startAngle, sub.endAngle);
            svgContent += `
              <path class="wheel-sector wheel-sub-sector" 
                    d="${subPath}" 
                    fill="${category.color}" 
                    opacity="0.85" 
                    data-category="${category.id}"
                    data-sub="${sub.label}"
                    style="color: ${category.accentColor}; filter: brightness(${1.0 + (subIdx - 2) * 0.08});"
              />
            `;

            const subMidAngle = (sub.startAngle + sub.endAngle) / 2;
            const subNormMid = (subMidAngle % 360 + 360) % 360;
            
            // Position the text tangentially in the middle of the subsector
            const subTextR = R2 + 22; // Center of sub-sector text (approx 160)
            const textPos = polarToCartesian(cx, cy, subTextR, subMidAngle);
            
            let rotateAngle = subMidAngle + 90;
            if (subNormMid > 0 && subNormMid < 180) {
              rotateAngle = subMidAngle - 90;
            }

            svgContent += `
              <text class="wheel-sub-text" 
                    x="${textPos.x}" 
                    y="${textPos.y}" 
                    transform="rotate(${rotateAngle}, ${textPos.x}, ${textPos.y})" 
                    text-anchor="middle" 
                    dominant-baseline="central" 
                    fill="#f6f2ea">
                ${sub.label}
              </text>
            `;

            const subIconR = R2 + 56; // Position the icon near the outer edge (approx 194)
            const subIconPos = polarToCartesian(cx, cy, subIconR, subMidAngle);
            const subIconRot = subMidAngle + 90;
            svgContent += `
              <g transform="translate(${subIconPos.x}, ${subIconPos.y}) rotate(${subIconRot})">
                ${sub.icon}
              </g>
            `;
          });
        });

        svgContent += `</g>`;

        // Add static central CACAO ORIGIN hub
        svgContent += `
          <g id="center-hub-group">
            <circle class="wheel-center-hub" cx="${cx}" cy="${cy}" r="${R0}" />
            <g transform="translate(${cx}, ${cy - 12}) scale(0.75)">
              <path d="M0 -22 C12 -12 12 12 0 22 C-12 12 -12 -12 0 -22 Z" stroke="#c7a66a" fill="none" stroke-width="1.8"/>
              <path d="M0 -22 V22" stroke="#c7a66a" fill="none" stroke-width="1.2"/>
              <path d="M-4 -18 C4 -10 4 10 -4 18" stroke="#c7a66a" fill="none" stroke-width="1.2"/>
              <path d="M4 -18 C-4 -10 -4 10 4 18" stroke="#c7a66a" fill="none" stroke-width="1.2"/>
            </g>
            <text class="wheel-center-hub-text" x="${cx}" y="${cy + 14}">Cacao</text>
            <text class="wheel-center-hub-subtext" x="${cx}" y="${cy + 32}">Origin</text>
          </g>
        `;

        svg.innerHTML = svgContent;

        const rotatingGroup = document.getElementById("rotating-group");
        const centerHub = document.getElementById("center-hub-group");
        const sectors = document.querySelectorAll(".wheel-sector");
        const cards = document.querySelectorAll(".wheel-detail-card");

        const rotationMap = {
          "flavor": 30,
          "process": -90,
          "taste": 150
        };

        function setFocus(sectorId) {
          if (activeSectorId === sectorId) return;
          
          activeSectorId = sectorId;

          if (sectorId) {
            const targetRotation = rotationMap[sectorId];
            const currentNorm = ((gRotation % 360) + 360) % 360;
            const targetNorm = ((targetRotation % 360) + 360) % 360;
            
            let diff = targetNorm - currentNorm;
            if (diff > 180) diff -= 360;
            if (diff < -180) diff += 360;
            
            gRotation += diff;
            rotatingGroup.style.transform = `rotate(${gRotation}deg)`;
            svg.classList.add("has-focus");
          } else {
            gRotation = 0;
            rotatingGroup.style.transform = `rotate(0deg)`;
            svg.classList.remove("has-focus");
          }

          sectors.forEach(sec => {
            if (!sectorId) {
              sec.classList.remove("focused");
            } else if (sec.getAttribute("data-category") === sectorId) {
              sec.classList.add("focused");
            } else {
              sec.classList.remove("focused");
            }
          });

          cards.forEach(card => {
            const cardSector = card.getAttribute("data-sector");
            card.classList.remove("active-flavor", "active-process", "active-taste");
            
            if (cardSector === sectorId) {
              card.classList.add(`active-${cardSector}`);
            }
          });
        }

        sectors.forEach(sec => {
          sec.addEventListener("click", function(e) {
            e.stopPropagation();
            const category = this.getAttribute("data-category");
            setFocus(category);
          });
        });

        cards.forEach(card => {
          card.addEventListener("click", function() {
            const sectorId = this.getAttribute("data-sector");
            if (activeSectorId === sectorId) {
              setFocus(null);
            } else {
              setFocus(sectorId);
            }
          });
        });

        centerHub.addEventListener("click", function(e) {
          e.stopPropagation();
          setFocus(null);
        });

        document.addEventListener("click", function(e) {
          const wheelSection = document.getElementById("flavor-wheel-sec");
          if (wheelSection && !wheelSection.contains(e.target)) {
            setFocus(null);
          }
        });
      });
    </script>
  </section>


  <!-- Gallery Preview -->
  <section style="background:var(--cream);text-align:center;">
    <div class="section">
    <div class="section-label">Our World</div>
    <h2 class="section-title">Gallery</h2>
    <div class="divider" style="margin:20px auto 40px;"></div>
    <div class="gallery-grid">
      <div class="gallery-item">
        <div class="gallery-item-bg" style="background-image: url('assets/cocoabeans.png.jpg');"></div>
        <div class="gallery-item-overlay"></div>
        <span class="gallery-item-text">Roasting</span>
      </div>
      <div class="gallery-item">
        <div class="gallery-item-bg" style="background-image: url('assets/temepring.jpg');"></div>
        <div class="gallery-item-overlay"></div>
        <span class="gallery-item-text">Tempering</span>
      </div>
      <div class="gallery-item">
        <div class="gallery-item-bg" style="background-image: url('assets/bonbons.png');"></div>
        <div class="gallery-item-overlay"></div>
        <span class="gallery-item-text">Moulding</span>
      </div>
      <div class="gallery-item">
        <div class="gallery-item-bg" style="background-image: url('assets/workshop.jpg');"></div>
        <div class="gallery-item-overlay"></div>
        <span class="gallery-item-text">Workshop</span>
      </div>
      <div class="gallery-item">
        <div class="gallery-item-bg" style="background-image: url('assets/almondbutterphoto.jpg');"></div>
        <div class="gallery-item-overlay"></div>
        <span class="gallery-item-text">Finished Bars</span>
      </div>
      <div class="gallery-item">
        <div class="gallery-item-bg" style="background-image: url('assets/limechillitrufflesphoto.jpeg');"></div>
        <div class="gallery-item-overlay"></div>
        <span class="gallery-item-text">Packaging</span>
      </div>
    </div>
    <a href="gallery.php" class="btn-outline-dark" style="margin-top:32px; text-decoration:none;">View Full Gallery &rarr;</a>
    </div>
  </section>

  <!-- Newsletter CTA -->
  <section id="newsletter-section">
    <div class="inner">
      <div class="sub-label">Stay Connected</div>
      <h2>The Chocolate Letter</h2>
      <p>Weekly recipes, science deep-dives, workshop announcements and exclusive offers.</p>
      <div class="newsletter-row">
        <input class="newsletter-input" placeholder="Enter your email" type="email" />
        <button class="btn-gold">Subscribe</button>
      </div>
    </div>
  </section>

</div><!-- end home -->

<!-- --- ABOUT PAGE --- -->

<?php
  include $pathPrefix . 'includes/footer.php';
?>
