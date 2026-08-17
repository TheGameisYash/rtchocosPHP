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
      $message = trim($_POST['message'] ?? '');

      if (empty($name) || empty($email) || empty($message)) {
          $submissionError = "Please fill out all required fields (Name, Email, Message).";
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $submissionError = "Please enter a valid email address.";
      } else {
          try {
              $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, phone, subject, message, created_at) VALUES (:name, :email, :phone, :subject, :message, NOW())");
              $subjectLine = "B2B Inquiry: " . ($service ?: "Work With Us") . ($company ? " ($company)" : "");
              $fullMsg = "Company/Brand: " . ($company ?: "N/A") . "\nService Interested: " . ($service ?: "General Inquiry") . "\n\nMessage:\n" . $message;
              
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

<!-- --- WORK WITH US LUXURY PAGE --- -->
<div id="page-work-with-us" class="page active" style="padding-top:80px; background-color: #030a06; color: #f5ede6;">
  
  <!-- Hero Section with Luxury Gold Glow & Radial Particles -->
  <section class="b2b-hero" style="background: radial-gradient(circle at 50% 35%, #0e2b1b 0%, #06160d 60%, #030a06 100%); padding: 100px 24px 80px; text-align: center; position: relative; overflow: hidden; border-bottom: 1px solid rgba(212, 175, 55, 0.2);">
    <!-- Decorative background aura -->
    <div style="position: absolute; top: -20%; left: 50%; transform: translateX(-50%); width: 600px; height: 600px; background: radial-gradient(circle, rgba(212, 175, 55, 0.08) 0%, transparent 70%); pointer-events: none; z-index: 1;"></div>
    
    <div style="max-width: 960px; margin: 0 auto; relative; z-index: 2;">
      <div style="background: rgba(212, 175, 55, 0.12); border: 1px solid rgba(212, 175, 55, 0.4); color: #d4af37; font-family: 'Inter', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 2.5px; padding: 7px 22px; border-radius: 30px; display: inline-block; margin-bottom: 22px; text-transform: uppercase; box-shadow: 0 4px 20px rgba(212, 175, 55, 0.15);">
        ✦ EXCLUSIVE B2B &amp; CONSULTING SERVICES ✦
      </div>
      
      <h1 style="font-family: 'Playfair Display', serif; font-size: clamp(38px, 5.5vw, 62px); font-weight: 700; color: #ffffff; line-height: 1.12; margin-bottom: 22px; text-shadow: 0 4px 20px rgba(0,0,0,0.6);">
        Crafting Excellence in <span style="background: linear-gradient(135deg, #fceabb 0%, #d4af37 50%, #e5c453 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Bean-to-Bar Chocolate</span>
      </h1>
      
      <p style="font-family: 'Inter', sans-serif; font-size: clamp(16px, 1.8vw, 19px); color: rgba(245, 237, 230, 0.88); line-height: 1.75; max-width: 780px; margin: 0 auto 36px; font-weight: 300;">
        Partner with certified chocolate educator and recipe developer <strong>Aarti Saluja Sahni</strong> for bespoke chocolate formulation, corporate masterclasses, factory layout consulting, and high-impact brand collaborations.
      </p>
      
      <div style="display: flex; gap: 18px; justify-content: center; flex-wrap: wrap;">
        <a href="#inquiry-form" class="btn-hero-primary" style="text-decoration:none; display:inline-flex; align-items:center; gap:10px; padding: 16px 36px; font-size: 14px; letter-spacing: 0.5px;">
          <span>Request B2B Consultation</span>
          <span style="font-size: 16px;">&rarr;</span>
        </a>
        <a href="#services-grid" class="btn-hero-outline" style="text-decoration:none; display:inline-flex; align-items:center; gap:10px; padding: 16px 36px; font-size: 14px; border-color: rgba(212, 175, 55, 0.4); color: #f3edd7;">
          <span>Explore Service Pillars</span>
        </a>
      </div>
    </div>
  </section>

  <!-- Credibility & Proof Bar -->
  <section style="background: rgba(10, 26, 17, 0.9); border-bottom: 1px solid rgba(212, 175, 55, 0.15); padding: 36px 24px;">
    <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; text-align: center;">
      <div style="padding: 12px;">
        <div style="font-family: 'Playfair Display', serif; font-size: 38px; font-weight: 700; color: #d4af37; line-height: 1;">10+</div>
        <div style="font-family: 'Inter', sans-serif; font-size: 12.5px; color: rgba(245, 237, 230, 0.75); margin-top: 6px; letter-spacing: 0.5px; text-transform: uppercase;">Years of Recipe R&amp;D</div>
      </div>
      <div style="padding: 12px; border-left: 1px solid rgba(212, 175, 55, 0.15);">
        <div style="font-family: 'Playfair Display', serif; font-size: 38px; font-weight: 700; color: #d4af37; line-height: 1;">50+</div>
        <div style="font-family: 'Inter', sans-serif; font-size: 12.5px; color: rgba(245, 237, 230, 0.75); margin-top: 6px; letter-spacing: 0.5px; text-transform: uppercase;">Brands &amp; Artisans Consulted</div>
      </div>
      <div style="padding: 12px; border-left: 1px solid rgba(212, 175, 55, 0.15);">
        <div style="font-family: 'Playfair Display', serif; font-size: 38px; font-weight: 700; color: #d4af37; line-height: 1;">2,000+</div>
        <div style="font-family: 'Inter', sans-serif; font-size: 12.5px; color: rgba(245, 237, 230, 0.75); margin-top: 6px; letter-spacing: 0.5px; text-transform: uppercase;">Students &amp; Alumni Trained</div>
      </div>
      <div style="padding: 12px; border-left: 1px solid rgba(212, 175, 55, 0.15);">
        <div style="font-family: 'Playfair Display', serif; font-size: 38px; font-weight: 700; color: #d4af37; line-height: 1;">100%</div>
        <div style="font-family: 'Inter', sans-serif; font-size: 12.5px; color: rgba(245, 237, 230, 0.75); margin-top: 6px; letter-spacing: 0.5px; text-transform: uppercase;">Science-First &amp; Custom</div>
      </div>
    </div>
  </section>

  <!-- Core B2B Services Grid (4 Luxury Pillars) -->
  <section id="services-grid" class="section" style="background-color: #06140c; padding: 100px 24px;">
    <div style="max-width: 1240px; margin: 0 auto;">
      
      <div style="text-align: center; margin-bottom: 64px;">
        <div style="color: #d4af37; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; margin-bottom: 10px;">CAPABILITIES &amp; SOLUTIONS</div>
        <h2 style="font-family: 'Playfair Display', serif; font-size: clamp(32px, 4vw, 46px); font-weight: 700; color: #ffffff; margin-bottom: 14px;">Bespoke Services for Brands &amp; Corporates</h2>
        <div style="width: 70px; height: 3px; background: linear-gradient(90deg, #d4af37 0%, rgba(212, 175, 55, 0.2) 100%); margin: 0 auto; border-radius: 2px;"></div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px;">
        
        <!-- Pillar 1: Recipe R&D -->
        <div style="background: rgba(14, 34, 22, 0.75); border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 24px; padding: 38px 30px; backdrop-filter: blur(16px); box-shadow: 0 14px 40px rgba(0, 0, 0, 0.35); transition: transform 0.35s ease, border-color 0.35s ease;">
          <div style="width: 56px; height: 56px; border-radius: 16px; background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.35); display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/>
              <line x1="8.5" y1="2" x2="15.5" y2="2"/>
            </svg>
          </div>
          <h3 style="font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; color: #ffffff; margin-bottom: 14px;">Recipe Formulation &amp; R&amp;D</h3>
          <p style="font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.7; color: rgba(245, 237, 230, 0.85); font-weight: 300; margin-bottom: 20px;">
            Scientific development of commercial and luxury bean-to-bar recipes engineered for palate perfection and production stability.
          </p>
          <ul style="list-style: none; padding: 0; margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: rgba(245, 237, 230, 0.9); line-height: 1.9;">
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> Tempering curves &amp; rheology optimization</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> Monk fruit, Erythritol &amp; Alt-sugar formulations</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> Oat &amp; Coconut vegan milk chocolate ratios</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> Fat bloom &amp; sugar bloom prevention protocols</li>
          </ul>
        </div>

        <!-- Pillar 2: Corporate Masterclasses -->
        <div style="background: rgba(14, 34, 22, 0.75); border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 24px; padding: 38px 30px; backdrop-filter: blur(16px); box-shadow: 0 14px 40px rgba(0, 0, 0, 0.35); transition: transform 0.35s ease, border-color 0.35s ease;">
          <div style="width: 56px; height: 56px; border-radius: 16px; background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.35); display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
              <path d="M6 12v5c0 2 6 2 6 2s6 0 6-2v-5"/>
            </svg>
          </div>
          <h3 style="font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; color: #ffffff; margin-bottom: 14px;">Corporate Masterclasses</h3>
          <p style="font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.7; color: rgba(245, 237, 230, 0.85); font-weight: 300; margin-bottom: 20px;">
            Unforgettable tasting flights and hands-on chocolate making workshops designed for executive teams, hotels, and luxury brands.
          </p>
          <ul style="list-style: none; padding: 0; margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: rgba(245, 237, 230, 0.9); line-height: 1.9;">
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> Single-origin Cacao Tasting &amp; Pairing Flights</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> Executive Team Building &amp; Gifting Experiences</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> In-person in Mumbai or Live Virtual Worldwide</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> Custom Branded DIY Chocolate Kits</li>
          </ul>
        </div>

        <!-- Pillar 3: Bean-to-Bar Brand Consulting -->
        <div style="background: rgba(14, 34, 22, 0.75); border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 24px; padding: 38px 30px; backdrop-filter: blur(16px); box-shadow: 0 14px 40px rgba(0, 0, 0, 0.35); transition: transform 0.35s ease, border-color 0.35s ease;">
          <div style="width: 56px; height: 56px; border-radius: 16px; background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.35); display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
            </svg>
          </div>
          <h3 style="font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; color: #ffffff; margin-bottom: 14px;">Bean-to-Bar Brand Consulting</h3>
          <p style="font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.7; color: rgba(245, 237, 230, 0.85); font-weight: 300; margin-bottom: 20px;">
            End-to-end technical guidance for bean-to-bar startups, artisanal chocolatiers, and commercial manufacturing setups.
          </p>
          <ul style="list-style: none; padding: 0; margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: rgba(245, 237, 230, 0.9); line-height: 1.9;">
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> Machine Selection (Melangers, Roasters, Temperers)</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> Indian Cacao Estate Sourcing (Kerala, TN, KA)</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> Factory Floor Layout &amp; Production Hygiene</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> Batch Scaling &amp; Cost-per-bar Optimization</li>
          </ul>
        </div>

        <!-- Pillar 4: Media & Press Partnerships -->
        <div style="background: rgba(14, 34, 22, 0.75); border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 24px; padding: 38px 30px; backdrop-filter: blur(16px); box-shadow: 0 14px 40px rgba(0, 0, 0, 0.35); transition: transform 0.35s ease, border-color 0.35s ease;">
          <div style="width: 56px; height: 56px; border-radius: 16px; background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.35); display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/>
              <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
              <line x1="12" y1="19" x2="12" y2="23"/>
            </svg>
          </div>
          <h3 style="font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; color: #ffffff; margin-bottom: 14px;">Media &amp; Press Partnerships</h3>
          <p style="font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.7; color: rgba(245, 237, 230, 0.85); font-weight: 300; margin-bottom: 20px;">
            Collaborate with India's premier chocolate learning platform to showcase your craft chocolate brand, machines, or cocoa ingredients.
          </p>
          <ul style="list-style: none; padding: 0; margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: rgba(245, 237, 230, 0.9); line-height: 1.9;">
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> Brand Features in The Cacao Journal</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> YouTube Channel Sponsorships &amp; Reviews</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> Event Keynotes &amp; Industry Panels</li>
            <li style="display: flex; align-items: center; gap: 8px;"><span style="color: #d4af37;">✓</span> Product Testing &amp; Ingredient Endorsements</li>
          </ul>
        </div>

      </div>

    </div>
  </section>

  <!-- 4-Step Engagement Process -->
  <section style="background: radial-gradient(circle at 50% 50%, #0c2417 0%, #05120a 100%); padding: 90px 24px; border-top: 1px solid rgba(212, 175, 55, 0.15); border-bottom: 1px solid rgba(212, 175, 55, 0.15);">
    <div style="max-width: 1100px; margin: 0 auto;">
      
      <div style="text-align: center; margin-bottom: 60px;">
        <div style="color: #d4af37; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px;">HOW IT WORKS</div>
        <h2 style="font-family: 'Playfair Display', serif; font-size: clamp(28px, 3.5vw, 42px); font-weight: 700; color: #ffffff; margin-bottom: 12px;">Our Engagement Process</h2>
        <div style="width: 60px; height: 3px; background: linear-gradient(90deg, #d4af37, transparent); margin: 0 auto;"></div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; position: relative;">
        
        <div style="background: rgba(14, 34, 22, 0.6); border: 1px solid rgba(212, 175, 55, 0.2); border-radius: 18px; padding: 28px 24px; position: relative;">
          <div style="font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: #d4af37; opacity: 0.8; margin-bottom: 10px;">01</div>
          <h4 style="font-family: 'Playfair Display', serif; font-size: 19px; color: #ffffff; margin-bottom: 8px;">Discovery Call</h4>
          <p style="font-family: 'Inter', sans-serif; font-size: 13.5px; color: rgba(245, 237, 230, 0.8); line-height: 1.6; font-weight: 300;">Initial consultation to map your formulation goals, brand vision, or event scope.</p>
        </div>

        <div style="background: rgba(14, 34, 22, 0.6); border: 1px solid rgba(212, 175, 55, 0.2); border-radius: 18px; padding: 28px 24px; position: relative;">
          <div style="font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: #d4af37; opacity: 0.8; margin-bottom: 10px;">02</div>
          <h4 style="font-family: 'Playfair Display', serif; font-size: 19px; color: #ffffff; margin-bottom: 8px;">Proposal &amp; Scope</h4>
          <p style="font-family: 'Inter', sans-serif; font-size: 13.5px; color: rgba(245, 237, 230, 0.8); line-height: 1.6; font-weight: 300;">Custom proposal detailing recipe metrics, milestone timelines, equipment lists, and costs.</p>
        </div>

        <div style="background: rgba(14, 34, 22, 0.6); border: 1px solid rgba(212, 175, 55, 0.2); border-radius: 18px; padding: 28px 24px; position: relative;">
          <div style="font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: #d4af37; opacity: 0.8; margin-bottom: 10px;">03</div>
          <h4 style="font-family: 'Playfair Display', serif; font-size: 19px; color: #ffffff; margin-bottom: 8px;">R&amp;D &amp; Testing</h4>
          <p style="font-family: 'Inter', sans-serif; font-size: 13.5px; color: rgba(245, 237, 230, 0.8); line-height: 1.6; font-weight: 300;">Hands-on lab batches, tempering trials, sensory profiling, and feedback loops.</p>
        </div>

        <div style="background: rgba(14, 34, 22, 0.6); border: 1px solid rgba(212, 175, 55, 0.2); border-radius: 18px; padding: 28px 24px; position: relative;">
          <div style="font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: #d4af37; opacity: 0.8; margin-bottom: 10px;">04</div>
          <h4 style="font-family: 'Playfair Display', serif; font-size: 19px; color: #ffffff; margin-bottom: 8px;">Launch &amp; Handover</h4>
          <p style="font-family: 'Inter', sans-serif; font-size: 13.5px; color: rgba(245, 237, 230, 0.8); line-height: 1.6; font-weight: 300;">Final formulation blueprints, SOP documentation, staff training, and post-launch support.</p>
        </div>

      </div>

    </div>
  </section>

  <!-- Consultation Request Form (Luxury Dark Glass) -->
  <section id="inquiry-form" class="section" style="background: #030a06; padding: 100px 24px;">
    <div style="max-width: 820px; margin: 0 auto; background: rgba(12, 28, 19, 0.9); border: 1px solid rgba(212, 175, 55, 0.35); border-radius: 28px; padding: 54px 42px; backdrop-filter: blur(24px); box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6), 0 0 30px rgba(212, 175, 55, 0.1);">
      
      <div style="text-align: center; margin-bottom: 40px;">
        <div style="color: #d4af37; font-family: 'Inter', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; margin-bottom: 10px;">GET STARTED TODAY</div>
        <h2 style="font-family: 'Playfair Display', serif; font-size: clamp(30px, 4vw, 40px); font-weight: 700; color: #ffffff; margin-bottom: 12px;">Request a B2B Consultation</h2>
        <p style="font-family: 'Inter', sans-serif; font-size: 15px; color: rgba(245, 237, 230, 0.85); font-weight: 300; max-width: 580px; margin: 0 auto;">
          Fill out your requirements below and certified chocolate educator Aarti Saluja Sahni will respond personally within 24 hours.
        </p>
      </div>

      <?php if ($submissionSuccess): ?>
        <div style="background: rgba(86, 146, 105, 0.25); border: 1px solid #7acb92; color: #7acb92; padding: 22px 28px; border-radius: 16px; text-align: center; font-family: 'Inter', sans-serif; font-size: 15.5px; line-height: 1.6; margin-bottom: 28px; animation: fadeIn 0.4s ease;">
          ✓ Thank you! Your B2B consultation request has been received. We will get in touch with you within 24 hours.
        </div>
      <?php elseif (!empty($submissionError)): ?>
        <div style="background: rgba(255, 107, 107, 0.2); border: 1px solid #ff6b6b; color: #ff6b6b; padding: 18px 24px; border-radius: 16px; text-align: center; font-family: 'Inter', sans-serif; font-size: 14.5px; margin-bottom: 28px;">
          <?php echo htmlspecialchars($submissionError); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="work-with-us.php#inquiry-form" style="display: grid; gap: 24px;">
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px;">
          <div>
            <label style="display: block; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; color: #d4af37; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase;">Your Full Name *</label>
            <input type="text" name="name" required placeholder="e.g. Aarti Sahni" style="width: 100%; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 12px; padding: 14px 18px; color: #ffffff; font-family: 'Inter', sans-serif; font-size: 14.5px; outline: none; transition: border-color 0.3s ease;" onfocus="this.style.borderColor='#d4af37'" onblur="this.style.borderColor='rgba(212, 175, 55, 0.25)'" />
          </div>
          <div>
            <label style="display: block; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; color: #d4af37; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase;">Work Email Address *</label>
            <input type="email" name="email" required placeholder="name@company.com" style="width: 100%; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 12px; padding: 14px 18px; color: #ffffff; font-family: 'Inter', sans-serif; font-size: 14.5px; outline: none; transition: border-color 0.3s ease;" onfocus="this.style.borderColor='#d4af37'" onblur="this.style.borderColor='rgba(212, 175, 55, 0.25)'" />
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px;">
          <div>
            <label style="display: block; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; color: #d4af37; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase;">Phone / WhatsApp</label>
            <input type="text" name="phone" placeholder="+91 98765 43210" style="width: 100%; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 12px; padding: 14px 18px; color: #ffffff; font-family: 'Inter', sans-serif; font-size: 14.5px; outline: none; transition: border-color 0.3s ease;" onfocus="this.style.borderColor='#d4af37'" onblur="this.style.borderColor='rgba(212, 175, 55, 0.25)'" />
          </div>
          <div>
            <label style="display: block; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; color: #d4af37; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase;">Company / Brand Name</label>
            <input type="text" name="company" placeholder="e.g. Artisan Cacao Ltd." style="width: 100%; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 12px; padding: 14px 18px; color: #ffffff; font-family: 'Inter', sans-serif; font-size: 14.5px; outline: none; transition: border-color 0.3s ease;" onfocus="this.style.borderColor='#d4af37'" onblur="this.style.borderColor='rgba(212, 175, 55, 0.25)'" />
          </div>
        </div>

        <div>
          <label style="display: block; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; color: #d4af37; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase;">Primary Service Required</label>
          <select name="service" style="width: 100%; background: #081d12; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 12px; padding: 14px 18px; color: #ffffff; font-family: 'Inter', sans-serif; font-size: 14.5px; outline: none;">
            <option value="Recipe Formulation & R&D">🧪 Recipe Formulation &amp; R&amp;D (Custom Dark, Alt-Milk, Sugar-Free)</option>
            <option value="Corporate Masterclass / Workshop">🏢 Corporate Masterclasses &amp; Executive Workshops</option>
            <option value="Bean-to-Bar Brand Consulting">🏭 Bean-to-Bar Brand Consulting &amp; Factory Setup</option>
            <option value="Media & Press Partnership">🎙️ Media, Press &amp; Brand Sponsorship</option>
            <option value="General B2B Inquiry">✨ General B2B &amp; Custom Collaboration</option>
          </select>
        </div>

        <div>
          <label style="display: block; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; color: #d4af37; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase;">Project Overview / Inquiry Details *</label>
          <textarea name="message" rows="5" required placeholder="Tell us about your project vision, target timeline, or specific formulation requirements..." style="width: 100%; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 12px; padding: 16px 18px; color: #ffffff; font-family: 'Inter', sans-serif; font-size: 14.5px; outline: none; resize: vertical; transition: border-color 0.3s ease;" onfocus="this.style.borderColor='#d4af37'" onblur="this.style.borderColor='rgba(212, 175, 55, 0.25)'"></textarea>
        </div>

        <div style="text-align: center; margin-top: 12px;">
          <button type="submit" class="btn-hero-primary" style="padding: 18px 48px; border: none; cursor: pointer; width: 100%; max-width: 360px; font-size: 15px; font-weight: 700; letter-spacing: 0.5px; box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3);">
            <span>Submit B2B Inquiry</span>
            <span style="font-size: 18px;">&rarr;</span>
          </button>
        </div>

      </form>

    </div>
  </section>

  <!-- B2B Frequently Asked Questions Accordion -->
  <section class="section" style="background-color: #06140c; padding: 90px 24px; border-top: 1px solid rgba(212, 175, 55, 0.15);">
    <div style="max-width: 860px; margin: 0 auto;">
      
      <div style="text-align: center; margin-bottom: 50px;">
        <div style="color: #d4af37; font-family: 'Inter', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; margin-bottom: 8px;">FREQUENTLY ASKED QUESTIONS</div>
        <h2 style="font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: #ffffff; margin-bottom: 10px;">B2B &amp; Consulting FAQs</h2>
        <div style="width: 60px; height: 3px; background: linear-gradient(90deg, #d4af37, transparent); margin: 0 auto;"></div>
      </div>

      <div style="display: flex; flex-direction: column; gap: 16px;">
        
        <details style="background: rgba(14, 34, 22, 0.7); border: 1px solid rgba(212, 175, 55, 0.2); border-radius: 16px; padding: 20px 24px; cursor: pointer;">
          <summary style="font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 600; color: #ffffff; outline: none; display: flex; justify-content: space-between; align-items: center;">
            <span>How does bean-to-bar recipe formulation consulting work?</span>
            <span style="color: #d4af37; font-size: 20px;">+</span>
          </summary>
          <p style="font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.7; color: rgba(245, 237, 230, 0.85); font-weight: 300; margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(212, 175, 55, 0.15);">
            We work with you to understand your target flavor profile, dietary requirements (e.g. vegan, sugar-free, single-origin), and production equipment. We provide exact percentage formulations, roasting thermal curves, conching durations, and tempering SOPs.
          </p>
        </details>

        <details style="background: rgba(14, 34, 22, 0.7); border: 1px solid rgba(212, 175, 55, 0.2); border-radius: 16px; padding: 20px 24px; cursor: pointer;">
          <summary style="font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 600; color: #ffffff; outline: none; display: flex; justify-content: space-between; align-items: center;">
            <span>Can corporate masterclasses be conducted outside Mumbai or virtually?</span>
            <span style="color: #d4af37; font-size: 20px;">+</span>
          </summary>
          <p style="font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.7; color: rgba(245, 237, 230, 0.85); font-weight: 300; margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(212, 175, 55, 0.15);">
            Yes! We conduct live on-site corporate workshops and tasting retreats across major cities in India, as well as live interactive virtual masterclasses worldwide with custom DIY chocolate kits shipped to participants.
          </p>
        </details>

        <details style="background: rgba(14, 34, 22, 0.7); border: 1px solid rgba(212, 175, 55, 0.2); border-radius: 16px; padding: 20px 24px; cursor: pointer;">
          <summary style="font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 600; color: #ffffff; outline: none; display: flex; justify-content: space-between; align-items: center;">
            <span>Do you assist with commercial equipment selection &amp; cocoa bean sourcing?</span>
            <span style="color: #d4af37; font-size: 20px;">+</span>
          </summary>
          <p style="font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.7; color: rgba(245, 237, 230, 0.85); font-weight: 300; margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(212, 175, 55, 0.15);">
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
