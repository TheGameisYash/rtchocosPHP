<?php
  $pageTitle = "Chocolate Recipes & Formulations — Bean to Bar Learning Gallery | RT Chocos";
  $pageDescription = "Explore professional chocolate recipes, dark chocolate formulations, truffle recipes, and tasting guides from India's first Chocolate Academy blog.";
  $pageKeywords = "chocolate, chocolate learning, recipes, bean to bar chocolate, chocolate academy, chocolate recipes, craft chocolate formulation, chocolate truffle recipe, dark chocolate recipes India";
  $pathPrefix = "";
  
  $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
  $canonicalUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  
  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'Recipes', 'item' => $canonicalUrl]
  ];
  
  // Dynamic Recipe schema data
  $recipeData = [
      'name' => "Signature Bean-to-Bar Dark Chocolate Truffles",
      'description' => "Indulgent, silky craft dark chocolate truffles formulated with hand-tempered Kerala cocoa beans and organic cream.",
      'prepTime' => "PT20M",
      'cookTime' => "PT10M",
      'totalTime' => "PT30M",
      'yield' => "20 truffles",
      'ingredients' => [
          "200g craft dark chocolate (70% cocoa)",
          "120ml organic heavy whipping cream",
          "20g unsalted grass-fed butter",
          "20g unsweetened natural cocoa powder (for dusting)"
      ],
      'instructions' => [
          "Finely chop the craft dark chocolate and place it in a heatproof glass bowl.",
          "Gently heat the heavy cream in a small saucepan until it just begins to simmer.",
          "Pour the hot cream over the chopped chocolate and let it stand undisturbed for 2 minutes.",
          "Slowly stir from the center outwards until a smooth, glossy ganache forms. Stir in the butter.",
          "Cover and chill the ganache for 2 hours until firm.",
          "Scoop small portions, roll quickly into balls, and coat thoroughly with natural cocoa powder."
      ]
  ];
  
  include $pathPrefix . 'includes/header.php';
?>

<!-- --- HOME PAGE --- -->
<div id="page-gallery" class="page active" style="padding-top:80px;">
  <div class="page-hero recipes-page-hero">
    <div class="page-hero-content">
      <h1 class="fade-up recipes-hero-title">Recipes &amp; Rituals</h1>
    </div>
  </div>
  <div class="section">
    <div class="gallery-page-grid">
      <a href="assets/Recipes/Almondbutterchocolate.html" class="gallery-page-item span-2" style="background:linear-gradient(rgba(0,0,0,0.05), rgba(0,0,0,0.05)), url('assets/almondbutterphoto.jpg') center/cover no-repeat;text-decoration:none;cursor:pointer;border-radius:4px;" aria-label="Almond Butter Chocolate Bar"></a>
      <a href="assets/Recipes/LimeChilliTruffles.html" class="gallery-page-item span-2" style="background:linear-gradient(rgba(0,0,0,0.05), rgba(0,0,0,0.05)), url('assets/limechillitrufflesphoto.jpeg') center/cover no-repeat;text-decoration:none;cursor:pointer;border-radius:4px;" aria-label="Lime Chilli Truffles"></a>
      <div class="gallery-page-item span-2" style="background:linear-gradient(rgba(13, 59, 18, 0.45), rgba(13, 59, 18, 0.45)), url('assets/bonbons.png') center/cover no-repeat; cursor: default; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 4px;">
        <span style="font-family:'Cormorant Garamond', serif; font-size: 24px; font-weight: 600; color: var(--cream); letter-spacing: 0.05em; text-transform: uppercase;">Classic Bonbons</span>
        <span style="font-family:'Jost', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 2px; color: var(--gold); text-transform: uppercase; margin-top: 8px; border: 1px solid var(--gold); padding: 4px 12px; border-radius: 2px;">Coming Soon</span>
      </div>
      <div class="gallery-page-item span-2" style="background:linear-gradient(rgba(13, 59, 18, 0.45), rgba(13, 59, 18, 0.45)), url('assets/cocoabeans.png.jpg') center/cover no-repeat; cursor: default; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 4px;">
        <span style="font-family:'Cormorant Garamond', serif; font-size: 24px; font-weight: 600; color: var(--cream); letter-spacing: 0.05em; text-transform: uppercase;">Single Origin Bar</span>
        <span style="font-family:'Jost', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 2px; color: var(--gold); text-transform: uppercase; margin-top: 8px; border: 1px solid var(--gold); padding: 4px 12px; border-radius: 2px;">Coming Soon</span>
      </div>
    </div>
  </div>
</div>

<!-- --- CONTACT PAGE --- -->

<?php
  include $pathPrefix . 'includes/footer.php';
?>
