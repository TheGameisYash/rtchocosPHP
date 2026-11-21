<?php
  $pageTitle = "Chocolate Recipes & Formulations | RT Chocos India";
  $pageDescription = "Tested chocolate recipes from RT Chocos, including almond butter chocolate bars and lime chilli truffles with ingredients, methods and professional notes.";
  $pathPrefix = "";
  $canonicalUrl = "https://www.rtchocos.com/gallery";
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

<!-- --- RECIPES & FORMULATIONS PAGE --- -->
<div id="page-gallery" class="page active" style="padding-top:100px;">
  <div class="page-hero recipes-page-hero" style="position: relative; overflow: hidden; padding: 80px 24px;">
    <div class="page-hero-content" style="max-width: 820px; margin: 0 auto; text-align: center;">
      <div class="section-label">Test Kitchen Formulations</div>
      <h1 class="fade-up recipes-hero-title" style="font-family:'Playfair Display', serif; font-size: clamp(34px, 5.5vw, 64px); color: var(--white); margin-top: 12px;">Tested Recipes &amp; Cacao Formulations</h1>
      <p class="fade-up-d1" style="font-size: 16.5px; color: rgba(255,255,255,0.85); max-width: 620px; margin: 16px auto 0; line-height: 1.7;">Step-by-step artisanal chocolate recipes engineered with exact percentages, cocoa butter tempering curves, and flavour pairings.</p>
    </div>
  </div>

  <div class="section" style="padding-top: 40px;">
    <!-- Dynamic AI Recipe Suggestion Card -->
    <div class="ai-recipe-suggestion glass-card" style="background: rgba(18, 38, 27, 0.65); backdrop-filter: blur(20px); border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 24px; padding: 32px; margin-bottom: 48px; text-align: center; box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);">
      <span class="badge-gold" style="margin-bottom: 12px;">✨ AI Daily Pairing &amp; Formulation</span>
      <h3 id="ai-dynamic-recipe-title" style="font-family:'Playfair Display', serif; font-size: 24px; color: var(--white); margin-bottom: 8px;">Crafting fresh inspiration...</h3>
      <p id="ai-dynamic-recipe-desc" style="font-size: 14.5px; color: rgba(255,255,255,0.85); line-height: 1.6; max-width: 680px; margin: 0 auto;">Our CocoaGenius AI is formulating a unique flavor profile. Just a moment.</p>
    </div>

    <!-- Luxury Recipe Grid -->
    <div class="recipes-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px;">
      
      <!-- Recipe 1: Almond Butter Dark Chocolate Bar -->
      <a href="assets/Recipes/Almondbutterchocolate.html" class="recipe-card glass-card card-hover-lift" style="display: flex; flex-direction: column; overflow: hidden; text-decoration: none; text-align: left;">
        <div style="height: 240px; overflow: hidden; position: relative;">
          <img src="assets/recipe_almond_bar.png" alt="Artisanal Almond Butter Dark Chocolate Bar with Maldon Sea Salt" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;" />
          <span style="position: absolute; top: 16px; left: 16px; background: rgba(18, 38, 27, 0.85); backdrop-filter: blur(10px); color: var(--accent); font-size: 10.5px; font-weight: 700; padding: 4px 12px; border-radius: 30px; border: 1px solid rgba(212, 175, 55, 0.3);">70% DARK</span>
        </div>
        <div style="padding: 24px; display: flex; flex-direction: column; flex-grow: 1;">
          <div style="display: flex; gap: 12px; font-size: 11.5px; color: var(--accent-light); font-weight: 600; margin-bottom: 8px;">
            <span>⏱️ 45 min</span>
            <span>•</span>
            <span>👨‍🍳 Intermediate</span>
          </div>
          <h3 style="font-family:'Playfair Display', serif; font-size: 22px; color: var(--brown); margin-bottom: 10px;">Almond Butter Chocolate Bar</h3>
          <p style="font-size: 13.5px; color: var(--brown-light); line-height: 1.6; margin-bottom: 20px;">Rich 70% dark chocolate bar layered with stone-ground almond butter and Maldon sea salt flakes.</p>
          <span style="font-size: 12px; font-weight: 700; color: var(--accent); margin-top: auto; display: flex; align-items: center; gap: 6px;">View Full Formulation &rarr;</span>
        </div>
      </a>

      <!-- Recipe 2: Lime Chilli Truffles -->
      <a href="assets/Recipes/LimeChilliTruffles.html" class="recipe-card glass-card card-hover-lift" style="display: flex; flex-direction: column; overflow: hidden; text-decoration: none; text-align: left;">
        <div style="height: 240px; overflow: hidden; position: relative;">
          <img src="assets/recipe_chilli_truffles.png" alt="Hand-rolled Lime Chilli Dark Chocolate Ganache Truffles" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;" />
          <span style="position: absolute; top: 16px; left: 16px; background: rgba(18, 38, 27, 0.85); backdrop-filter: blur(10px); color: var(--accent); font-size: 10.5px; font-weight: 700; padding: 4px 12px; border-radius: 30px; border: 1px solid rgba(212, 175, 55, 0.3);">GANACHE</span>
        </div>
        <div style="padding: 24px; display: flex; flex-direction: column; flex-grow: 1;">
          <div style="display: flex; gap: 12px; font-size: 11.5px; color: var(--accent-light); font-weight: 600; margin-bottom: 8px;">
            <span>⏱️ 60 min</span>
            <span>•</span>
            <span>🌶️ Zesty &amp; Spicy</span>
          </div>
          <h3 style="font-family:'Playfair Display', serif; font-size: 22px; color: var(--brown); margin-bottom: 10px;">Lime Chilli Ganache Truffles</h3>
          <p style="font-size: 13.5px; color: var(--brown-light); line-height: 1.6; margin-bottom: 20px;">Silky dark chocolate ganache infused with fresh Mexican lime zest and Bird's Eye chilli powder.</p>
          <span style="font-size: 12px; font-weight: 700; color: var(--accent); margin-top: auto; display: flex; align-items: center; gap: 6px;">View Full Formulation &rarr;</span>
        </div>
      </a>

      <!-- Recipe 3: Hand-Painted Passion Bonbons -->
      <div class="recipe-card glass-card card-hover-lift" style="display: flex; flex-direction: column; overflow: hidden; text-align: left;">
        <div style="height: 240px; overflow: hidden; position: relative;">
          <img src="assets/recipe_bonbon_collection.png" alt="Glossy Hand-Painted Artisanal Chocolate Bonbons" style="width: 100%; height: 100%; object-fit: cover;" />
          <span style="position: absolute; top: 16px; left: 16px; background: rgba(212, 175, 55, 0.9); color: var(--dark-900); font-size: 10.5px; font-weight: 800; padding: 4px 12px; border-radius: 30px;">MASTERCLASS</span>
        </div>
        <div style="padding: 24px; display: flex; flex-direction: column; flex-grow: 1;">
          <div style="display: flex; gap: 12px; font-size: 11.5px; color: var(--accent-light); font-weight: 600; margin-bottom: 8px;">
            <span>⏱️ 90 min</span>
            <span>•</span>
            <span>🎨 Hand-Painted Shell</span>
          </div>
          <h3 style="font-family:'Playfair Display', serif; font-size: 22px; color: var(--brown); margin-bottom: 10px;">Artisanal Bonbon Collection</h3>
          <p style="font-size: 13.5px; color: var(--brown-light); line-height: 1.6; margin-bottom: 20px;">High-gloss cocoa butter shells filled with passion fruit caramel and 65% single origin ganache.</p>
          <span style="font-size: 11px; font-weight: 700; letter-spacing: 1px; color: var(--accent); text-transform: uppercase; margin-top: auto;">Coming Soon in Academy</span>
        </div>
      </div>

      <!-- Recipe 4: Single Origin 70% Kerala Bar -->
      <div class="recipe-card glass-card card-hover-lift" style="display: flex; flex-direction: column; overflow: hidden; text-align: left;">
        <div style="height: 240px; overflow: hidden; position: relative;">
          <img src="assets/recipe_single_origin.png" alt="Single Origin 70% Indian Cacao Bean-to-Bar Dark Chocolate" style="width: 100%; height: 100%; object-fit: cover;" />
          <span style="position: absolute; top: 16px; left: 16px; background: rgba(18, 38, 27, 0.85); backdrop-filter: blur(10px); color: var(--accent); font-size: 10.5px; font-weight: 700; padding: 4px 12px; border-radius: 30px; border: 1px solid rgba(212, 175, 55, 0.3);">BEAN-TO-BAR</span>
        </div>
        <div style="padding: 24px; display: flex; flex-direction: column; flex-grow: 1;">
          <div style="display: flex; gap: 12px; font-size: 11.5px; color: var(--accent-light); font-weight: 600; margin-bottom: 8px;">
            <span>⏱️ 72 hrs Conch</span>
            <span>•</span>
            <span>🌱 Single Origin</span>
          </div>
          <h3 style="font-family:'Playfair Display', serif; font-size: 22px; color: var(--brown); margin-bottom: 10px;">Single Origin 70% Cacao Bar</h3>
          <p style="font-size: 13.5px; color: var(--brown-light); line-height: 1.6; margin-bottom: 20px;">Fermented Idukki, Kerala cacao beans stone-ground for 72 hours with unrefined organic cane sugar.</p>
          <span style="font-size: 11px; font-weight: 700; letter-spacing: 1px; color: var(--accent); text-transform: uppercase; margin-top: auto;">Featured Batch Profile</span>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- --- CONTACT PAGE --- -->

<?php
  include $pathPrefix . 'includes/footer.php';
?>
