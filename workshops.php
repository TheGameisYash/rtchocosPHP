<?php
  $pageTitle = "Workshops | RT Chocos — India's Chocolate Blog";
  $pageDescription = "Professional chocolate workshops and bean-to-bar training in Mumbai and online.";
  $pathPrefix = "";
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
