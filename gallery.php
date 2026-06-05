<?php
  $pageTitle = "Recipes | RT Chocos — India's Chocolate Blog";
  $pageDescription = "Premium craft chocolate recipes, formulation guides, and tasting rituals.";
  $pathPrefix = "";
  include $pathPrefix . 'includes/header.php';
?>

<!-- --- HOME PAGE --- -->
<div id="page-gallery" class="page active" style="padding-top:72px;">
  <div class="page-hero recipes-page-hero" style="background:linear-gradient(rgba(0,0,0,0.28), rgba(0,0,0,0.28)), url('assets/recipeblockimage.png') center/cover no-repeat;">
    <div class="page-hero-content">
      <h1 class="fade-up recipes-hero-title">Recipes &amp; Rituals</h1>
    </div>
  </div>
  <div class="section">
    <div class="gallery-page-grid">
      <a href="assets/Recipes/Almondbutterchocolate.html" class="gallery-page-item span-2" style="background:linear-gradient(rgba(0,0,0,0.05), rgba(0,0,0,0.05)), url('assets/almondbutterphoto.jpg') center/cover no-repeat;text-decoration:none;cursor:pointer;" aria-label="Almond Butter Chocolate Bar"></a>
      <a href="assets/Recipes/LimeChilliTruffles.html" class="gallery-page-item span-2" style="background:linear-gradient(rgba(0,0,0,0.05), rgba(0,0,0,0.05)), url('assets/limechillitrufflesphoto.jpeg') center/cover no-repeat;text-decoration:none;cursor:pointer;" aria-label="Lime Chilli Truffles"></a>
    </div>
  </div>
</div>

<!-- --- CONTACT PAGE --- -->

<?php
  include $pathPrefix . 'includes/footer.php';
?>
