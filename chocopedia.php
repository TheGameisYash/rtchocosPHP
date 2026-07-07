<?php
$pathPrefix = "";
$isHome = false;
$pageTitle = "Chocopedia — The Chocolate Encyclopedia | RT Chocos";
$pageDescription = "Your comprehensive guide to chocolate. Explore bean-to-bar terms, cacao science, tempering techniques, and everything chocolate from A to Z.";
$schemaType = "CollectionPage";
$breadcrumbs = [
    ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
    ['name' => 'Chocopedia', 'item' => 'https://www.rtchocos.com/chocopedia.php']
];
include 'includes/header.php';
?>

<!-- CHOCOPEDIA HERO -->
<section class="chocopedia-hero">
  <h1>Chocopedia</h1>
  <p>Your A-to-Z encyclopedia of chocolate. From bean varieties to tempering science — everything you need to know.</p>
  <div class="chocopedia-search">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="11" cy="11" r="8"></circle>
      <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
    </svg>
    <input type="text" id="chocopedia-search-input" placeholder="Search terms (e.g. tempering, conching)..." oninput="filterChocopedia(this.value)">
  </div>
</section>

<!-- CATEGORIES -->
<div class="section">
  <div class="section-label" style="text-align:center;">Browse by Category</div>
  <h2 class="section-title" style="text-align:center; margin-bottom:40px;">Explore the World of Chocolate</h2>

  <div class="chocopedia-categories">
    <div class="chocopedia-cat-card" onclick="scrollToLetter('B')">
      <div class="chocopedia-cat-icon">🫘</div>
      <h3>Bean Origins</h3>
      <p>Cacao varietals, terroir, and growing regions around the world.</p>
    </div>
    <div class="chocopedia-cat-card" onclick="scrollToLetter('C')">
      <div class="chocopedia-cat-icon">⚗️</div>
      <h3>Craft & Process</h3>
      <p>From roasting and winnowing to conching and tempering.</p>
    </div>
    <div class="chocopedia-cat-card" onclick="scrollToLetter('D')">
      <div class="chocopedia-cat-icon">🍫</div>
      <h3>Chocolate Types</h3>
      <p>Dark, milk, white, ruby, and everything in between.</p>
    </div>
    <div class="chocopedia-cat-card" onclick="scrollToLetter('F')">
      <div class="chocopedia-cat-icon">🔬</div>
      <h3>Food Science</h3>
      <p>Crystal polymorphs, bloom, emulsification, and cocoa chemistry.</p>
    </div>
  </div>

  <!-- GLOSSARY -->
  <div class="chocopedia-glossary" id="chocopedia-glossary">
    <!-- Letter A -->
    <div class="chocopedia-letter-group" data-letter="A">
      <div class="chocopedia-letter" id="letter-A">A</div>
      <div class="chocopedia-term" data-term="alkalization">
        <h4>Alkalization <span class="chocopedia-term-tag">Process</span></h4>
        <p>Also known as "Dutching" — a chemical process where cocoa is treated with an alkaline solution to neutralize its natural acidity. This darkens the colour, mellows the flavour, and improves solubility. Invented by Coenraad Johannes van Houten in 1828.</p>
      </div>
      <div class="chocopedia-term" data-term="arriba">
        <h4>Arriba Nacional <span class="chocopedia-term-tag">Bean</span></h4>
        <p>A fine-flavour cacao variety native to Ecuador's coastal lowlands. Known for its distinctive floral and jasmine-like aroma, Arriba is one of the world's most prized cacao origins, contributing to Ecuador's status as a top fine-flavour exporter.</p>
      </div>
    </div>

    <!-- Letter B -->
    <div class="chocopedia-letter-group" data-letter="B">
      <div class="chocopedia-letter" id="letter-B">B</div>
      <div class="chocopedia-term" data-term="bean-to-bar">
        <h4>Bean-to-Bar <span class="chocopedia-term-tag">Process</span></h4>
        <p>A chocolate-making philosophy where the maker controls every step — from sourcing raw cacao beans to producing the finished bar. This approach prioritizes traceability, craftsmanship, and flavour transparency over industrial efficiency.</p>
      </div>
      <div class="chocopedia-term" data-term="bloom">
        <h4>Bloom <span class="chocopedia-term-tag">Science</span></h4>
        <p>A whitish-grey haze that appears on chocolate's surface. Fat bloom is caused by unstable cocoa butter crystals migrating to the surface; sugar bloom results from moisture dissolving surface sugar. Neither is harmful but affects appearance and texture.</p>
      </div>
      <div class="chocopedia-term" data-term="broma process">
        <h4>Broma Process <span class="chocopedia-term-tag">Process</span></h4>
        <p>A method of extracting cocoa butter from cacao by hanging bags of roasted, ground cocoa beans in a warm room. The heat melts the butter, which drips out, leaving behind a lower-fat cocoa cake that can be ground into cocoa powder.</p>
      </div>
    </div>

    <!-- Letter C -->
    <div class="chocopedia-letter-group" data-letter="C">
      <div class="chocopedia-letter" id="letter-C">C</div>
      <div class="chocopedia-term" data-term="cacao">
        <h4>Cacao <span class="chocopedia-term-tag">Bean</span></h4>
        <p>The raw, unprocessed seed of the Theobroma cacao tree. "Cacao" typically refers to the bean in its natural state — before roasting, fermenting, and processing into what we know as "cocoa" or chocolate.</p>
      </div>
      <div class="chocopedia-term" data-term="conching">
        <h4>Conching <span class="chocopedia-term-tag">Process</span></h4>
        <p>A critical refining step invented by Rodolphe Lindt in 1879. The chocolate mass is continuously agitated and aerated at controlled temperatures for hours (or days). This develops flavour, reduces particle size, drives off volatile acids, and creates chocolate's signature smooth mouthfeel.</p>
      </div>
      <div class="chocopedia-term" data-term="couverture">
        <h4>Couverture <span class="chocopedia-term-tag">Type</span></h4>
        <p>A high-quality chocolate with a minimum 31% cocoa butter content (EU standard). The higher fat ratio gives couverture superior fluidity when melted, making it the professional's choice for tempering, moulding, enrobing, and ganache work.</p>
      </div>
      <div class="chocopedia-term" data-term="criollo">
        <h4>Criollo <span class="chocopedia-term-tag">Bean</span></h4>
        <p>The rarest and most prized of the three main cacao varietals, comprising less than 5% of world production. Criollo beans are known for their complex, delicate flavour profiles with minimal bitterness — often featuring notes of nuts, caramel, and tropical fruit.</p>
      </div>
    </div>

    <!-- Letter D -->
    <div class="chocopedia-letter-group" data-letter="D">
      <div class="chocopedia-letter" id="letter-D">D</div>
      <div class="chocopedia-term" data-term="dark chocolate">
        <h4>Dark Chocolate <span class="chocopedia-term-tag">Type</span></h4>
        <p>Chocolate containing cocoa solids, cocoa butter, and sugar — without any milk solids. Ranges from 50% to 100% cacao. Higher percentages yield more intense, bitter flavours, while lower percentages are sweeter and more accessible.</p>
      </div>
      <div class="chocopedia-term" data-term="dutching">
        <h4>Dutching <span class="chocopedia-term-tag">Process</span></h4>
        <p>See <em>Alkalization</em>. Named after Dutch chemist Coenraad van Houten who pioneered the technique. The process raises cocoa's pH from ~5.0 to 7-8, resulting in a darker colour and less acidic taste.</p>
      </div>
    </div>

    <!-- Letter E -->
    <div class="chocopedia-letter-group" data-letter="E">
      <div class="chocopedia-letter" id="letter-E">E</div>
      <div class="chocopedia-term" data-term="enrobing">
        <h4>Enrobing <span class="chocopedia-term-tag">Process</span></h4>
        <p>The process of coating a confection centre (truffle, biscuit, fruit, etc.) with a thin, even layer of tempered chocolate. Industrial enrobers use a curtain of flowing chocolate, while artisans may dip centres by hand using a fork or dipping tool.</p>
      </div>
    </div>

    <!-- Letter F -->
    <div class="chocopedia-letter-group" data-letter="F">
      <div class="chocopedia-letter" id="letter-F">F</div>
      <div class="chocopedia-term" data-term="fermentation">
        <h4>Fermentation <span class="chocopedia-term-tag">Process</span></h4>
        <p>A 5-7 day process crucial to chocolate flavour development. Freshly harvested cacao beans are heaped in wooden boxes or banana leaves, where yeasts and bacteria break down the sugary pulp. This generates heat (up to 50°C), kills the embryo, and triggers complex biochemical reactions that develop the precursor flavour compounds.</p>
      </div>
      <div class="chocopedia-term" data-term="forastero">
        <h4>Forastero <span class="chocopedia-term-tag">Bean</span></h4>
        <p>The most widely cultivated cacao variety, accounting for 80-90% of global production. Forastero is hardier and more disease-resistant than Criollo, but generally produces more robust, straightforward flavours with higher bitterness and astringency.</p>
      </div>
      <div class="chocopedia-term" data-term="form v">
        <h4>Form V (Beta-2) <span class="chocopedia-term-tag">Science</span></h4>
        <p>The ideal crystalline structure of tempered cocoa butter. Of the six polymorphic forms (I-VI) that cocoa butter can crystallise in, Form V produces the characteristic glossy sheen, firm snap, smooth texture, and resistance to bloom that defines well-tempered chocolate.</p>
      </div>
    </div>

    <!-- Letter G -->
    <div class="chocopedia-letter-group" data-letter="G">
      <div class="chocopedia-letter" id="letter-G">G</div>
      <div class="chocopedia-term" data-term="ganache">
        <h4>Ganache <span class="chocopedia-term-tag">Technique</span></h4>
        <p>An emulsion of chocolate and cream (and sometimes butter). The ratio of chocolate to cream determines the ganache's consistency: 2:1 for truffles, 1:1 for frosting, and 1:2 for a pourable glaze. Proper emulsification — combining in stages — is key to a smooth, stable ganache.</p>
      </div>
      <div class="chocopedia-term" data-term="gianduja">
        <h4>Gianduja <span class="chocopedia-term-tag">Type</span></h4>
        <p>An Italian chocolate confection made by blending chocolate (usually milk) with at least 30% finely ground hazelnuts. Originating in Turin around 1852, it was created as a response to cocoa shortages. Nutella is a mass-market descendant of this craft tradition.</p>
      </div>
    </div>

    <!-- Letter L -->
    <div class="chocopedia-letter-group" data-letter="L">
      <div class="chocopedia-letter" id="letter-L">L</div>
      <div class="chocopedia-term" data-term="lecithin">
        <h4>Lecithin <span class="chocopedia-term-tag">Ingredient</span></h4>
        <p>An emulsifier (usually soy or sunflower derived) added to chocolate at 0.3-0.5% to improve viscosity and flow. It reduces the amount of cocoa butter needed for a smooth texture, making production more efficient. Some bean-to-bar purists avoid it.</p>
      </div>
    </div>

    <!-- Letter M -->
    <div class="chocopedia-letter-group" data-letter="M">
      <div class="chocopedia-letter" id="letter-M">M</div>
      <div class="chocopedia-term" data-term="maillard reaction">
        <h4>Maillard Reaction <span class="chocopedia-term-tag">Science</span></h4>
        <p>A non-enzymatic browning reaction between amino acids and reducing sugars that occurs during cacao roasting (and baking). It generates hundreds of volatile compounds responsible for the complex flavour spectrum of roasted cocoa — from nutty and caramel to floral and smoky notes.</p>
      </div>
      <div class="chocopedia-term" data-term="melanging">
        <h4>Melanging <span class="chocopedia-term-tag">Process</span></h4>
        <p>Grinding and refining chocolate using a stone melanger (a rotating granite wheel on a granite base). Popular among small-batch and bean-to-bar makers as it combines grinding, refining, and sometimes conching in a single machine. Typical melange time ranges from 24 to 72 hours.</p>
      </div>
    </div>

    <!-- Letter N -->
    <div class="chocopedia-letter-group" data-letter="N">
      <div class="chocopedia-letter" id="letter-N">N</div>
      <div class="chocopedia-term" data-term="nib">
        <h4>Nib <span class="chocopedia-term-tag">Ingredient</span></h4>
        <p>Pieces of roasted, shelled cacao beans. Nibs are the purest form of chocolate and can be eaten as-is (crunchy, intensely flavoured) or ground into cocoa liquor. They're increasingly popular as a healthy, antioxidant-rich snack and baking ingredient.</p>
      </div>
    </div>

    <!-- Letter R -->
    <div class="chocopedia-letter-group" data-letter="R">
      <div class="chocopedia-letter" id="letter-R">R</div>
      <div class="chocopedia-term" data-term="roasting">
        <h4>Roasting <span class="chocopedia-term-tag">Process</span></h4>
        <p>Heat treatment of cacao beans at 120-160°C. Roasting develops flavour via Maillard reactions and Strecker degradation, reduces moisture, loosens the shell for winnowing, and kills potentially harmful microorganisms. Roast profiles are a maker's signature — varying temperature, time, and airflow to bring out specific flavour notes.</p>
      </div>
      <div class="chocopedia-term" data-term="ruby chocolate">
        <h4>Ruby Chocolate <span class="chocopedia-term-tag">Type</span></h4>
        <p>The fourth type of chocolate (after dark, milk, white) launched by Barry Callebaut in 2017. Made from specially selected "ruby" cacao beans, it has a natural pink hue and a berry-like, slightly tart flavour profile without the addition of any colouring or fruit flavouring.</p>
      </div>
    </div>

    <!-- Letter T -->
    <div class="chocopedia-letter-group" data-letter="T">
      <div class="chocopedia-letter" id="letter-T">T</div>
      <div class="chocopedia-term" data-term="tempering">
        <h4>Tempering <span class="chocopedia-term-tag">Process</span></h4>
        <p>The controlled process of heating, cooling, and reheating chocolate to encourage the formation of stable Form V cocoa butter crystals. Properly tempered chocolate has a glossy finish, satisfying snap, smooth mouthfeel, and resists bloom. The classic method involves tabling (spreading on marble), while modern approaches use seeding or Mycryo techniques.</p>
      </div>
      <div class="chocopedia-term" data-term="theobromine">
        <h4>Theobromine <span class="chocopedia-term-tag">Science</span></h4>
        <p>The primary alkaloid in cacao (from Greek: theobroma = "food of the gods"). A mild stimulant similar to caffeine but with a gentler, longer-lasting effect. It's responsible for chocolate's slight bitterness and part of its mood-enhancing properties. Toxic to dogs and cats due to their slow metabolization.</p>
      </div>
      <div class="chocopedia-term" data-term="trinitario">
        <h4>Trinitario <span class="chocopedia-term-tag">Bean</span></h4>
        <p>A hybrid cacao variety that originated in Trinidad in the 18th century from a natural cross between Criollo and Forastero. Trinitario combines some of Criollo's fine flavour complexity with Forastero's vigour and disease resistance. It comprises 10-15% of world production and is prized for premium chocolate.</p>
      </div>
    </div>

    <!-- Letter W -->
    <div class="chocopedia-letter-group" data-letter="W">
      <div class="chocopedia-letter" id="letter-W">W</div>
      <div class="chocopedia-term" data-term="winnowing">
        <h4>Winnowing <span class="chocopedia-term-tag">Process</span></h4>
        <p>The process of separating roasted cacao nibs from their papery shells (husks). Traditionally done by hand-cracking and air-blowing, modern winnowers use vibrating screens and air currents. Clean winnowing is critical — residual shell fragments create off-flavours and gritty texture in the final chocolate.</p>
      </div>
      <div class="chocopedia-term" data-term="white chocolate">
        <h4>White Chocolate <span class="chocopedia-term-tag">Type</span></h4>
        <p>Made from cocoa butter, sugar, and milk solids — containing no cocoa solids. Must contain at least 20% cocoa butter by weight (EU/FDA standards). Often debated whether it qualifies as "real" chocolate since it lacks the cocoa solids that define dark and milk varieties.</p>
      </div>
    </div>

  </div>
</div>

<script>
function filterChocopedia(query) {
  query = query.toLowerCase().trim();
  const groups = document.querySelectorAll('.chocopedia-letter-group');
  groups.forEach(group => {
    const terms = group.querySelectorAll('.chocopedia-term');
    let anyVisible = false;
    terms.forEach(term => {
      const text = term.textContent.toLowerCase();
      const match = !query || text.includes(query);
      term.style.display = match ? '' : 'none';
      if (match) anyVisible = true;
    });
    group.style.display = anyVisible ? '' : 'none';
  });
}
function scrollToLetter(letter) {
  const el = document.getElementById('letter-' + letter);
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<?php include 'includes/footer.php'; ?>
