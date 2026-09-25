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
  
  $faqItems = [
      [
          'question' => 'Who are RT Chocos chocolate workshops for?',
          'answer' => 'The planned sessions are designed for home bakers, culinary students, working chocolatiers and founders who want a clearer technical understanding of chocolate.'
      ],
      [
          'question' => 'Will bean-to-bar chocolate making be covered?',
          'answer' => 'Yes. The learning roadmap includes cacao selection, roasting, winnowing, refining, conching, tempering and evaluating a finished chocolate bar.'
      ],
      [
          'question' => 'Are workshops available online?',
          'answer' => 'RT Chocos plans both Mumbai-based and online learning formats. Dates, format and fees will be published before registration opens.'
      ]
  ];
  
  include $pathPrefix . 'includes/header.php';
?>

<!-- --- WORKSHOPS PAGE --- -->
<div id="page-workshops" class="page active" style="padding-top:76px;">
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
          Step inside India's premier artisanal chocolate lab. Comprehensive masterclasses covering bean selection, roast profiling, stone melangeur conching, and Form V tempering science from expert chocolatier Aarti Saluja Sahni.
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
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
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
          <a href="#workshop-faq" class="btn-outline-lux">
            Curriculum &amp; FAQ &rarr;
          </a>
        </div>
      </div>

      <!-- Right Visual Showcase (Desktop shows background image seamlessly; Mobile shows crisp image card) -->
      <div class="workshop-hero-showcase-wrap">
        <img src="assets/workshop_hero.png" alt="Bean-to-Bar Chocolate Making Process: Bean, Cleaning &amp; Sorting, Roasting, Winnowing, Grinding, Conching, Tempering" class="workshop-hero-mobile-photo" width="1024" height="426" loading="eager" />
      </div>
    </div>
  </section>
  <div class="section">

    <!-- AI Class Insight -->
    <div class="ai-class-insight" style="background: rgba(201,149,107,0.06); border-left: 4px solid var(--accent); padding: 16px 20px; border-radius: 4px; margin-bottom: 32px; text-align: left;">
      <h4 style="font-family:'Playfair Display', serif; font-size: 16px; color: var(--accent-dark); margin-bottom: 6px;">💡 AI Class Fact of the Moment</h4>
      <p id="ai-dynamic-class-insight" style="font-size: 14px; color: var(--brown-light); line-height: 1.6; margin: 0; font-style: italic;"><?php echo htmlspecialchars($latestClassFact, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

    <div class="grid-3" id="workshops-grid">
      <?php
        require_once 'includes/workshops_data.php';
        foreach ($workshops as $w) {
            echo renderWorkshopCard($w);
        }
      ?>
    </div>

    <section class="seo-learning-section" aria-labelledby="learning-roadmap-title">
      <div class="section-label">What You Can Learn</div>
      <h2 id="learning-roadmap-title" class="section-title">A practical chocolate learning roadmap</h2>
      <p class="seo-learning-intro">RT Chocos connects technique with the food science behind it. The programme is being developed around four focused learning paths, from understanding cacao to producing consistent finished chocolate.</p>
      <div class="seo-topic-grid">
        <article class="seo-topic-card">
          <h3>Bean-to-Bar Foundations</h3>
          <p>Follow cacao through sorting, roasting, cracking, winnowing, refining and conching. Learn how origin and processing decisions shape flavour and texture.</p>
        </article>
        <article class="seo-topic-card">
          <h3>Tempering &amp; Crystal Science</h3>
          <p>Understand cocoa butter crystals, working temperatures and cooling. Diagnose dull finish, poor snap, streaks and fat bloom with repeatable methods.</p>
        </article>
        <article class="seo-topic-card">
          <h3>Bonbons, Ganache &amp; Fillings</h3>
          <p>Build balanced shells and centres while learning about emulsions, flavour infusion, water activity, shelf life and safe storage.</p>
        </article>
        <article class="seo-topic-card">
          <h3>Recipe &amp; Product Development</h3>
          <p>Move from an idea to a testable formulation. Explore ingredient function, percentages, batch records, sensory evaluation and production consistency.</p>
        </article>
      </div>
      <div class="seo-learning-cta">
        <p>While registrations are being prepared, start with the <a href="blog.php">RT Chocos chocolate blog</a> or explore our <a href="gallery.php">tested chocolate recipes</a>.</p>
      </div>
    </section>

    <section class="seo-faq" id="workshop-faq" aria-labelledby="workshop-faq-title">
      <div class="section-label">Frequently Asked Questions</div>
      <h2 id="workshop-faq-title" class="section-title">Chocolate workshop FAQs</h2>
      <?php foreach ($faqItems as $faq): ?>
        <details>
          <summary><?php echo htmlspecialchars($faq['question']); ?></summary>
          <p><?php echo htmlspecialchars($faq['answer']); ?></p>
        </details>
      <?php endforeach; ?>
    </section>
  </div>
</div>

<!-- --- BLOG PAGE --- -->

<?php
  include $pathPrefix . 'includes/footer.php';
?>
