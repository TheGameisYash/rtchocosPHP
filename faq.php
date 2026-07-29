<?php
  $pageTitle = "Frequently Asked Questions — Chocolate Academy, Bean-to-Bar Workshops & Shop | RT Chocos India";
  $pageDescription = "Find answers to common questions about RT Chocos — India's first chocolate blog and bean-to-bar academy. Learn about our workshops, courses, chocolate products, shipping, and how to start your chocolate making journey.";
  $pageKeywords = "RT Chocos FAQ, chocolate academy questions, bean to bar FAQ India, chocolate workshops FAQ, chocolate course India questions, buy chocolate online India FAQ, chocolate shipping India, chocolate making questions, craft chocolate FAQ";
  $pathPrefix = "";

  $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
  $canonicalUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/faq.php";

  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'FAQ', 'item' => $canonicalUrl]
  ];

  // Initialize FAQ schema items before header
  $GLOBALS['faqSchemaItems'] = [];

  include $pathPrefix . 'includes/header.php';
?>

<main>
<div id="page-faq" class="page active" style="padding-top:80px;">
  <div class="page-hero contact-page-hero">
    <div class="page-hero-content">
      <h1 class="fade-up">Frequently Asked Questions</h1>
      <p class="fade-up-d1">Everything you need to know about RT Chocos — India's first chocolate blog, bean-to-bar academy, workshops, and shop.</p>
    </div>
  </div>

<?php
  require_once __DIR__ . '/includes/db.php';
  
  $categories = [
      'general' => 'General Questions',
      'workshops' => 'Workshop & Academy',
      'courses' => 'Courses & Certification',
      'shop' => 'Shopping & Products',
      'shipping' => 'Shipping & Delivery'
  ];
  
  try {
      $pdo = get_db();
      
      foreach ($categories as $catKey => $catLabel):
          $stmt = $pdo->prepare("SELECT id, question, answer FROM faqs WHERE category = ? AND is_active = 1 ORDER BY display_order ASC, id ASC");
          $stmt->execute([$catKey]);
          $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
          if (empty($faqs)) continue;
  ?>
  
  <section style="background: <?php echo ($catKey === 'workshops' || $catKey === 'shipping') ? 'var(--green-50)' : 'var(--cream)'; ?>;">
    <div class="section" style="text-align:center;">
      <div class="section-label"><?php echo htmlspecialchars($catLabel); ?></div>
      <h2 class="section-title"><?php echo htmlspecialchars($catLabel); ?></h2>
      <div class="divider" style="margin:16px auto 32px;"></div>
      
      <div style="max-width:800px; margin:0 auto; text-align:left;">
        <?php foreach ($faqs as $faq): 
            $itemId = 'faq-' . $catKey . '-' . $faq['id'];
            
            // Push to global schema array
            $GLOBALS['faqSchemaItems'][] = [
                'question' => $faq['question'],
                'answer' => strip_tags($faq['answer'])
            ];
        ?>
        <div style="background:white; border-radius:16px; padding:20px 24px; margin-bottom:12px; box-shadow:0 2px 12px rgba(59,42,34,0.06); cursor:pointer; transition:box-shadow 0.2s ease;"
             onclick="toggleFaq('<?php echo $itemId; ?>', this)"
             role="button"
             aria-expanded="false"
             aria-controls="<?php echo $itemId; ?>-a">
          <div style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
            <h3 style="font-family:'Jost',sans-serif; font-size:15px; font-weight:600; color:var(--brown); margin:0; line-height:1.5;">
              <?php echo htmlspecialchars($faq['question']); ?>
            </h3>
            <span id="<?php echo $itemId; ?>-icon" style="font-size:22px; color:var(--brown-light); transition:transform 0.3s ease; flex-shrink:0; font-weight:300;">+</span>
          </div>
          <div id="<?php echo $itemId; ?>-a" style="display:none; margin-top:14px; padding-top:14px; border-top:1px solid rgba(59,42,34,0.08);">
            <p style="font-family:'Jost',sans-serif; font-size:14.5px; line-height:1.75; color:var(--brown-light); font-weight:300; margin:0;">
              <?php echo $faq['answer']; ?>
            </p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  
  <?php
      endforeach;
  } catch (Exception $e) {
      echo '<div class="section"><p style="color:var(--brown-light); text-align:center;">FAQs are currently being updated. Please check back soon.</p></div>';
  }
  ?>

</div>
</main>

<!-- FAQ Accordion Toggle Script -->
<script>
function toggleFaq(id, el) {
  var answer = document.getElementById(id + '-a');
  var icon = document.getElementById(id + '-icon');
  if (!answer) return;
  var isOpen = answer.style.display !== 'none';
  answer.style.display = isOpen ? 'none' : 'block';
  icon.textContent = isOpen ? '+' : '−';
  el.setAttribute('aria-expanded', String(!isOpen));
}
</script>

<?php
// Output FAQPage schema via a separate script tag (since header.php already closed <head>)
if (!empty($GLOBALS['faqSchemaItems'])) {
    $faqEntities = [];
    foreach ($GLOBALS['faqSchemaItems'] as $item) {
        $faqEntities[] = [
            "@type" => "Question",
            "name" => $item['question'],
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => $item['answer']
            ]
        ];
    }
    $faqSchema = [
        "@context" => "https://schema.org",
        "@type" => "FAQPage",
        "mainEntity" => $faqEntities
    ];
    echo '<script type="application/ld+json">' . "\n";
    echo json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
}

include $pathPrefix . 'includes/footer.php';
?>
