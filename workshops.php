<?php
  $pageTitle = "Chocolate Making Workshops India — Bean to Bar Training | RT Chocos";
  $pageDescription = "Join India's top-rated chocolate making workshops. Learn bean-to-bar chocolate, tempering science, and conching secrets from RT Chocos in Mumbai & online.";
  $pageKeywords = "chocolate workshops India, bean to bar classes Mumbai, professional chocolate course, learn tempering chocolate";
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
      <h1 style="font-family:'Cormorant Garamond', serif;font-style:italic;font-weight:700;font-size:42px;margin-bottom:12px;color:var(--brown);letter-spacing:0.02em;">Our Workshops Are Brewing</h1>
      <p style="font-family:'Cormorant Garamond', serif;font-style:italic;font-size:24px;line-height:1.6;color:var(--brown-light);font-weight:600;max-width:680px;margin:0 auto;">Exciting chocolate-making workshops will be announced soon</p>
    </div>
    <div class="grid-3" id="workshops-grid"></div>
  </div>
</div>

<!-- --- BLOG PAGE --- -->

<?php
  include $pathPrefix . 'includes/footer.php';
?>
