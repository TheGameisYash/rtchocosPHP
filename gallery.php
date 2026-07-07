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

    <!-- Dynamic AI Recipe Suggestion -->
    <div class="ai-recipe-suggestion" style="background: rgba(201,149,107,0.06); border: 1px solid rgba(201,149,107,0.15); border-radius: 16px; padding: 24px; margin-bottom: 40px; text-align: center;">
      <span style="font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--accent); background: rgba(201,149,107,0.12); padding: 3px 8px; border-radius: 4px; display: inline-block; margin-bottom: 12px;">✨ AI Daily Pairing &amp; Formulation</span>
      <h3 id="ai-dynamic-recipe-title" style="font-family:'Playfair Display', serif; font-size: 22px; color: var(--brown); margin-bottom: 8px;">Crafting fresh inspiration...</h3>
      <p id="ai-dynamic-recipe-desc" style="font-size: 14.5px; color: var(--brown-light); line-height: 1.6; max-width: 680px; margin: 0 auto;">Our CocoaGenius AI is formulating a unique flavor profile. Just a moment.</p>
    </div>

    <div class="gallery-page-grid">
      <a href="assets/Recipes/Almondbutterchocolate.html" class="gallery-page-item span-2 recipe-tile" style="background:linear-gradient(rgba(0,0,0,0.10), rgba(0,0,0,0.58)), url('assets/almondbutterphoto.jpg') center/cover no-repeat;" aria-label="Almond Butter Chocolate Bar recipe"><span>Almond Butter Chocolate Bar</span></a>
      <a href="assets/Recipes/LimeChilliTruffles.html" class="gallery-page-item span-2 recipe-tile" style="background:linear-gradient(rgba(0,0,0,0.10), rgba(0,0,0,0.58)), url('assets/limechillitrufflesphoto.jpeg') center/cover no-repeat;" aria-label="Lime Chilli Truffles recipe"><span>Lime Chilli Truffles</span></a>
      <div class="gallery-page-item span-2" style="background:linear-gradient(rgba(26, 16, 18, 0.5), rgba(26, 16, 18, 0.75)), url('assets/bonbons.png') center/cover no-repeat; cursor: default; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 12px;">
        <span style="font-family:'Playfair Display', serif; font-size: 24px; font-weight: 600; color: var(--cream); letter-spacing: 0.05em; text-transform: uppercase;">Classic Bonbons</span>
        <span style="font-family:'Inter', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 2px; color: var(--accent); text-transform: uppercase; margin-top: 8px; border: 1px solid var(--accent); padding: 4px 12px; border-radius: 50px;">Coming Soon</span>
      </div>
      <div class="gallery-page-item span-2" style="background:linear-gradient(rgba(26, 16, 18, 0.5), rgba(26, 16, 18, 0.75)), url('assets/cocoabeans.png.jpg') center/cover no-repeat; cursor: default; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 12px;">
        <span style="font-family:'Playfair Display', serif; font-size: 24px; font-weight: 600; color: var(--cream); letter-spacing: 0.05em; text-transform: uppercase;">Single Origin Bar</span>
        <span style="font-family:'Inter', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 2px; color: var(--accent); text-transform: uppercase; margin-top: 8px; border: 1px solid var(--accent); padding: 4px 12px; border-radius: 50px;">Coming Soon</span>
      </div>
    </div>
  </div>
</div>

<!-- --- CONTACT PAGE --- -->

<?php
  include $pathPrefix . 'includes/footer.php';
?>
