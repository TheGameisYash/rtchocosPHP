<?php
  $pageTitle = "Work With Us — B2B Consulting, Recipe R&D & Executive Masterclasses | RT Chocos";
  $pageDescription = "Partner with Aarti Saluja Sahni and RT Chocos for bean-to-bar chocolate recipe formulation, corporate workshops, brand consulting, machine sourcing, and media collaborations.";
  $pathPrefix = "";
  $canonicalUrl = "https://www.rtchocos.com/work-with-us.php";
  $schemaType = "ContactPage";
  
  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'Work With Us', 'item' => $canonicalUrl]
  ];

  $submissionSuccess = false;
  $submissionError = "";

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      require_once 'includes/db.php';
      
      $name = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $phone = trim($_POST['phone'] ?? '');
      $company = trim($_POST['company'] ?? '');
      $service = trim($_POST['service'] ?? '');
      $budget = trim($_POST['budget'] ?? '');
      $message = trim($_POST['message'] ?? '');

      if (empty($name) || empty($email) || empty($message)) {
          $submissionError = "Please fill out all required fields (Name, Email, Message).";
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $submissionError = "Please enter a valid email address.";
      } else {
          try {
              $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, phone, subject, message, created_at) VALUES (:name, :email, :phone, :subject, :message, NOW())");
              $subjectLine = "B2B Consultation: " . ($service ?: "Work With Us") . ($company ? " ($company)" : "");
              $fullMsg = "Company/Brand: " . ($company ?: "N/A") . "\nService Interested: " . ($service ?: "General Inquiry") . "\nTarget Budget: " . ($budget ?: "Not Specified") . "\n\nProject Details:\n" . $message;
              
              $stmt->execute([
                  ':name' => $name,
                  ':email' => $email,
                  ':phone' => $phone,
                  ':subject' => $subjectLine,
                  ':message' => $fullMsg
              ]);
              $submissionSuccess = true;
          } catch (Exception $e) {
              $submissionError = "An unexpected error occurred. Please try again or email us directly at hello@rtchocos.com.";
          }
      }
  }

  include $pathPrefix . 'includes/header.php';
?>

<!-- --- UNCONDITIONAL LUXURY DARK FOREST THEME FOR B2B PAGE --- -->
<style>
  body #page-work-with-us,
  html body #page-work-with-us {
    background-color: #030a06 !important;
    color: #f3edd7 !important;
    font-family: 'Inter', sans-serif !important;
  }
  
  #page-work-with-us * {
    box-sizing: border-box !important;
  }

  /* Universal High-Contrast Dark Theme Overrides */
  #page-work-with-us h1,
  #page-work-with-us h2,
  #page-work-with-us h3,
  #page-work-with-us h4,
  #page-work-with-us .section-title,
  #page-work-with-us .section-subtitle {
    color: #ffffff !important;
    font-family: 'Playfair Display', serif !important;
    font-weight: 700 !important;
  }

  #page-work-with-us p,
  #page-work-with-us li,
  #page-work-with-us div,
  #page-work-with-us span {
    color: #f3edd7 !important;
    font-family: 'Inter', sans-serif !important;
  }

  #page-work-with-us strong,
  #page-work-with-us b {
    color: #ffffff !important;
    font-weight: 600 !important;
  }

  #page-work-with-us .b2b-tag {
    color: #d4af37 !important;
    font-family: 'Inter', sans-serif !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    letter-spacing: 2.5px !important;
    text-transform: uppercase !important;
  }

  /* 1. Hero Section */
  #page-work-with-us .b2b-hero-banner {
    background: radial-gradient(circle at 50% 35%, #0e2b1b 0%, #06160d 70%, #030a06 100%) !important;
    padding: 95px 24px 80px !important;
    text-align: center !important;
    position: relative !important;
    border-bottom: 1px solid rgba(212, 175, 55, 0.25) !important;
  }

  /* 2. Metrics Bar */
  #page-work-with-us .b2b-metrics-bar {
    background: #081a10 !important;
    border-bottom: 1px solid rgba(212, 175, 55, 0.2) !important;
    padding: 40px 24px !important;
  }

  #page-work-with-us .b2b-metrics-bar .b2b-metric-num {
    color: #d4af37 !important;
    font-family: 'Playfair Display', serif !important;
    font-size: 42px !important;
    font-weight: 700 !important;
    line-height: 1 !important;
  }

  #page-work-with-us .b2b-metrics-bar .b2b-metric-label {
    color: #f3edd7 !important;
    font-family: 'Inter', sans-serif !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
    margin-top: 8px !important;
  }

  /* 3. Founder Spotlight */
  #page-work-with-us .b2b-founder-sec {
    background-color: #030a06 !important;
    padding: 90px 24px !important;
    border-bottom: 1px solid rgba(212, 175, 55, 0.2) !important;
  }

  /* 4. Capabilities & Services Grid */
  #page-work-with-us .b2b-services-sec {
    background: #081a10 !important;
    padding: 90px 24px !important;
    border-bottom: 1px solid rgba(212, 175, 55, 0.2) !important;
  }

  #page-work-with-us .b2b-card {
    background: rgba(14, 34, 22, 0.85) !important;
    border: 1px solid rgba(212, 175, 55, 0.3) !important;
    border-radius: 20px !important;
    padding: 34px 28px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4) !important;
  }

  /* 5. Dark Science Section */
  #page-work-with-us .b2b-dark-sec {
    background-color: #030a06 !important;
    padding: 90px 24px !important;
    border-bottom: 1px solid rgba(212, 175, 55, 0.2) !important;
  }

  /* 6. Process Section */
  #page-work-with-us .b2b-process-sec {
    background: #081a10 !important;
    padding: 90px 24px !important;
    border-bottom: 1px solid rgba(212, 175, 55, 0.2) !important;
  }

  /* 7. Form Section */
  #page-work-with-us .b2b-form-sec {
    background: #030a06 !important;
    padding: 100px 24px !important;
    border-bottom: 1px solid rgba(212, 175, 55, 0.2) !important;
  }

  #page-work-with-us .b2b-form-card {
    background: rgba(10, 26, 17, 0.95) !important;
    border: 1px solid rgba(212, 175, 55, 0.35) !important;
    border-radius: 24px !important;
    padding: 50px 38px !important;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6) !important;
  }

  #page-work-with-us label {
    color: #d4af37 !important;
    font-family: 'Inter', sans-serif !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
    margin-bottom: 8px !important;
    display: block !important;
  }

  #page-work-with-us input,
  #page-work-with-us select,
  #page-work-with-us textarea {
    background: rgba(255, 255, 255, 0.06) !important;
    border: 1px solid rgba(212, 175, 55, 0.35) !important;
    color: #ffffff !important;
    font-family: 'Inter', sans-serif !important;
    font-size: 14.5px !important;
    border-radius: 10px !important;
    padding: 13px 16px !important;
    outline: none !important;
    width: 100% !important;
  }

  /* 8. FAQs Section */
  #page-work-with-us .b2b-faq-sec {
    background: #081a10 !important;
    padding: 90px 24px !important;
  }

  #page-work-with-us details {
    background: rgba(14, 34, 22, 0.8) !important;
    border: 1px solid rgba(212, 175, 55, 0.25) !important;
    border-radius: 16px !important;
    padding: 20px 24px !important;
  }

  #page-work-with-us summary,
  #page-work-with-us summary span {
    color: #ffffff !important;
    font-family: 'Playfair Display', serif !important;
    font-size: 18.5px !important;
    font-weight: 700 !important;
    outline: none !important;
  }

  #page-work-with-us details p {
    color: #f3edd7 !important;
    font-family: 'Inter', sans-serif !important;
    font-size: 14.5px !important;
    line-height: 1.7 !important;
    margin-top: 14px !important;
    padding-top: 12px !important;
    border-top: 1px solid rgba(212, 175, 55, 0.18) !important;
    font-weight: 300 !important;
  }
</style>

<!-- --- WORK WITH US PAGE CONTENT --- -->
<div id="page-work-with-us" class="page active" style="padding-top:76px;">
  
  <!-- 1. Hero Section -->
  <section class="b2b-hero-banner">
    <div style="max-width: 920px; margin: 0 auto; position: relative; z-index: 2;">
      
      <div style="background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.4); padding: 7px 22px; border-radius: 30px; display: inline-block; margin-bottom: 20px;">
        <span class="b2b-tag">✦ EXCLUSIVE B2B &amp; CONSULTING SERVICES ✦</span>
      </div>
      
      <h1 style="font-size: clamp(38px, 5.5vw, 60px); line-height: 1.15; margin-bottom: 20px; text-shadow: 0 4px 20px rgba(0,0,0,0.5);">
        Work With <span style="color: #d4af37 !important;">RT Chocos</span>
      </h1>
      
      <p style="font-size: clamp(15.5px, 1.8vw, 19px); line-height: 1.75; max-width: 780px; margin: 0 auto 36px; font-weight: 300;">
        From bean-to-bar recipe formulation and factory setup consulting to executive corporate masterclasses and brand partnerships—work directly with certified chocolate educator <strong style="color: #ffffff !important;">Aarti Saluja Sahni</strong>.
      </p>
      
      <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
        <a href="#inquiry-form" class="btn-primary" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px; padding: 15px 36px; font-size: 14px; background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%) !important; color: #06160d !important; font-weight: 700; border-radius: 30px; box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);">
          <span style="color: #06160d !important;">Request B2B Consultation</span>
          <span style="color: #06160d !important;">&rarr;</span>
        </a>
        <a href="#services-grid" class="btn-outline-dark" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px; padding: 15px 36px; font-size: 14px; border: 1px solid rgba(212, 175, 55, 0.4) !important; color: #ffffff !important; border-radius: 30px; background: rgba(255,255,255,0.05);">
          <span>Explore Capabilities</span>
        </a>
      </div>

    </div>
  </section>

  <!-- 2. Credibility & Proof Metrics Bar -->
  <section class="b2b-metrics-bar">
    <div style="max-width: 1140px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 28px; text-align: center;">
      <div style="padding: 12px;">
        <div class="b2b-metric-num">10+</div>
        <div class="b2b-metric-label">Years of Recipe R&amp;D</div>
      </div>
      <div style="padding: 12px; border-left: 1px solid rgba(212, 175, 55, 0.2);">
        <div class="b2b-metric-num">50+</div>
        <div class="b2b-metric-label">Brands &amp; Artisans Consulted</div>
      </div>
      <div style="padding: 12px; border-left: 1px solid rgba(212, 175, 55, 0.2);">
        <div class="b2b-metric-num">2,000+</div>
        <div class="b2b-metric-label">Masterclass Alumni</div>
      </div>
      <div style="padding: 12px; border-left: 1px solid rgba(212, 175, 55, 0.2);">
        <div class="b2b-metric-num">100%</div>
        <div class="b2b-metric-label">Science-First &amp; Custom</div>
      </div>
    </div>
  </section>

  <!-- 3. Founder Spotlight Section -->
  <section class="b2b-founder-sec">
    <div style="max-width: 1140px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 52px; align-items: center;">
      
      <!-- Founder Photo Card -->
      <div style="position: relative;">
        <div style="border-radius: 20px; overflow: hidden; box-shadow: 0 15px 45px rgba(0, 0, 0, 0.5); border: 1px solid rgba(212, 175, 55, 0.3);">
          <img src="assets/myphoto.jpg" alt="Aarti Saluja Sahni - Founder &amp; Lead Consultant" style="width: 100%; height: auto; display: block;" />
          <div style="background: rgba(14, 34, 22, 0.95) !important; padding: 22px 24px; border-top: 1px solid rgba(212, 175, 55, 0.3);">
            <h3 style="margin-bottom: 4px; color: #ffffff !important;">Aarti Saluja Sahni</h3>
            <div style="font-size: 12px !important; color: #d4af37 !important; font-weight: 700 !important; letter-spacing: 1px !important; text-transform: uppercase !important;">Founder &amp; Lead Chocolate Educator</div>
          </div>
        </div>
      </div>

      <!-- Founder Content -->
      <div>
        <div class="b2b-tag" style="margin-bottom: 10px;">LEAD CONSULTANT &amp; EDUCATOR</div>
        <h2 style="font-size: clamp(28px, 3.5vw, 42px); line-height: 1.2; margin-bottom: 18px; color: #ffffff !important;">
          Pioneering Indian Cacao Science &amp; Formulation
        </h2>
        <div style="width: 60px; height: 3px; background: #d4af37; margin-bottom: 22px;"></div>
        
        <p style="margin-bottom: 16px; font-weight: 300;">
          With over a decade of dedicated bean-to-bar formulation experience, Aarti Saluja Sahni has trained over 2,000 professional chocolatiers and consulted for leading Indian and international craft chocolate brands.
        </p>
        <p style="margin-bottom: 28px; font-weight: 300;">
          Specializing in Indian single-origin cacao (Kerala, Tamil Nadu, and Karnataka), Aarti combines deep chemical understanding of cacao fermentation with modern stone-grinding thermodynamics to craft recipes that win awards and captivate palates.
        </p>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.3); display: flex; align-items: center; justify-content: center; color: #d4af37 !important; font-size: 14px; font-weight: 700;">✓</div>
            <span style="font-size: 14px !important; color: #ffffff !important; font-weight: 600;">Certified Educator</span>
          </div>
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.3); display: flex; align-items: center; justify-content: center; color: #d4af37 !important; font-size: 14px; font-weight: 700;">✓</div>
            <span style="font-size: 14px !important; color: #ffffff !important; font-weight: 600;">Direct-Trade Sourcing</span>
          </div>
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.3); display: flex; align-items: center; justify-content: center; color: #d4af37 !important; font-size: 14px; font-weight: 700;">✓</div>
            <span style="font-size: 14px !important; color: #ffffff !important; font-weight: 600;">HACCP &amp; R&amp;D SOPs</span>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- 4. Core B2B Services Grid -->
  <section id="services-grid" class="b2b-services-sec">
    <div style="max-width: 1200px; margin: 0 auto;">
      
      <div style="text-align: center; margin-bottom: 56px;">
        <div class="b2b-tag" style="margin-bottom: 8px;">CAPABILITIES &amp; SOLUTIONS</div>
        <h2 style="font-size: clamp(30px, 3.8vw, 44px); margin-bottom: 12px; color: #ffffff !important;">Bespoke Services for Brands &amp; Corporates</h2>
        <div style="width: 60px; height: 3px; background: #d4af37; margin: 0 auto;"></div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(270px, 1fr)); gap: 28px;">
        
        <!-- Pillar 1: Recipe R&D -->
        <div class="b2b-card">
          <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.3); display: flex; align-items: center; justify-content: center; margin-bottom: 22px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/>
              <line x1="8.5" y1="2" x2="15.5" y2="2"/>
            </svg>
          </div>
          <h3 style="font-size: 22px; margin-bottom: 12px; color: #ffffff !important;">Recipe Formulation &amp; R&amp;D</h3>
          <p style="font-size: 14.5px; line-height: 1.65; margin-bottom: 20px; font-weight: 300;">
            Scientific development of commercial and luxury bean-to-bar recipes engineered for palate perfection and production stability.
          </p>
          <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> Tempering curves &amp; rheology optimization</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> Monk fruit, Erythritol &amp; Alt-sugar formulas</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> Oat &amp; Coconut vegan milk chocolate ratios</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> Fat bloom &amp; sugar bloom prevention</li>
          </ul>
        </div>

        <!-- Pillar 2: Corporate Masterclasses -->
        <div class="b2b-card">
          <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.3); display: flex; align-items: center; justify-content: center; margin-bottom: 22px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
              <path d="M6 12v5c0 2 6 2 6 2s6 0 6-2v-5"/>
            </svg>
          </div>
          <h3 style="font-size: 22px; margin-bottom: 12px; color: #ffffff !important;">Corporate Masterclasses</h3>
          <p style="font-size: 14.5px; line-height: 1.65; margin-bottom: 20px; font-weight: 300;">
            Unforgettable tasting flights and hands-on chocolate making workshops designed for executive teams, hotels, and luxury brands.
          </p>
          <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> Single-origin Cacao Tasting Flights</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> Executive Team Building &amp; Gifting</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> In-person in Mumbai or Live Virtual</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> Custom Branded DIY Chocolate Kits</li>
          </ul>
        </div>

        <!-- Pillar 3: Bean-to-Bar Brand Consulting -->
        <div class="b2b-card">
          <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.3); display: flex; align-items: center; justify-content: center; margin-bottom: 22px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
            </svg>
          </div>
          <h3 style="font-size: 22px; margin-bottom: 12px; color: #ffffff !important;">Bean-to-Bar Brand Consulting</h3>
          <p style="font-size: 14.5px; line-height: 1.65; margin-bottom: 20px; font-weight: 300;">
            End-to-end technical guidance for bean-to-bar startups, artisanal chocolatiers, and commercial manufacturing setups.
          </p>
          <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> Machine Selection (Melangers, Roasters)</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> Indian Cacao Estate Bean Sourcing</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> Factory Floor Layout &amp; Production Hygiene</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> Batch Scaling &amp; Cost Optimization</li>
          </ul>
        </div>

        <!-- Pillar 4: Media & Press Partnerships -->
        <div class="b2b-card">
          <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.3); display: flex; align-items: center; justify-content: center; margin-bottom: 22px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/>
              <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
              <line x1="12" y1="19" x2="12" y2="23"/>
            </svg>
          </div>
          <h3 style="font-size: 22px; margin-bottom: 12px; color: #ffffff !important;">Media &amp; Press Partnerships</h3>
          <p style="font-size: 14.5px; line-height: 1.65; margin-bottom: 20px; font-weight: 300;">
            Collaborate with India's premier chocolate learning platform to showcase your craft chocolate brand, machines, or cocoa ingredients.
          </p>
          <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> Brand Features in The Cacao Journal</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> YouTube Channel Sponsorships &amp; Reviews</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> Event Keynotes &amp; Industry Panels</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37 !important; font-weight: 700;">✓</span> Product Testing &amp; Ingredient Endorsements</li>
          </ul>
        </div>

      </div>

    </div>
  </section>

  <!-- 5. Technical Cocoa Science Pillars -->
  <section class="b2b-dark-sec">
    <div style="max-width: 1180px; margin: 0 auto;">
      
      <div style="text-align: center; margin-bottom: 56px;">
        <div style="color: #d4af37 !important; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px;">TECHNICAL SCIENCE</div>
        <h2 style="font-size: clamp(28px, 3.5vw, 42px); color: #ffffff !important;">The 6 Pillars of Cocoa Science We Master</h2>
        <div style="width: 60px; height: 3px; background: #d4af37; margin: 0 auto;"></div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
        
        <div class="b2b-card">
          <h4 style="color: #d4af37 !important; font-size: 20px; margin-bottom: 10px;">1. Fermentation Chemistry</h4>
          <p style="font-size: 14px; line-height: 1.65; margin: 0; font-weight: 300;">Analyzing lactic vs. acetic acid development during sweatbox fermentation to control floral and fruit notes in Indian cacao beans.</p>
        </div>

        <div class="b2b-card">
          <h4 style="color: #d4af37 !important; font-size: 20px; margin-bottom: 10px;">2. Roasting Thermal Curves</h4>
          <p style="font-size: 14px; line-height: 1.65; margin: 0; font-weight: 300;">Developing precise temperature vs. time roasting profiles to preserve delicate terpenes or enhance rich malt and nutty notes.</p>
        </div>

        <div class="b2b-card">
          <h4 style="color: #d4af37 !important; font-size: 20px; margin-bottom: 10px;">3. Particle Size &amp; Rheology</h4>
          <p style="font-size: 14px; line-height: 1.65; margin: 0; font-weight: 300;">Micro-grinding cacao nibs in granite stone melangers down to &lt; 18 microns for silky mouthfeel without gritty residue.</p>
        </div>

        <div class="b2b-card">
          <h4 style="color: #d4af37 !important; font-size: 20px; margin-bottom: 10px;">4. Conching &amp; Acid Evacuation</h4>
          <p style="font-size: 14px; line-height: 1.65; margin: 0; font-weight: 300;">Controlling airflow, friction heat, and moisture removal to eliminate harsh astringency while sealing in cocoa butter fats.</p>
        </div>

        <div class="b2b-card">
          <h4 style="color: #d4af37 !important; font-size: 20px; margin-bottom: 10px;">5. Beta V Crystal Seeding</h4>
          <p style="font-size: 14px; line-height: 1.65; margin: 0; font-weight: 300;">Precision tempering thermodynamics to achieve glossy sheen, crisp snap sound, high heat stability, and zero fat-bloom.</p>
        </div>

        <div class="b2b-card">
          <h4 style="color: #d4af37 !important; font-size: 20px; margin-bottom: 10px;">6. Shelf Stability &amp; Packaging</h4>
          <p style="font-size: 14px; line-height: 1.65; margin: 0; font-weight: 300;">Water activity testing, moisture barrier foil selection, and shelf-life diagnostics for Indian climate distribution.</p>
        </div>

      </div>

    </div>
  </section>

  <!-- 6. 4-Step Engagement Process -->
  <section class="b2b-process-sec">
    <div style="max-width: 1100px; margin: 0 auto;">
      
      <div style="text-align: center; margin-bottom: 56px;">
        <div class="b2b-tag" style="margin-bottom: 8px;">HOW IT WORKS</div>
        <h2 style="font-size: clamp(28px, 3.5vw, 42px); margin-bottom: 12px; color: #ffffff !important;">Our Engagement Process</h2>
        <div style="width: 60px; height: 3px; background: #d4af37; margin: 0 auto;"></div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px;">
        
        <div class="b2b-card">
          <div style="font-family: 'Playfair Display', serif; font-size: 38px; font-weight: 700; color: #d4af37 !important; margin-bottom: 10px;">01</div>
          <h4 style="font-size: 20px; margin-bottom: 8px; color: #ffffff !important;">Discovery Call</h4>
          <p style="font-size: 14px; line-height: 1.65; margin: 0; font-weight: 300;">Initial strategy consultation to map your formulation goals, brand vision, or event scope.</p>
        </div>

        <div class="b2b-card">
          <div style="font-family: 'Playfair Display', serif; font-size: 38px; font-weight: 700; color: #d4af37 !important; margin-bottom: 10px;">02</div>
          <h4 style="font-size: 20px; margin-bottom: 8px; color: #ffffff !important;">Proposal &amp; Scope</h4>
          <p style="font-size: 14px; line-height: 1.65; margin: 0; font-weight: 300;">Custom proposal detailing recipe metrics, milestone timelines, equipment requirements, and costs.</p>
        </div>

        <div class="b2b-card">
          <div style="font-family: 'Playfair Display', serif; font-size: 38px; font-weight: 700; color: #d4af37 !important; margin-bottom: 10px;">03</div>
          <h4 style="font-size: 20px; margin-bottom: 8px; color: #ffffff !important;">R&amp;D &amp; Testing</h4>
          <p style="font-size: 14px; line-height: 1.65; margin: 0; font-weight: 300;">Hands-on lab batches, tempering trials, sensory profiling, and client tasting feedback loops.</p>
        </div>

        <div class="b2b-card">
          <div style="font-family: 'Playfair Display', serif; font-size: 38px; font-weight: 700; color: #d4af37 !important; margin-bottom: 10px;">04</div>
          <h4 style="font-size: 20px; margin-bottom: 8px; color: #ffffff !important;">Launch &amp; Scale</h4>
          <p style="font-size: 14px; line-height: 1.65; margin: 0; font-weight: 300;">Final formulation blueprints, SOP documentation, staff training, and post-launch support.</p>
        </div>

      </div>

    </div>
  </section>

  <!-- 7. Consultation Request Form Card -->
  <section id="inquiry-form" class="b2b-form-sec">
    <div class="b2b-form-card" style="max-width: 820px; margin: 0 auto;">
      
      <div style="text-align: center; margin-bottom: 40px;">
        <div class="b2b-tag" style="margin-bottom: 8px;">GET STARTED TODAY</div>
        <h2 style="font-size: clamp(30px, 4vw, 40px); margin-bottom: 12px; color: #ffffff !important;">Request a B2B Consultation</h2>
        <p style="font-size: 15px; color: #f3edd7 !important; font-weight: 300; max-width: 580px; margin: 0 auto;">
          Fill out your requirements below and certified chocolate educator <strong style="color: #ffffff !important;">Aarti Saluja Sahni</strong> will respond personally within 24 hours.
        </p>
      </div>

      <?php if ($submissionSuccess): ?>
        <div style="background: rgba(212, 175, 55, 0.15); border: 1px solid #d4af37; color: #ffffff; padding: 20px 24px; border-radius: 14px; text-align: center; font-family: 'Inter', sans-serif; font-size: 15px; line-height: 1.6; margin-bottom: 28px; font-weight: 600;">
          ✓ Thank you! Your B2B consultation request has been received. We will get in touch with you within 24 hours.
        </div>
      <?php elseif (!empty($submissionError)): ?>
        <div style="background: rgba(255, 107, 107, 0.15); border: 1px solid #ff6b6b; color: #ff6b6b; padding: 16px 20px; border-radius: 14px; text-align: center; font-family: 'Inter', sans-serif; font-size: 14px; margin-bottom: 28px; font-weight: 600;">
          <?php echo htmlspecialchars($submissionError); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="work-with-us.php#inquiry-form" style="display: grid; gap: 22px;">
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
          <div>
            <label>Your Full Name *</label>
            <input type="text" name="name" required placeholder="e.g. Aarti Sahni" />
          </div>
          <div>
            <label>Work Email Address *</label>
            <input type="email" name="email" required placeholder="name@company.com" />
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
          <div>
            <label>Phone / WhatsApp</label>
            <input type="text" name="phone" placeholder="+91 98765 43210" />
          </div>
          <div>
            <label>Company / Brand Name</label>
            <input type="text" name="company" placeholder="e.g. Artisan Cacao Ltd." />
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
          <div>
            <label>Primary Service Required</label>
            <select name="service">
              <option value="Recipe Formulation & R&D">🧪 Recipe Formulation &amp; R&amp;D (Custom Dark, Alt-Milk, Sugar-Free)</option>
              <option value="Corporate Masterclass / Workshop">🏢 Corporate Masterclasses &amp; Executive Workshops</option>
              <option value="Bean-to-Bar Brand Consulting">🏭 Bean-to-Bar Brand Consulting &amp; Factory Setup</option>
              <option value="Media & Press Partnership">🎙️ Media, Press &amp; Brand Sponsorship</option>
              <option value="General B2B Inquiry">✨ General B2B &amp; Custom Collaboration</option>
            </select>
          </div>
          <div>
            <label>Target Budget Range</label>
            <select name="budget">
              <option value="₹50,000 - ₹1,50,000">₹50,000 – ₹1,50,000</option>
              <option value="₹1,50,000 - ₹3,50,000">₹1,50,000 – ₹3,50,000</option>
              <option value="₹3,50,000+">₹3,50,000+ (Enterprise / Full Setup)</option>
              <option value="Flexible / Undecided">Flexible / Undecided</option>
            </select>
          </div>
        </div>

        <div>
          <label>Project Overview / Inquiry Details *</label>
          <textarea name="message" rows="5" required placeholder="Tell us about your project vision, target timeline, or specific formulation requirements..."></textarea>
        </div>

        <div style="text-align: center; margin-top: 10px;">
          <button type="submit" class="btn-primary" style="padding: 16px 44px; border: none; cursor: pointer; width: 100%; max-width: 360px; font-size: 15px; font-weight: 700; letter-spacing: 0.5px; border-radius: 30px; background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%) !important; color: #06160d !important;">
            <span style="color: #06160d !important;">Submit B2B Inquiry</span>
            <span style="color: #06160d !important; font-size: 16px;">&rarr;</span>
          </button>
        </div>

      </form>

    </div>
  </section>

  <!-- 8. B2B Frequently Asked Questions Accordion -->
  <section class="b2b-faq-sec">
    <div style="max-width: 860px; margin: 0 auto;">
      
      <div style="text-align: center; margin-bottom: 50px;">
        <div class="b2b-tag" style="margin-bottom: 8px;">FREQUENTLY ASKED QUESTIONS</div>
        <h2 style="font-size: 32px; margin-bottom: 10px; color: #ffffff !important;">B2B &amp; Consulting FAQs</h2>
        <div style="width: 60px; height: 3px; background: #d4af37; margin: 0 auto;"></div>
      </div>

      <div style="display: flex; flex-direction: column; gap: 16px;">
        
        <details>
          <summary>
            <span>How does bean-to-bar recipe formulation consulting work?</span>
            <span style="color: #d4af37 !important; font-size: 20px; font-weight: 700;">+</span>
          </summary>
          <p>
            We work with you to understand your target flavor profile, dietary requirements (e.g. vegan, sugar-free, single-origin), and production equipment. We provide exact percentage formulations, roasting thermal curves, conching durations, and tempering SOPs.
          </p>
        </details>

        <details>
          <summary>
            <span>Can corporate masterclasses be conducted outside Mumbai or virtually?</span>
            <span style="color: #d4af37 !important; font-size: 20px; font-weight: 700;">+</span>
          </summary>
          <p>
            Yes! We conduct live on-site corporate workshops and tasting retreats across major cities in India, as well as live interactive virtual masterclasses worldwide with custom DIY chocolate kits shipped to participants.
          </p>
        </details>

        <details>
          <summary>
            <span>Do you assist with commercial equipment selection &amp; cocoa bean sourcing?</span>
            <span style="color: #d4af37 !important; font-size: 20px; font-weight: 700;">+</span>
          </summary>
          <p>
            Absolutely. We guide startups on selecting the right roasters, winnowers, stone grinders/melangers, and continuous tempering machines based on target monthly volume, and connect you with trusted direct-trade organic cacao farmers in South India.
          </p>
        </details>

      </div>

    </div>
  </section>

</div>

<?php
  include $pathPrefix . 'includes/footer.php';
?>
