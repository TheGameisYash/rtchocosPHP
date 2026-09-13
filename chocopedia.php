<?php
$pathPrefix = "";
$isHome = false;
$pageTitle = "Chocopedia — The Chocolate Encyclopedia | RT Chocos";
$pageDescription = "Your comprehensive guide to chocolate. Explore bean-to-bar terms, cacao science, tempering techniques, and everything chocolate from A to Z.";
$schemaType = "CollectionPage";
$breadcrumbs = [
    ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
    ['name' => 'Chocopedia', 'item' => 'https://www.rtchocos.com/chocopedia']
];
include 'includes/header.php';
?>

<!-- --- CHOCOPEDIA PAGE WRAPPER --- -->
<div id="page-chocopedia" class="page active" style="padding-top: 100px;">

  <!-- CHOCOPEDIA HERO -->
  <section class="chocopedia-hero" style="padding-top: 24px !important;">
    <div class="section-label" style="text-align: center; margin-bottom: 12px; display: inline-block;">📖 Bean-to-Bar Glossary &amp; Science</div>
    <h1 style="margin-top: 6px !important;">Chocopedia</h1>
    <p>Your A-to-Z encyclopedia of chocolate. From bean varieties to tempering science — everything you need to know.</p>
    
    <div class="chocopedia-search">
      <span class="search-icon">
        <svg width="18" height="18" style="width:18px; height:18px; min-width:18px; max-width:18px; display:block;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </span>
      <input type="text" id="chocopedia-search-input" placeholder="Search terms (e.g. tempering, conching, criollo)..." oninput="handleSearchInput(this.value)" autocomplete="off">
      <button type="button" id="search-clear-btn" class="search-clear-btn" onclick="clearSearchInput()" aria-label="Clear Search">✕</button>
    </div>
  </section>

  <!-- CATEGORIES & GLOSSARY CONTAINER -->
  <div class="section" style="padding-top: 14px;">
    <div class="section-label" style="text-align: center;">Browse by Category</div>
    <h2 class="section-title" style="text-align: center; margin-bottom: 32px;">Explore the World of Chocolate</h2>

    <!-- Category Filter Cards -->
    <div class="chocopedia-categories">
      <div class="chocopedia-cat-card" data-category="Bean" onclick="toggleCategoryFilter('Bean', this)">
        <div class="chocopedia-cat-icon">🫘</div>
        <h3>Bean Origins</h3>
        <p>Cacao varietals, terroir, and growing regions around the world.</p>
      </div>
      <div class="chocopedia-cat-card" data-category="Process,Technique" onclick="toggleCategoryFilter('Process,Technique', this)">
        <div class="chocopedia-cat-icon">⚗️</div>
        <h3>Craft &amp; Process</h3>
        <p>From roasting and winnowing to conching and tempering.</p>
      </div>
      <div class="chocopedia-cat-card" data-category="Type" onclick="toggleCategoryFilter('Type', this)">
        <div class="chocopedia-cat-icon">🍫</div>
        <h3>Chocolate Types</h3>
        <p>Dark, milk, white, ruby, and everything in between.</p>
      </div>
      <div class="chocopedia-cat-card" data-category="Science,Ingredient" onclick="toggleCategoryFilter('Science,Ingredient', this)">
        <div class="chocopedia-cat-icon">🔬</div>
        <h3>Food Science</h3>
        <p>Crystal polymorphs, bloom, emulsification, and cocoa chemistry.</p>
      </div>
    </div>

    <!-- Active Filter Feedback Banner -->
    <div id="chocopedia-filter-banner" class="chocopedia-filter-banner" style="display: none;">
      <span class="filter-banner-text" id="filter-banner-text">Showing filtered terms</span>
      <button type="button" class="filter-banner-reset" onclick="resetChocopediaFilters()">Clear Filter (Show All 28 Terms)</button>
    </div>

    <!-- Alphabet Quick Navigation Bar -->
    <nav class="chocopedia-alpha-nav" aria-label="Alphabet Jump Navigation">
      <span class="alpha-nav-label">A–Z Index:</span>
      <button type="button" class="alpha-nav-chip active" onclick="scrollToLetter('ALL')">All</button>
      <?php
        $activeLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'L', 'M', 'N', 'R', 'T', 'W'];
        foreach (range('A', 'Z') as $char):
          $hasLetter = in_array($char, $activeLetters);
      ?>
        <button type="button" class="alpha-nav-chip <?= $hasLetter ? '' : 'disabled' ?>" <?= $hasLetter ? 'onclick="scrollToLetter(\''.$char.'\')"' : '' ?>><?= $char ?></button>
      <?php endforeach; ?>
    </nav>

    <!-- Empty State -->
    <div id="chocopedia-empty-state" class="chocopedia-empty-state" style="display: none;">
      <div class="empty-icon">🔍</div>
      <h3>No Chocolate Terms Found</h3>
      <p id="empty-query-msg">We couldn't find any terms matching your criteria.</p>
      <button type="button" class="btn-clear-search" onclick="resetChocopediaFilters()">Reset Search &amp; Show All Terms</button>
    </div>

    <!-- GLOSSARY LIST -->
    <div class="chocopedia-glossary" id="chocopedia-glossary">
      
      <!-- Letter A -->
      <div class="chocopedia-letter-group" data-letter="A">
        <div class="chocopedia-letter" id="letter-A">A <span class="chocopedia-letter-count">(2)</span></div>
        <div class="chocopedia-terms-list">
          <div class="chocopedia-term" data-term="alkalization" data-category="Process">
            <div class="chocopedia-term-header">
              <h4>Alkalization</h4>
              <span class="chocopedia-term-tag tag-process">Process</span>
            </div>
            <p>Also known as "Dutching" — a chemical process where cocoa is treated with an alkaline solution to neutralize its natural acidity. This darkens the colour, mellows the flavour, and improves solubility. Invented by Coenraad Johannes van Houten in 1828.</p>
          </div>
          <div class="chocopedia-term" data-term="arriba nacional" data-category="Bean">
            <div class="chocopedia-term-header">
              <h4>Arriba Nacional</h4>
              <span class="chocopedia-term-tag tag-bean">Bean</span>
            </div>
            <p>A fine-flavour cacao variety native to Ecuador's coastal lowlands. Known for its distinctive floral and jasmine-like aroma, Arriba is one of the world's most prized cacao origins, contributing to Ecuador's status as a top fine-flavour exporter.</p>
          </div>
        </div>
      </div>

      <!-- Letter B -->
      <div class="chocopedia-letter-group" data-letter="B">
        <div class="chocopedia-letter" id="letter-B">B <span class="chocopedia-letter-count">(3)</span></div>
        <div class="chocopedia-terms-list">
          <div class="chocopedia-term" data-term="bean-to-bar" data-category="Process">
            <div class="chocopedia-term-header">
              <h4>Bean-to-Bar</h4>
              <span class="chocopedia-term-tag tag-process">Process</span>
            </div>
            <p>A chocolate-making philosophy where the maker controls every step — from sourcing raw cacao beans to producing the finished bar. This approach prioritizes traceability, craftsmanship, and flavour transparency over industrial efficiency.</p>
          </div>
          <div class="chocopedia-term" data-term="bloom" data-category="Science">
            <div class="chocopedia-term-header">
              <h4>Bloom</h4>
              <span class="chocopedia-term-tag tag-science">Science</span>
            </div>
            <p>A whitish-grey haze that appears on chocolate's surface. Fat bloom is caused by unstable cocoa butter crystals migrating to the surface; sugar bloom results from moisture dissolving surface sugar. Neither is harmful but affects appearance and texture.</p>
          </div>
          <div class="chocopedia-term" data-term="broma process" data-category="Process">
            <div class="chocopedia-term-header">
              <h4>Broma Process</h4>
              <span class="chocopedia-term-tag tag-process">Process</span>
            </div>
            <p>A method of extracting cocoa butter from cacao by hanging bags of roasted, ground cocoa beans in a warm room. The heat melts the butter, which drips out, leaving behind a lower-fat cocoa cake that can be ground into cocoa powder.</p>
          </div>
        </div>
      </div>

      <!-- Letter C -->
      <div class="chocopedia-letter-group" data-letter="C">
        <div class="chocopedia-letter" id="letter-C">C <span class="chocopedia-letter-count">(4)</span></div>
        <div class="chocopedia-terms-list">
          <div class="chocopedia-term" data-term="cacao" data-category="Bean">
            <div class="chocopedia-term-header">
              <h4>Cacao</h4>
              <span class="chocopedia-term-tag tag-bean">Bean</span>
            </div>
            <p>The raw, unprocessed seed of the Theobroma cacao tree. "Cacao" typically refers to the bean in its natural state — before roasting, fermenting, and processing into what we know as "cocoa" or chocolate.</p>
          </div>
          <div class="chocopedia-term" data-term="conching" data-category="Process">
            <div class="chocopedia-term-header">
              <h4>Conching</h4>
              <span class="chocopedia-term-tag tag-process">Process</span>
            </div>
            <p>A critical refining step invented by Rodolphe Lindt in 1879. The chocolate mass is continuously agitated and aerated at controlled temperatures for hours (or days). This develops flavour, reduces particle size, drives off volatile acids, and creates chocolate's signature smooth mouthfeel.</p>
          </div>
          <div class="chocopedia-term" data-term="couverture" data-category="Type">
            <div class="chocopedia-term-header">
              <h4>Couverture</h4>
              <span class="chocopedia-term-tag tag-type">Type</span>
            </div>
            <p>A high-quality chocolate with a minimum 31% cocoa butter content (EU standard). The higher fat ratio gives couverture superior fluidity when melted, making it the professional's choice for tempering, moulding, enrobing, and ganache work.</p>
          </div>
          <div class="chocopedia-term" data-term="criollo" data-category="Bean">
            <div class="chocopedia-term-header">
              <h4>Criollo</h4>
              <span class="chocopedia-term-tag tag-bean">Bean</span>
            </div>
            <p>The rarest and most prized of the three main cacao varietals, comprising less than 5% of world production. Criollo beans are known for their complex, delicate flavour profiles with minimal bitterness — often featuring notes of nuts, caramel, and tropical fruit.</p>
          </div>
        </div>
      </div>

      <!-- Letter D -->
      <div class="chocopedia-letter-group" data-letter="D">
        <div class="chocopedia-letter" id="letter-D">D <span class="chocopedia-letter-count">(2)</span></div>
        <div class="chocopedia-terms-list">
          <div class="chocopedia-term" data-term="dark chocolate" data-category="Type">
            <div class="chocopedia-term-header">
              <h4>Dark Chocolate</h4>
              <span class="chocopedia-term-tag tag-type">Type</span>
            </div>
            <p>Chocolate containing cocoa solids, cocoa butter, and sugar — without any milk solids. Ranges from 50% to 100% cacao. Higher percentages yield more intense, bitter flavours, while lower percentages are sweeter and more accessible.</p>
          </div>
          <div class="chocopedia-term" data-term="dutching" data-category="Process">
            <div class="chocopedia-term-header">
              <h4>Dutching</h4>
              <span class="chocopedia-term-tag tag-process">Process</span>
            </div>
            <p>See <em>Alkalization</em>. Named after Dutch chemist Coenraad van Houten who pioneered the technique. The process raises cocoa's pH from ~5.0 to 7-8, resulting in a darker colour and less acidic taste.</p>
          </div>
        </div>
      </div>

      <!-- Letter E -->
      <div class="chocopedia-letter-group" data-letter="E">
        <div class="chocopedia-letter" id="letter-E">E <span class="chocopedia-letter-count">(1)</span></div>
        <div class="chocopedia-terms-list">
          <div class="chocopedia-term" data-term="enrobing" data-category="Process">
            <div class="chocopedia-term-header">
              <h4>Enrobing</h4>
              <span class="chocopedia-term-tag tag-process">Process</span>
            </div>
            <p>The process of coating a confection centre (truffle, biscuit, fruit, etc.) with a thin, even layer of tempered chocolate. Industrial enrobers use a curtain of flowing chocolate, while artisans may dip centres by hand using a fork or dipping tool.</p>
          </div>
        </div>
      </div>

      <!-- Letter F -->
      <div class="chocopedia-letter-group" data-letter="F">
        <div class="chocopedia-letter" id="letter-F">F <span class="chocopedia-letter-count">(3)</span></div>
        <div class="chocopedia-terms-list">
          <div class="chocopedia-term" data-term="fermentation" data-category="Process">
            <div class="chocopedia-term-header">
              <h4>Fermentation</h4>
              <span class="chocopedia-term-tag tag-process">Process</span>
            </div>
            <p>A 5-7 day process crucial to chocolate flavour development. Freshly harvested cacao beans are heaped in wooden boxes or banana leaves, where yeasts and bacteria break down the sugary pulp. This generates heat (up to 50°C), kills the embryo, and triggers complex biochemical reactions that develop the precursor flavour compounds.</p>
          </div>
          <div class="chocopedia-term" data-term="forastero" data-category="Bean">
            <div class="chocopedia-term-header">
              <h4>Forastero</h4>
              <span class="chocopedia-term-tag tag-bean">Bean</span>
            </div>
            <p>The most widely cultivated cacao variety, accounting for 80-90% of global production. Forastero is hardier and more disease-resistant than Criollo, but generally produces more robust, straightforward flavours with higher bitterness and astringency.</p>
          </div>
          <div class="chocopedia-term" data-term="form v" data-category="Science">
            <div class="chocopedia-term-header">
              <h4>Form V (Beta-2)</h4>
              <span class="chocopedia-term-tag tag-science">Science</span>
            </div>
            <p>The ideal crystalline structure of tempered cocoa butter. Of the six polymorphic forms (I-VI) that cocoa butter can crystallise in, Form V produces the characteristic glossy sheen, firm snap, smooth texture, and resistance to bloom that defines well-tempered chocolate.</p>
          </div>
        </div>
      </div>

      <!-- Letter G -->
      <div class="chocopedia-letter-group" data-letter="G">
        <div class="chocopedia-letter" id="letter-G">G <span class="chocopedia-letter-count">(2)</span></div>
        <div class="chocopedia-terms-list">
          <div class="chocopedia-term" data-term="ganache" data-category="Technique">
            <div class="chocopedia-term-header">
              <h4>Ganache</h4>
              <span class="chocopedia-term-tag tag-technique">Technique</span>
            </div>
            <p>An emulsion of chocolate and cream (and sometimes butter). The ratio of chocolate to cream determines the ganache's consistency: 2:1 for truffles, 1:1 for frosting, and 1:2 for a pourable glaze. Proper emulsification — combining in stages — is key to a smooth, stable ganache.</p>
          </div>
          <div class="chocopedia-term" data-term="gianduja" data-category="Type">
            <div class="chocopedia-term-header">
              <h4>Gianduja</h4>
              <span class="chocopedia-term-tag tag-type">Type</span>
            </div>
            <p>An Italian chocolate confection made by blending chocolate (usually milk) with at least 30% finely ground hazelnuts. Originating in Turin around 1852, it was created as a response to cocoa shortages. Nutella is a mass-market descendant of this craft tradition.</p>
          </div>
        </div>
      </div>

      <!-- Letter L -->
      <div class="chocopedia-letter-group" data-letter="L">
        <div class="chocopedia-letter" id="letter-L">L <span class="chocopedia-letter-count">(1)</span></div>
        <div class="chocopedia-terms-list">
          <div class="chocopedia-term" data-term="lecithin" data-category="Ingredient">
            <div class="chocopedia-term-header">
              <h4>Lecithin</h4>
              <span class="chocopedia-term-tag tag-ingredient">Ingredient</span>
            </div>
            <p>An emulsifier (usually soy or sunflower derived) added to chocolate at 0.3-0.5% to improve viscosity and flow. It reduces the amount of cocoa butter needed for a smooth texture, making production more efficient. Some bean-to-bar purists avoid it.</p>
          </div>
        </div>
      </div>

      <!-- Letter M -->
      <div class="chocopedia-letter-group" data-letter="M">
        <div class="chocopedia-letter" id="letter-M">M <span class="chocopedia-letter-count">(2)</span></div>
        <div class="chocopedia-terms-list">
          <div class="chocopedia-term" data-term="maillard reaction" data-category="Science">
            <div class="chocopedia-term-header">
              <h4>Maillard Reaction</h4>
              <span class="chocopedia-term-tag tag-science">Science</span>
            </div>
            <p>A non-enzymatic browning reaction between amino acids and reducing sugars that occurs during cacao roasting (and baking). It generates hundreds of volatile compounds responsible for the complex flavour spectrum of roasted cocoa — from nutty and caramel to floral and smoky notes.</p>
          </div>
          <div class="chocopedia-term" data-term="melanging" data-category="Process">
            <div class="chocopedia-term-header">
              <h4>Melanging</h4>
              <span class="chocopedia-term-tag tag-process">Process</span>
            </div>
            <p>Grinding and refining chocolate using a stone melanger (a rotating granite wheel on a granite base). Popular among small-batch and bean-to-bar makers as it combines grinding, refining, and sometimes conching in a single machine. Typical melange time ranges from 24 to 72 hours.</p>
          </div>
        </div>
      </div>

      <!-- Letter N -->
      <div class="chocopedia-letter-group" data-letter="N">
        <div class="chocopedia-letter" id="letter-N">N <span class="chocopedia-letter-count">(1)</span></div>
        <div class="chocopedia-terms-list">
          <div class="chocopedia-term" data-term="nib" data-category="Ingredient">
            <div class="chocopedia-term-header">
              <h4>Nib</h4>
              <span class="chocopedia-term-tag tag-ingredient">Ingredient</span>
            </div>
            <p>Pieces of roasted, shelled cacao beans. Nibs are the purest form of chocolate and can be eaten as-is (crunchy, intensely flavoured) or ground into cocoa liquor. They're increasingly popular as a healthy, antioxidant-rich snack and baking ingredient.</p>
          </div>
        </div>
      </div>

      <!-- Letter R -->
      <div class="chocopedia-letter-group" data-letter="R">
        <div class="chocopedia-letter" id="letter-R">R <span class="chocopedia-letter-count">(2)</span></div>
        <div class="chocopedia-terms-list">
          <div class="chocopedia-term" data-term="roasting" data-category="Process">
            <div class="chocopedia-term-header">
              <h4>Roasting</h4>
              <span class="chocopedia-term-tag tag-process">Process</span>
            </div>
            <p>Heat treatment of cacao beans at 120-160°C. Roasting develops flavour via Maillard reactions and Strecker degradation, reduces moisture, loosens the shell for winnowing, and kills potentially harmful microorganisms. Roast profiles are a maker's signature — varying temperature, time, and airflow to bring out specific flavour notes.</p>
          </div>
          <div class="chocopedia-term" data-term="ruby chocolate" data-category="Type">
            <div class="chocopedia-term-header">
              <h4>Ruby Chocolate</h4>
              <span class="chocopedia-term-tag tag-type">Type</span>
            </div>
            <p>The fourth type of chocolate (after dark, milk, white) launched by Barry Callebaut in 2017. Made from specially selected "ruby" cacao beans, it has a natural pink hue and a berry-like, slightly tart flavour profile without the addition of any colouring or fruit flavouring.</p>
          </div>
        </div>
      </div>

      <!-- Letter T -->
      <div class="chocopedia-letter-group" data-letter="T">
        <div class="chocopedia-letter" id="letter-T">T <span class="chocopedia-letter-count">(3)</span></div>
        <div class="chocopedia-terms-list">
          <div class="chocopedia-term" data-term="tempering" data-category="Process">
            <div class="chocopedia-term-header">
              <h4>Tempering</h4>
              <span class="chocopedia-term-tag tag-process">Process</span>
            </div>
            <p>The controlled process of heating, cooling, and reheating chocolate to encourage the formation of stable Form V cocoa butter crystals. Properly tempered chocolate has a glossy finish, satisfying snap, smooth mouthfeel, and resists bloom. The classic method involves tabling (spreading on marble), while modern approaches use seeding or Mycryo techniques.</p>
          </div>
          <div class="chocopedia-term" data-term="theobromine" data-category="Science">
            <div class="chocopedia-term-header">
              <h4>Theobromine</h4>
              <span class="chocopedia-term-tag tag-science">Science</span>
            </div>
            <p>The primary alkaloid in cacao (from Greek: theobroma = "food of the gods"). A mild stimulant similar to caffeine but with a gentler, longer-lasting effect. It's responsible for chocolate's slight bitterness and part of its mood-enhancing properties. Toxic to dogs and cats due to their slow metabolization.</p>
          </div>
          <div class="chocopedia-term" data-term="trinitario" data-category="Bean">
            <div class="chocopedia-term-header">
              <h4>Trinitario</h4>
              <span class="chocopedia-term-tag tag-bean">Bean</span>
            </div>
            <p>A hybrid cacao variety that originated in Trinidad in the 18th century from a natural cross between Criollo and Forastero. Trinitario combines some of Criollo's fine flavour complexity with Forastero's vigour and disease resistance. It comprises 10-15% of world production and is prized for premium chocolate.</p>
          </div>
        </div>
      </div>

      <!-- Letter W -->
      <div class="chocopedia-letter-group" data-letter="W">
        <div class="chocopedia-letter" id="letter-W">W <span class="chocopedia-letter-count">(2)</span></div>
        <div class="chocopedia-terms-list">
          <div class="chocopedia-term" data-term="winnowing" data-category="Process">
            <div class="chocopedia-term-header">
              <h4>Winnowing</h4>
              <span class="chocopedia-term-tag tag-process">Process</span>
            </div>
            <p>The process of separating roasted cacao nibs from their papery shells (husks). Traditionally done by hand-cracking and air-blowing, modern winnowers use vibrating screens and air currents. Clean winnowing is critical — residual shell fragments create off-flavours and gritty texture in the final chocolate.</p>
          </div>
          <div class="chocopedia-term" data-term="white chocolate" data-category="Type">
            <div class="chocopedia-term-header">
              <h4>White Chocolate</h4>
              <span class="chocopedia-term-tag tag-type">Type</span>
            </div>
            <p>Made from cocoa butter, sugar, and milk solids — containing no cocoa solids. Must contain at least 20% cocoa butter by weight (EU/FDA standards). Often debated whether it qualifies as "real" chocolate since it lacks the cocoa solids that define dark and milk varieties.</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
let currentCategoryFilter = null;
let currentSearchQuery = '';

function handleSearchInput(val) {
  currentSearchQuery = val.trim();
  const clearBtn = document.getElementById('search-clear-btn');
  if (clearBtn) {
    clearBtn.style.display = currentSearchQuery ? 'flex' : 'none';
  }
  applyFilters();
}

function clearSearchInput() {
  const input = document.getElementById('chocopedia-search-input');
  if (input) input.value = '';
  currentSearchQuery = '';
  const clearBtn = document.getElementById('search-clear-btn');
  if (clearBtn) clearBtn.style.display = 'none';
  applyFilters();
}

function toggleCategoryFilter(catStr, cardEl) {
  const cats = catStr.split(',');
  const isCurrentlyActive = cardEl.classList.contains('active');

  // Deactivate all cards first
  document.querySelectorAll('.chocopedia-cat-card').forEach(c => c.classList.remove('active'));

  if (isCurrentlyActive) {
    currentCategoryFilter = null;
  } else {
    cardEl.classList.add('active');
    currentCategoryFilter = cats;
    
    // Smooth scroll down to glossary list
    const glossary = document.getElementById('chocopedia-glossary');
    if (glossary) {
      const topOffset = glossary.getBoundingClientRect().top + window.pageYOffset - 110;
      window.scrollTo({ top: topOffset, behavior: 'smooth' });
    }
  }
  applyFilters();
}

function resetChocopediaFilters() {
  clearSearchInput();
  document.querySelectorAll('.chocopedia-cat-card').forEach(c => c.classList.remove('active'));
  currentCategoryFilter = null;
  applyFilters();
}

function applyFilters() {
  const query = currentSearchQuery.toLowerCase();
  const groups = document.querySelectorAll('.chocopedia-letter-group');
  let totalVisible = 0;

  groups.forEach(group => {
    const terms = group.querySelectorAll('.chocopedia-term');
    let groupVisibleCount = 0;

    terms.forEach(term => {
      const text = term.textContent.toLowerCase();
      const termCat = term.getAttribute('data-category') || '';
      
      const matchesSearch = !query || text.includes(query);
      const matchesCategory = !currentCategoryFilter || currentCategoryFilter.includes(termCat);

      const isMatch = matchesSearch && matchesCategory;
      term.style.display = isMatch ? '' : 'none';
      if (isMatch) {
        groupVisibleCount++;
        totalVisible++;
      }
    });

    group.style.display = groupVisibleCount > 0 ? '' : 'none';
    const countBadge = group.querySelector('.chocopedia-letter-count');
    if (countBadge) {
      countBadge.textContent = `(${groupVisibleCount})`;
    }
  });

  // Empty state handling
  const emptyState = document.getElementById('chocopedia-empty-state');
  const emptyMsg = document.getElementById('empty-query-msg');
  const glossary = document.getElementById('chocopedia-glossary');

  if (totalVisible === 0) {
    if (emptyState) emptyState.style.display = 'block';
    if (emptyMsg) {
      if (currentSearchQuery && currentCategoryFilter) {
        emptyMsg.textContent = `No terms matching "${currentSearchQuery}" in the selected category.`;
      } else if (currentSearchQuery) {
        emptyMsg.textContent = `No chocolate terms found matching "${currentSearchQuery}".`;
      } else {
        emptyMsg.textContent = `No terms found for this filter.`;
      }
    }
    if (glossary) glossary.style.display = 'none';
  } else {
    if (emptyState) emptyState.style.display = 'none';
    if (glossary) glossary.style.display = '';
  }

  // Filter banner update
  const banner = document.getElementById('chocopedia-filter-banner');
  const bannerText = document.getElementById('filter-banner-text');
  if (banner && bannerText) {
    if (currentCategoryFilter || currentSearchQuery) {
      banner.style.display = 'flex';
      let msg = `Showing ${totalVisible} term${totalVisible !== 1 ? 's' : ''}`;
      if (currentCategoryFilter) {
        msg += ` in category filter`;
      }
      if (currentSearchQuery) {
        msg += ` for "${currentSearchQuery}"`;
      }
      bannerText.textContent = msg;
    } else {
      banner.style.display = 'none';
    }
  }

  // Update Alphabet Chips active / disabled state
  updateAlphaChipsState(groups);
}

function updateAlphaChipsState(groups) {
  document.querySelectorAll('.alpha-nav-chip:not([onclick*="ALL"])').forEach(chip => {
    const letter = chip.textContent.trim();
    const grp = document.querySelector(`.chocopedia-letter-group[data-letter="${letter}"]`);
    if (grp && grp.style.display !== 'none') {
      chip.classList.remove('disabled');
    } else {
      chip.classList.add('disabled');
    }
  });
}

function scrollToLetter(letter) {
  if (letter === 'ALL') {
    resetChocopediaFilters();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }
  const el = document.getElementById('letter-' + letter);
  if (el) {
    const topOffset = el.getBoundingClientRect().top + window.pageYOffset - 95;
    window.scrollTo({ top: topOffset, behavior: 'smooth' });
  }
}
</script>

<?php include 'includes/footer.php'; ?>
