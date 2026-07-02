<?php
  $pageTitle = "Chocolate Workshops & Bean-to-Bar Classes | RT Chocos";
  $pageDescription = "Learn chocolate making with RT Chocos in Mumbai and online. Explore upcoming bean-to-bar, tempering, bonbon and formulation workshops for every skill level.";
  $pathPrefix = "";
  $canonicalUrl = "https://www.rtchocos.com/workshops.php";
  $schemaType = "CollectionPage";
  
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

<!-- --- HOME PAGE --- -->
<div id="page-workshops" class="page active" style="padding-top:80px;">
  <div class="page-hero workshops-page-hero">
  </div>
  <div class="section">
    <div id="workshop-filters" style="margin-bottom:40px;text-align:center;">
      <div class="section-label" style="margin-bottom:12px;">RT Chocos Learning Studio</div>
      <h1 style="font-family:'Cormorant Garamond', serif;font-style:italic;font-weight:700;font-size:42px;margin-bottom:12px;color:var(--brown);letter-spacing:0.02em;">Chocolate Workshops &amp; Bean-to-Bar Learning</h1>
      <p style="font-family:'Cormorant Garamond', serif;font-style:italic;font-size:24px;line-height:1.6;color:var(--brown-light);font-weight:600;max-width:760px;margin:0 auto;">Science-first chocolate classes for curious makers, professionals and food entrepreneurs. New workshop dates are coming soon.</p>
    </div>
    <div class="grid-3" id="workshops-grid"></div>

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
