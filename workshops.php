<?php
  $pageTitle = "Chocolate Workshops & Bean-to-Bar Classes | RT Chocos";
  $pageDescription = "Learn chocolate making with RT Chocos in Mumbai and online. Explore upcoming bean-to-bar, tempering, bonbon and formulation workshops for every skill level.";
  $pathPrefix = "";
  $canonicalUrl = "https://www.rtchocos.com/workshops";
  $schemaType = "CollectionPage";
  
  // Load database connection and fetch the latest class fact for server-side pre-rendering
  require_once $pathPrefix . 'includes/db.php';
  try {
      $pdo = get_db();
      $factStmt = $pdo->query("SELECT fact_text FROM ai_class_facts ORDER BY id DESC LIMIT 1");
      $latestClassFact = $factStmt->fetchColumn();
  } catch (Exception $e) {
      $latestClassFact = "Tempering cocoa butter requires precisely forming Type V crystals for that satisfying snap and glossy finish.";
  }
  if (!$latestClassFact) {
      $latestClassFact = "Tempering cocoa butter requires precisely forming Type V crystals for that satisfying snap and glossy finish.";
  }
  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'Workshops', 'item' => $canonicalUrl]
  ];
  
  include $pathPrefix . 'includes/header.php';
?>

<!-- --- WORKSHOPS PAGE --- -->
<div id="page-workshops" class="page active">
  <!-- =========================================================================
       SECTION 1: HERO SECTION (Full-Size Luxury Hero — Matching Product Page)
       ========================================================================= -->
  <section class="workshop-hero-lux">
    <div class="workshop-hero-container">

      <!-- Left Column: Content, 4 Feature Badges, Action CTAs -->
      <div class="workshop-hero-left">
        <span class="workshop-hero-tag">RT Chocos Learning Studio &amp; Academy</span>
        <h1 class="workshop-hero-title">The Cacao Lab.<br>Master Bean-to-Bar Chocolate.</h1>
        <p class="workshop-hero-desc">
          A collection of premium, technical chocolate workshops and masterclasses. Learn bean-to-bar making, tempering science, and recipe formulation from expert Aarti Saluja Sahni.
        </p>

        <!-- 4 Luxury Value Badges (Same Architecture as Product Page Hero) -->
        <div class="workshop-hero-badges">
          <!-- 1: 7-Stage Process -->
          <div class="workshop-badge-item">
            <div class="workshop-badge-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
            </div>
            <span class="workshop-badge-label">7-Stage<br>Bean-to-Bar</span>
          </div>

          <!-- 2: Hands-on Studio -->
          <div class="workshop-badge-item">
            <div class="workshop-badge-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 11a8 8 0 0 0 16 0H4z"></path>
                <path d="M12 2v4"></path>
                <line x1="2" y1="20" x2="22" y2="20"></line>
              </svg>
            </div>
            <span class="workshop-badge-label">Hands-on<br>Studio Practice</span>
          </div>

          <!-- 3: Tempering & Formulation -->
          <div class="workshop-badge-item">
            <div class="workshop-badge-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
              </svg>
            </div>
            <span class="workshop-badge-label">Tempering<br>&amp; Formulation</span>
          </div>

          <!-- 4: Certification -->
          <div class="workshop-badge-item">
            <div class="workshop-badge-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
              </svg>
            </div>
            <span class="workshop-badge-label">Certification<br>&amp; Take-Home</span>
          </div>
        </div>

        <!-- Action CTAs -->
        <div class="workshop-hero-actions">
          <a href="#workshops-grid" class="btn-gold-lux">
            Explore Masterclasses &darr;
          </a>
          <a href="#learning-roadmap-title" class="btn-outline-lux">
            Learning Roadmap &rarr;
          </a>
        </div>
      </div>

      <!-- Right Visual Showcase (Desktop shows background image seamlessly; Mobile shows crisp feature card) -->
      <div class="workshop-hero-showcase-wrap">
        <div class="workshop-hero-photo-card">
          <img src="assets/workshop_hero.png" alt="7-Stage Bean-to-Bar Process: Sorting, Roasting, Winnowing, Grinding, Conching, Tempering" class="workshop-hero-mobile-photo" width="1024" height="426" loading="eager" />
          <div class="workshop-photo-caption">
            <span class="cacao-icon" aria-hidden="true">🍫</span> 7-Stage Bean-to-Bar Process &bull; Studio &amp; Online Labs
          </div>
        </div>
      </div>
    </div>
  </section>
  <div class="section">

    <!-- AI Class Insight Component -->
    <div class="ai-class-insight-card">
      <div class="insight-badge-row">
        <span class="insight-pill">
          <span class="insight-pulse-dot" aria-hidden="true"></span>
          Cacao Science Insight of the Moment
        </span>
        <span class="insight-curator">Curated by Aarti Saluja Sahni</span>
      </div>
      <blockquote class="insight-quote">
        &ldquo;<span id="ai-dynamic-class-insight"><?php echo htmlspecialchars($latestClassFact, ENT_QUOTES, 'UTF-8'); ?></span>&rdquo;
      </blockquote>
    </div>

    <!-- Section Header for Masterclasses Grid -->
    <div class="workshops-catalog-header">
      <div class="section-label">Academy Masterclasses</div>
      <h2 class="section-title">Professional Practical &amp; Science Labs</h2>
      <p class="section-subtitle">Small-cohort masterclasses combining sensory science with professional confectionery technique.</p>
      <div class="mobile-swipe-indicator" aria-hidden="true">
        <span class="swipe-arrow">&larr;</span> Swipe to explore all 3 Masterclasses <span class="swipe-arrow">&rarr;</span>
      </div>
    </div>

    <div class="grid-3 workshops-grid-track" id="workshops-grid">
      <?php
        require_once 'includes/workshops_data.php';
        foreach ($workshops as $w) {
            echo renderWorkshopCard($w);
        }
      ?>
    </div>

    <section class="seo-learning-section" aria-labelledby="learning-roadmap-title">
      <div class="section-label">Academic Curriculum</div>
      <h2 id="learning-roadmap-title" class="section-title">A Practical Chocolate Science Roadmap</h2>
      <p class="seo-learning-intro">At RT Chocos, confectionery technique is anchored in food science. The curriculum breaks down bean-to-bar production into four rigorous, interconnected modules—guiding you from raw cacao bean evaluation through to shelf-stable commercial formulation.</p>
      
      <div class="seo-topic-grid">
        <article class="seo-topic-card">
          <div class="topic-card-top">
            <span class="topic-module-badge">Module 01</span>
            <span class="topic-stage-label">Foundations</span>
          </div>
          <h3 class="topic-title">Bean-to-Bar Foundations</h3>
          <p class="topic-desc">Follow raw cacao from ethical plantation sourcing to finished mass. Learn bean cut tests, defect sorting, convection vs. drum roasting curves, winnowing yield math, and stone melangeur conching dynamics that shape aroma and mouthfeel.</p>
          <ul class="topic-skills-list">
            <li><span>✓</span> Cut tests &amp; origin terroir evaluation</li>
            <li><span>✓</span> Precision roasting &amp; winnowing physics</li>
            <li><span>✓</span> Nib refining &amp; particle micronization</li>
          </ul>
        </article>

        <article class="seo-topic-card">
          <div class="topic-card-top">
            <span class="topic-module-badge">Module 02</span>
            <span class="topic-stage-label">Crystallization</span>
          </div>
          <h3 class="topic-title">Tempering &amp; Crystal Science</h3>
          <p class="topic-desc">Master the physical chemistry of cocoa butter crystallization across Form I through Form VI polymorphs. Learn table marble tabling, seed induction, thermal working ranges, and diagnostic tests to permanently eliminate fat bloom and dull finish.</p>
          <ul class="topic-skills-list">
            <li><span>✓</span> Form-V crystal lattice alignment</li>
            <li><span>✓</span> Marble tabling &amp; seeding protocols</li>
            <li><span>✓</span> Fat bloom diagnostics &amp; prevention</li>
          </ul>
        </article>

        <article class="seo-topic-card">
          <div class="topic-card-top">
            <span class="topic-module-badge">Module 03</span>
            <span class="topic-stage-label">Confectionery</span>
          </div>
          <h3 class="topic-title">Bonbons, Ganache &amp; Fillings</h3>
          <p class="topic-desc">Cast ultra-thin, mirror-gloss shells with airbrushed coloured cocoa butter. Dive deep into lipid-water emulsion physics, water activity (a<sub>w</sub> &le; 0.85) control, extended shelf-life thermodynamics, and stable fruit gelée and praline layered centers.</p>
          <ul class="topic-skills-list">
            <li><span>✓</span> Mirror polishing &amp; airbrush techniques</li>
            <li><span>✓</span> Water activity (a<sub>w</sub>) control &amp; shelf life</li>
            <li><span>✓</span> Stable lipid/aqueous emulsion ratios</li>
          </ul>
        </article>

        <article class="seo-topic-card">
          <div class="topic-card-top">
            <span class="topic-module-badge">Module 04</span>
            <span class="topic-stage-label">Formulation</span>
          </div>
          <h3 class="topic-title">Recipe &amp; Commercial Formulation</h3>
          <p class="topic-desc">Translate artisan prototypes into standardized, production-ready formulations. Master Baker's percentages, total fat ratios, milk crumb integration, sensory defect identification, nutritional calculations, and commercial batch scaling.</p>
          <ul class="topic-skills-list">
            <li><span>✓</span> Mathematical formulation spreadsheets</li>
            <li><span>✓</span> Sensory profiling &amp; off-note troubleshooting</li>
            <li><span>✓</span> Batch records &amp; commercial scaling</li>
          </ul>
        </article>
      </div>

      <!-- Luxury Consultation Box -->
      <div class="academy-consultation-card">
        <div class="consultation-content">
          <span class="consultation-tag">Bespoke Studio Training</span>
          <h3 class="consultation-title">Looking for Private Mentorship or Corporate Workshops?</h3>
          <p class="consultation-desc">Aarti Saluja Sahni conducts customized one-on-one masterclasses, corporate team sensory sessions, and product formulation consulting for chocolate artisans and culinary brands across India.</p>
        </div>
        <div class="consultation-actions">
          <a href="https://wa.me/919140238741?text=Hello+Aarti!+I+am+interested+in+private+or+corporate+chocolate+masterclasses." target="_blank" rel="noopener" class="btn-gold-lux">
            Inquire on WhatsApp &rarr;
          </a>
          <a href="contact.php" class="btn-outline-lux">
            Send Academic Inquiry
          </a>
        </div>
      </div>
    </section>

    
  </div>
</div>

<!-- --- BLOG PAGE --- -->

<?php
  include $pathPrefix . 'includes/footer.php';
?>
