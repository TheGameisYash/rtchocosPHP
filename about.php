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
        <p style="margin-top:16px;">Then, one ordinary day, a friend handed me a small box. Inside: handmade chocolates. I took one bite — and something shifted. It wasn't just the flavour. It was the realisation that someone had made this with their hands, from scratch, with intention and craft. That moment quietly rewired everything.</p>
        <p style="margin-top:16px;">That little box of chocolates didn't just change my career — it changed how I see creativity, science, and what it means to build something meaningful.</p>
        <p style="margin-top:16px;">Today, I work as a recipe developer and chocolate consultant. My focus has shifted from the kitchen to where the real story begins: the farm. Tracing how every decision from fermentation to roasting to conching shapes what eventually lands on your palate — this is what excites me far more than piping a perfect bonbon.</p>
      </div>
      <div style="display:flex; justify-content:center; align-items:flex-start; padding-top:4px;">
        <img src="assets/myphoto.jpg" alt="Portrait of Aarti Saluja Sahni, founder of RT Chocos" style="width:100%; max-width:440px; height:auto; display:block; object-fit:contain; border-radius:24px; box-shadow: var(--shadow-lg);">
      </div>
    </div>

    <!-- Career Timeline Section -->
    <div style="margin-bottom: 80px;">
      <div style="text-align: center; margin-bottom: 40px;">
        <div class="section-label">Interactive Journey</div>
        <h2 class="section-title">Milestones in Cacao</h2>
        <p class="section-subtitle" style="max-width: 580px; margin: 0 auto;">Click on any milestone checkpoint below to expand and discover the inside stories and chocolate lessons learned at each stage of my journey.</p>
      </div>

      <div class="timeline-container">
        <!-- Milestone 1 -->
        <div class="timeline-node" onclick="toggleTimelineMilestone(this)">
          <div class="timeline-header">
            <span class="timeline-year">2012</span>
            <h4 class="timeline-title">🎓 The Spreadsheet Shift</h4>
            <span class="timeline-icon">▼</span>
          </div>
          <div class="timeline-body">
            <p><strong>The Context:</strong> Finance MBA graduate, numbers enthusiast, and corporate-bound. Then came that unexpected box of handmade truffles.</p>
            <p><strong>Cacao Lesson:</strong> Creativity and systems are not mutually exclusive. An analytical mindset is the secret weapon to understanding precision chocolate science.</p>
          </div>
        </div>

        <!-- Milestone 2 -->
        <div class="timeline-node" onclick="toggleTimelineMilestone(this)">
          <div class="timeline-header">
            <span class="timeline-year">2014</span>
            <h4 class="timeline-title">🧪 Ludhiana Cacao Discovery</h4>
            <span class="timeline-icon">▼</span>
          </div>
          <div class="timeline-body">
            <p><strong>The Context:</strong> Far from home, far from spreadsheets, and enrolling in my first chocolate class in Ludhiana, Punjab. The magic of tempering curves clicked.</p>
            <p><strong>Cacao Lesson:</strong> Tempering is not a mystery; it is crystal physics. Controlling cocoa butter crystallization curves defines professional finish.</p>
          </div>
        </div>

        <!-- Milestone 3 -->
        <div class="timeline-node" onclick="toggleTimelineMilestone(this)">
          <div class="timeline-header">
            <span class="timeline-year">2016</span>
            <h4 class="timeline-title">🚀 Kitchen to Corporate Clients</h4>
            <span class="timeline-icon">▼</span>
          </div>
          <div class="timeline-body">
            <p><strong>The Context:</strong> Scaling production in the home kitchen, supplying high-end cafes, hotels, and organizing the first hands-on workshops for women home-bakers.</p>
            <p><strong>Cacao Lesson:</strong> True success is empowering others. Helping aspiring home-chocolatiers scale their business is as rewarding as formulating the perfect bar.</p>
          </div>
        </div>

        <!-- Milestone 4 -->
        <div class="timeline-node" onclick="toggleTimelineMilestone(this)">
          <div class="timeline-header">
            <span class="timeline-year">2020</span>
            <h4 class="timeline-title">🌐 Going Virtual</h4>
            <span class="timeline-icon">▼</span>
          </div>
          <div class="timeline-body">
            <p><strong>The Context:</strong> Lockdowns hit. Transitioning physical workshops to high-definition live online classes, opening classrooms to international students across time zones.</p>
            <p><strong>Cacao Lesson:</strong> Passion translates across screens. Cacao acts as a universal bridge, linking enthusiasts from Mumbai to London to New York.</p>
          </div>
        </div>

        <!-- Milestone 5 -->
        <div class="timeline-node" onclick="toggleTimelineMilestone(this)">
          <div class="timeline-header">
            <span class="timeline-year">2022</span>
            <h4 class="timeline-title">💼 Scaling up in Mumbai</h4>
            <span class="timeline-icon">▼</span>
          </div>
          <div class="timeline-body">
            <p><strong>The Context:</strong> Joining a top Indian chocolate brand as Head of NPD (New Product Development) & Production in Mumbai. Handling industrial refining, conching, and industrial scaling.</p>
            <p><strong>Cacao Lesson:</strong> Scalability requires rigorous science. Translating delicate kitchen formulations into consistent commercial batches is where art meets industry.</p>
          </div>
        </div>

        <!-- Milestone 6 -->
        <div class="timeline-node" onclick="toggleTimelineMilestone(this)">
          <div class="timeline-header">
            <span class="timeline-year">Present</span>
            <h4 class="timeline-title">🌱 Farm Consulting & Recipe Dev</h4>
            <span class="timeline-icon">▼</span>
          </div>
          <div class="timeline-body">
            <p><strong>The Context:</strong> Operating as a freelance consultant and recipes tester, visiting cacao plantations, auditing fermentation setups, and helping craft bean-to-bar operations succeed.</p>
            <p><strong>Cacao Lesson:</strong> Flavour starts in the orchard. The ultimate quality of chocolate is determined in the fermentation drawer and drying bed, not the laboratory.</p>
          </div>
        </div>
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
