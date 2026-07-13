<?php
  $pageTitle = "Chocolate Academy India — Bean-to-Bar Workshops, Tempering Masterclass & Online Courses | RT Chocos";
  $pageDescription = "Enrol in India's top chocolate academy workshops. Professional bean-to-bar courses, tempering science masterclasses, and chocolate making workshops in Mumbai & online. Founded by Aarti Saluja Sahni — India's first chocolate educator with 2,000+ students trained.";
  $pageKeywords = "chocolate academy India, chocolate workshops India, chocolate course India, bean to bar course India, learn chocolate making India, chocolate classes Mumbai, chocolate masterclass India, tempering chocolate course India, professional chocolate course online India, chocolate making workshop Mumbai, bean to bar learning academy, craft chocolate course, cocoa science workshop, chocolate training India, learn bean to bar India, RT Chocos workshops, Aarti Saluja Sahni workshops";
  $pathPrefix = "";
  
  $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
  $canonicalUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  
  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'Workshops', 'item' => $canonicalUrl]
  ];
  
  // Dynamic Course schema data
  $courseData = [
      'name' => "Professional Bean-to-Bar Chocolate Masterclass",
      'description' => "Deep-dive course on cacao fermentation, roasting profiles, stone grinding, tempering science, and packaging design.",
      'mode' => "blended",
      'location' => "Mumbai Craft Kitchen Studio & Online Zoom sessions",
      'price' => "15000"
  ];
  
  include $pathPrefix . 'includes/header.php';
?>

<!-- --- HOME PAGE --- -->
<div id="page-workshops" class="page active" style="padding-top:80px;">
  <div class="page-hero workshops-page-hero">
  </div>
  <div class="section">
    <div id="workshop-filters" style="margin-bottom:40px;text-align:center;">
      <h1 style="font-family:'Cormorant Garamond', serif;font-style:italic;font-weight:700;font-size:42px;margin-bottom:12px;color:var(--brown);letter-spacing:0.02em;">Chocolate Academy India — Our Workshops Are Brewing</h1>
      <p style="font-family:'Cormorant Garamond', serif;font-style:italic;font-size:24px;line-height:1.6;color:var(--brown-light);font-weight:600;max-width:680px;margin:0 auto;">Exciting chocolate-making workshops will be announced soon</p>
      <p style="font-family:'Jost', sans-serif;font-size:15px;line-height:1.8;color:var(--brown-light);font-weight:300;max-width:680px;margin:20px auto 0;">RT Chocos is India's first chocolate academy offering professional bean-to-bar workshops, tempering science masterclasses, and hands-on cocoa-to-bar courses in Mumbai and online. Founded by Aarti Saluja Sahni — India's first chocolate blogger and educator with 10+ years of experience and 2,000+ students trained. Subscribe below to be the first to know when new chocolate courses open for enrolment.</p>
    </div>
    <div class="grid-3" id="workshops-grid"></div>
  </div>
</div>

<!-- --- BLOG PAGE --- -->

<?php
  include $pathPrefix . 'includes/footer.php';
?>
