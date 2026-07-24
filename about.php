<?php
  $pageTitle = "About Aarti Saluja Sahni, Chocolate Educator | RT Chocos";
  $pageDescription = "Meet Aarti Saluja Sahni, founder of RT Chocos. Over a decade of bean-to-bar chocolate making, consulting, and recipe formulation experience in Mumbai, India.";
  $pathPrefix = "";
  $canonicalUrl = "https://www.rtchocos.com/about.php";
  $schemaType = "ProfilePage";
  
  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'About Us', 'item' => $canonicalUrl]
  ];
  include $pathPrefix . 'includes/header.php';
?>

<!-- --- ABOUT PAGE --- -->
<div id="page-about" class="page active" style="padding-top:80px;">
  <!-- About Hero -->
  <div class="page-hero about-page-hero">
    <div class="about-page-hero-image">
      <img src="assets/about_banner.png" alt="Botanical eucalyptus and cacao pod banner layout" />
    </div>
    <div class="page-hero-content">
      <h1 class="fade-up">About RT Chocos</h1>
      <p class="fade-up-d1">From ancient rituals to artisan bars - honouring cacao's true origins.</p>
    </div>
  </div>

  <div class="section" style="background-color: var(--cream); padding-top: 28px;">
    <!-- Biography Grid -->
    <div class="about-grid" style="margin-bottom: 60px;">
      <div style="max-width: 820px;">
        <div class="section-label">Our Story</div>
        <h2 class="section-title">I Never Planned to Fall<br>in Love with Chocolate.</h2>
        <div class="divider"></div>
        <p class="lead-text" style="font-size:17px; font-weight:500; margin-top:20px; color: var(--brown);">But some of life's most meaningful chapters begin with an unexpected gift.</p>
        <p style="margin-top:16px;">I started with balance sheets, not ganache. A finance graduate from Banasthali University with an MBA and a clear professional path ahead — numbers were my language, spreadsheets my world. Chocolate? That was just something I loved to eat.</p>
        <p style="margin-top:16px;">Then, in 2012, a friend handed me a small box of handmade chocolates. I took one bite — and something shifted. It wasn't just the flavour; it was the realization that someone had crafted this by hand with intention and care. That single moment rewired everything, proving that analytical systems and culinary passion could merge.</p>
        <p style="margin-top:16px;">By 2014, I left corporate finance behind to enroll in my first professional chocolate class in Ludhiana, Punjab, where the crystal physics behind tempering curves clicked. Soon after in 2016, I scaled home production to supply luxury cafes and hotels while organizing hands-on workshops to empower aspiring female home-bakers.</p>
        <p style="margin-top:16px;">When 2020 brought global lockdowns, I transitioned physical workshops into interactive live online masterclasses, connecting chocolate lovers across the globe. By 2022, I took on the role of Head of NPD (New Product Development) & Production for a premier Indian chocolate brand in Mumbai, managing industrial conching, refining, and commercial batch scaling.</p>
        <p style="margin-top:16px;">Today, I work as a freelance recipe developer and chocolate consultant, partnering directly with cacao plantations — auditing post-harvest fermentation setups and formulating signature bean-to-bar recipes. Tracing how every decision from soil to drying bed shapes what lands on your palate is where the real story begins.</p>
      </div>
      <div style="display:flex; justify-content:center; align-items:flex-start; padding-top:4px;">
        <img src="assets/myphoto.jpg" alt="Portrait of Aarti Saluja Sahni, founder of RT Chocos" style="width:100%; max-width:440px; height:auto; display:block; object-fit:contain; border-radius:24px; box-shadow: var(--shadow-lg);">
      </div>
    </div>

    <!-- AI Career Interviewer -->
    <div class="ai-interview-container" style="background: rgba(18, 79, 39, 0.03); border: 1px solid rgba(18, 79, 39, 0.12); border-radius: 24px; padding: 40px; margin-bottom: 80px; position: relative; overflow: hidden;">
      <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 40px; align-items: center;">
        <div style="text-align: left;">
          <div class="section-label">AI Dialogues</div>
          <h2 style="font-family: 'Playfair Display', serif; font-size: 28px; color: var(--accent-dark); margin-bottom: 12px;">Ask Aarti's AI Companion</h2>
          <p style="font-size: 14.5px; color: var(--brown-light); line-height: 1.6; margin-bottom: 24px;">I've trained an AI companion with my systems-thinking philosophy, chocolate making principles, and career milestones. Ask any question below, or select a query chip to begin.</p>
          
          <div class="ai-prompt-chips-about" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
            <button class="ai-chip-btn" onclick="sendAboutAiPrompt('Why did you swap finance for chocolate?')">💼 MBA to Cacao</button>
            <button class="ai-chip-btn" onclick="sendAboutAiPrompt('What was your biggest scaling challenge?')">📈 Scaling Challenge</button>
            <button class="ai-chip-btn" onclick="sendAboutAiPrompt('What does a chocolate consultant do?')">🌱 Consulting Role</button>
            <button class="ai-chip-btn" onclick="sendAboutAiPrompt('How do you manage tempering crystallisation?')">🔬 Tempering Science</button>
          </div>
        </div>

        <div class="about-ai-chatbox">
          <!-- Chat box area -->
          <div id="about-ai-chat-output" style="max-height: 200px; overflow-y: auto; padding-right: 8px; display: flex; flex-direction: column; gap: 12px;">
            <div class="ai-msg bubble-received about-bubble-bot">
              Hello! I am Aarti's virtual assistant. Ask me anything about my decade of chocolate recipes testing, consulting, classes, or bean-to-bar science.
            </div>
          </div>

          <!-- Typing indicator -->
          <div id="about-ai-loader" style="display: none; align-items: center; gap: 8px; padding-left: 16px; margin-top: 10px;">
            <div class="ai-typing-indicator">
              <span class="ai-typing-dot"></span>
              <span class="ai-typing-dot"></span>
              <span class="ai-typing-dot"></span>
            </div>
            <span style="font-size: 12px; color: var(--accent-light); font-style: italic;">Aarti's Companion is formulating response...</span>
          </div>

          <!-- Chat Input -->
          <div class="about-ai-chat-input-row">
            <input type="text" id="about-ai-input" class="about-ai-input" placeholder="Type a custom question...">
            <button class="btn-primary about-ai-submit-btn" onclick="handleAboutAiSubmit()">Ask AI</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Cacao Philosophy Matcher -->
    <div class="cacao-matcher-container" style="background: var(--ivory); border: 1px solid rgba(18, 79, 39, 0.15); border-radius: 24px; padding: 40px; text-align: center; margin-bottom: 40px;">
      <div class="section-label">Self Diagnosis</div>
      <h2 style="font-family:'Playfair Display', serif; font-size: 26px; color: var(--brown); margin-bottom: 12px;">Discover Your Cacao Philosophy</h2>
      <p style="font-size: 14.5px; color: var(--brown-light); max-width: 580px; margin: 0 auto 32px;">Select the area of chocolate making that fascinates you the most, and we'll dynamically matches you to your ideal chocolate learning path.</p>

      <div class="matcher-options-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; max-width: 900px; margin: 0 auto 32px;">
        <div class="matcher-card" onclick="selectPhilosophy('science', this)">
          <div style="font-size: 28px; margin-bottom: 8px;">🔬</div>
          <h4 style="font-size: 14px; font-weight: 600; color: var(--brown);">Tempering Physics</h4>
          <p style="font-size: 12px; margin-top: 6px; color: var(--brown-light); opacity: 0.85;">Crystal structures, working curves, cooling chemistry.</p>
        </div>
        <div class="matcher-card" onclick="selectPhilosophy('practical', this)">
          <div style="font-size: 28px; margin-bottom: 8px;">👩‍🍳</div>
          <h4 style="font-size: 14px; font-weight: 600; color: var(--brown);">Practical Ganache</h4>
          <p style="font-size: 12px; margin-top: 6px; color: var(--brown-light); opacity: 0.85;">Ganache emulsions, bonbon painting, hand-rolling truffles.</p>
        </div>
        <div class="matcher-card" onclick="selectPhilosophy('farms', this)">
          <div style="font-size: 28px; margin-bottom: 8px;">🌱</div>
          <h4 style="font-size: 14px; font-weight: 600; color: var(--brown);">Cacao Sourcing</h4>
          <p style="font-size: 12px; margin-top: 6px; color: var(--brown-light); opacity: 0.85;">Orchard fermentation setups, bean roasting profiles.</p>
        </div>
        <div class="matcher-card" onclick="selectPhilosophy('scaling', this)">
          <div style="font-size: 28px; margin-bottom: 8px;">📈</div>
          <h4 style="font-size: 14px; font-weight: 600; color: var(--brown);">Batch Scaling</h4>
          <p style="font-size: 12px; margin-top: 6px; color: var(--brown-light); opacity: 0.85;">Translating kitchen recipes into commercial formulations.</p>
        </div>
      </div>

      <!-- Result Card -->
      <div id="matcher-result-box" style="display: none; background: rgba(18, 79, 39, 0.05); border: 1px dashed var(--accent); border-radius: 16px; padding: 24px; max-width: 600px; margin: 0 auto; animation: fadeIn 0.4s ease;">
        <span style="font-size: 11px; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: 1.5px;">Your Cacao Match</span>
        <h3 id="matcher-result-title" style="font-family:'Playfair Display', serif; font-size: 22px; color: var(--brown); margin: 8px 0 12px;"></h3>
        <p id="matcher-result-desc" style="font-size: 14px; color: var(--brown-light); line-height: 1.6; margin: 0;"></p>
      </div>
    </div>
  </div>
</div>

<?php
  include $pathPrefix . 'includes/footer.php';
?>
