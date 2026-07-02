<?php
  $pageTitle = "Chocolate Recipes & Formulations | RT Chocos India";
  $pageDescription = "Tested chocolate recipes from RT Chocos, including almond butter chocolate bars and lime chilli truffles with ingredients, methods and professional notes.";
  $pathPrefix = "";
  $canonicalUrl = "https://www.rtchocos.com/gallery.php";
  $schemaType = "CollectionPage";
  
  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'Recipes', 'item' => $canonicalUrl]
  ];
  
  $itemList = [
      'name' => 'RT Chocos chocolate recipes',
      'items' => [
          ['name' => 'Almond Butter Chocolate Bar', 'url' => 'https://www.rtchocos.com/assets/Recipes/Almondbutterchocolate.html'],
          ['name' => 'Lime Chilli Truffles', 'url' => 'https://www.rtchocos.com/assets/Recipes/LimeChilliTruffles.html']
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
    <div class="recipe-collection-intro">
      <div class="section-label">From the RT Chocos Test Kitchen</div>
      <h2 class="section-title">Chocolate recipes built to teach technique</h2>
      <p>Each formulation includes measured ingredients, a step-by-step method, storage guidance and professional notes. Start with an almond butter dark chocolate bar or a bright lime chilli ganache truffle, then use the principles to develop your own flavour combinations.</p>
    </div>
    <div class="gallery-page-grid">
      <a href="assets/Recipes/Almondbutterchocolate.html" class="gallery-page-item span-2 recipe-tile" style="background:linear-gradient(rgba(0,0,0,0.10), rgba(0,0,0,0.58)), url('assets/almondbutterphoto.jpg') center/cover no-repeat;" aria-label="Almond Butter Chocolate Bar recipe"><span>Almond Butter Chocolate Bar</span></a>
      <a href="assets/Recipes/LimeChilliTruffles.html" class="gallery-page-item span-2 recipe-tile" style="background:linear-gradient(rgba(0,0,0,0.10), rgba(0,0,0,0.58)), url('assets/limechillitrufflesphoto.jpeg') center/cover no-repeat;" aria-label="Lime Chilli Truffles recipe"><span>Lime Chilli Truffles</span></a>
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
