<?php
  $pageTitle = "Chocolate Workshops & Bean-to-Bar Classes | RT Chocos";
  $pageDescription = "Learn chocolate making with RT Chocos in Mumbai and online. Explore upcoming bean-to-bar, tempering, bonbon and formulation workshops for every skill level.";
  $pathPrefix = "";
  $canonicalUrl = "https://www.rtchocos.com/workshops.php";
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
<div id="page-workshops" class="page active" style="padding-top:80px;">
  <div class="page-hero workshops-page-hero">
    <div class="page-hero-content">
      <h1 class="fade-up">Workshops &amp; Masterclasses</h1>
      <p class="fade-up-d1">Science-first chocolate learning for curious makers, professionals and food entrepreneurs.</p>
    </div>
  </div>
  <div class="section">
    <div id="workshop-filters" style="margin-bottom:40px;text-align:center; display:flex; flex-direction:column; align-items:center;">
      <div class="section-label">RT Chocos Learning Studio</div>
      <h2 class="section-title">Upcoming Learning Sessions</h2>
      <p class="section-subtitle">A collection of premium, technical chocolate workshops is currently in development. Explore our topics below.</p>
    </div>

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

    <section class="seo-faq" aria-labelledby="workshop-faq-title">
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
