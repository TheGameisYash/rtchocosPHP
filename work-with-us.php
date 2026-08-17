<?php
  $pageTitle = "Work With Us — B2B Consulting, R&D & Masterclasses | RT Chocos";
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

<!-- --- WORK WITH US PAGE --- -->
<div id="page-work-with-us" class="page active" style="padding-top:80px; background-color: var(--dark-900);">
  
  <!-- Hero Section -->
  <section class="b2b-hero" style="background: radial-gradient(circle at 50% 40%, #0d2417 0%, #06140c 70%, #030a06 100%); padding: 90px 24px 70px; text-align: center; position: relative; overflow: hidden; border-bottom: 1px solid rgba(212, 175, 55, 0.15);">
    <div style="max-width: 900px; margin: 0 auto; relative; z-index: 2;">
      <div class="wheel-section-badge" style="background: rgba(212, 175, 55, 0.12); border: 1px solid rgba(212, 175, 55, 0.4); color: #d4af37; font-family: 'Inter', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 2.5px; padding: 6px 18px; border-radius: 30px; display: inline-block; margin-bottom: 18px;">
        COLLABORATION &amp; B2B SOLUTIONS
      </div>
      <h1 style="font-family: 'Playfair Display', serif; font-size: clamp(36px, 5vw, 56px); font-weight: 700; color: #ffffff; line-height: 1.15; margin-bottom: 20px;">
        Partner With <span style="color: #d4af37;">RT Chocos</span>
      </h1>
      <p style="font-family: 'Inter', sans-serif; font-size: clamp(15px, 1.8vw, 18px); color: rgba(245, 237, 230, 0.85); line-height: 1.7; max-width: 720px; margin: 0 auto 32px; font-weight: 300;">
        From bean-to-bar recipe formulation and factory setup consulting to executive corporate masterclasses and brand partnerships—work directly with certified chocolate educator Aarti Saluja Sahni.
      </p>
      <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
        <a href="#inquiry-form" class="btn-hero-primary" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px; padding: 14px 32px;">
          <span>Request Consultation</span>
          <span>&rarr;</span>
        </a>
        <a href="#services-grid" class="btn-hero-outline" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px; padding: 14px 32px;">
          <span>Explore Services</span>
        </a>
      </div>
    </div>
  </section>

  <!-- Core B2B Services Grid -->
  <section id="services-grid" class="section" style="background-color: #06140c; padding: 90px 24px;">
    <div style="max-width: 1200px; margin: 0 auto;">
      
      <div style="text-align: center; margin-bottom: 60px;">
        <div style="color: #d4af37; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px;">OUR EXPERTISE</div>
        <h2 style="font-family: 'Playfair Display', serif; font-size: clamp(28px, 3.5vw, 42px); font-weight: 700; color: #ffffff; margin-bottom: 12px;">How We Can Work Together</h2>
        <div style="width: 60px; height: 3px; background: linear-gradient(90deg, #d4af37, transparent); margin: 0 auto;"></div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(270px, 1fr)); gap: 28px;">
        
        <!-- Card 1: Recipe R&D -->
        <div style="background: rgba(14, 34, 22, 0.7); border: 1px solid rgba(212, 175, 55, 0.22); border-radius: 20px; padding: 32px 28px; backdrop-filter: blur(16px); transition: all 0.35s ease;">
          <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(212, 175, 55, 0.12); border: 1px solid rgba(212, 175, 55, 0.3); display: flex; align-items: center; justify-content: center; margin-bottom: 22px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/>
              <line x1="8.5" y1="2" x2="15.5" y2="2"/>
            </svg>
          </div>
          <h3 style="font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 700; color: #ffffff; margin-bottom: 12px;">Recipe Formulation &amp; R&amp;D</h3>
          <p style="font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.65; color: rgba(245, 237, 230, 0.8); font-weight: 300;">
            Custom formulation of single-origin dark chocolate, alt-milk (oat, coconut), sugar-free monk fruit/erythritol recipes, tempering stability, and fat-bloom prevention metrics for chocolate brands.
          </p>
        </div>

        <!-- Card 2: Corporate Masterclasses -->
        <div style="background: rgba(14, 34, 22, 0.7); border: 1px solid rgba(212, 175, 55, 0.22); border-radius: 20px; padding: 32px 28px; backdrop-filter: blur(16px); transition: all 0.35s ease;">
          <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(212, 175, 55, 0.12); border: 1px solid rgba(212, 175, 55, 0.3); display: flex; align-items: center; justify-content: center; margin-bottom: 22px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
              <path d="M6 12v5c0 2 6 2 6 2s6 0 6-2v-5"/>
            </svg>
          </div>
          <h3 style="font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 700; color: #ffffff; margin-bottom: 12px;">Corporate Masterclasses</h3>
          <p style="font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.65; color: rgba(245, 237, 230, 0.8); font-weight: 300;">
            Premium executive workshops, bean-to-bar tasting experiences, and corporate team building masterclasses tailored for luxury brands, hotels, and corporate retreats across Mumbai &amp; India.
          </p>
        </div>

        <!-- Card 3: Brand & Factory Consulting -->
        <div style="background: rgba(14, 34, 22, 0.7); border: 1px solid rgba(212, 175, 55, 0.22); border-radius: 20px; padding: 32px 28px; backdrop-filter: blur(16px); transition: all 0.35s ease;">
          <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(212, 175, 55, 0.12); border: 1px solid rgba(212, 175, 55, 0.3); display: flex; align-items: center; justify-content: center; margin-bottom: 22px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
            </svg>
          </div>
          <h3 style="font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 700; color: #ffffff; margin-bottom: 12px;">Bean-to-Bar Brand Consulting</h3>
          <p style="font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.65; color: rgba(245, 237, 230, 0.8); font-weight: 300;">
            End-to-end consulting for new bean-to-bar startups: equipment selection (roasters, melangers, temperers), Indian cacao estate bean sourcing, conching parameters, and quality control.
          </p>
        </div>

        <!-- Card 4: Media & Press -->
        <div style="background: rgba(14, 34, 22, 0.7); border: 1px solid rgba(212, 175, 55, 0.22); border-radius: 20px; padding: 32px 28px; backdrop-filter: blur(16px); transition: all 0.35s ease;">
          <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(212, 175, 55, 0.12); border: 1px solid rgba(212, 175, 55, 0.3); display: flex; align-items: center; justify-content: center; margin-bottom: 22px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/>
              <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
              <line x1="12" y1="19" x2="12" y2="23"/>
            </svg>
          </div>
          <h3 style="font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 700; color: #ffffff; margin-bottom: 12px;">Media &amp; Brand Partnerships</h3>
          <p style="font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.65; color: rgba(245, 237, 230, 0.8); font-weight: 300;">
            Artisan chocolate brand reviews, ingredient sponsorships, podcasts, keynotes, and media features across the RT Chocos journal and social channels reaching thousands of chocolate enthusiasts.
          </p>
        </div>

      </div>

    </div>
  </section>

  <!-- Consultation Request Form -->
  <section id="inquiry-form" class="section" style="background: radial-gradient(circle at 50% 50%, #0c2417 0%, #06140c 100%); padding: 90px 24px; border-top: 1px solid rgba(212, 175, 55, 0.15);">
    <div style="max-width: 760px; margin: 0 auto; background: rgba(10, 26, 17, 0.85); border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 24px; padding: 48px 36px; backdrop-filter: blur(20px); box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);">
      
      <div style="text-align: center; margin-bottom: 36px;">
        <div style="color: #d4af37; font-family: 'Inter', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; margin-bottom: 8px;">GET IN TOUCH</div>
        <h2 style="font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: #ffffff; margin-bottom: 10px;">Request a B2B Consultation</h2>
        <p style="font-family: 'Inter', sans-serif; font-size: 14.5px; color: rgba(245, 237, 230, 0.8); font-weight: 300;">Fill out the details below and Aarti Saluja Sahni will get back to you within 24 hours.</p>
      </div>

      <?php if ($submissionSuccess): ?>
        <div style="background: rgba(86, 146, 105, 0.2); border: 1px solid #569269; color: #7acb92; padding: 20px 24px; border-radius: 14px; text-align: center; font-family: 'Inter', sans-serif; font-size: 15px; line-height: 1.6; margin-bottom: 24px;">
          ✓ Thank you! Your B2B inquiry has been sent successfully. We will get back to you within 24 hours.
        </div>
      <?php elseif (!empty($submissionError)): ?>
        <div style="background: rgba(255, 107, 107, 0.2); border: 1px solid #ff6b6b; color: #ff6b6b; padding: 16px 20px; border-radius: 14px; text-align: center; font-family: 'Inter', sans-serif; font-size: 14px; margin-bottom: 24px;">
          <?php echo htmlspecialchars($submissionError); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="work-with-us.php#inquiry-form" style="display: grid; gap: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div>
            <label style="display: block; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; color: #d4af37; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase;">Your Name *</label>
            <input type="text" name="name" required placeholder="e.g. Aarti Sahni" style="width: 100%; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 10px; padding: 12px 16px; color: #ffffff; font-family: 'Inter', sans-serif; font-size: 14px; outline: none;" />
          </div>
          <div>
            <label style="display: block; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; color: #d4af37; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase;">Email Address *</label>
            <input type="email" name="email" required placeholder="name@company.com" style="width: 100%; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 10px; padding: 12px 16px; color: #ffffff; font-family: 'Inter', sans-serif; font-size: 14px; outline: none;" />
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div>
            <label style="display: block; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; color: #d4af37; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase;">Phone / WhatsApp</label>
            <input type="text" name="phone" placeholder="+91 98765 43210" style="width: 100%; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 10px; padding: 12px 16px; color: #ffffff; font-family: 'Inter', sans-serif; font-size: 14px; outline: none;" />
          </div>
          <div>
            <label style="display: block; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; color: #d4af37; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase;">Company / Brand Name</label>
            <input type="text" name="company" placeholder="e.g. Artisan Cacao Co." style="width: 100%; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 10px; padding: 12px 16px; color: #ffffff; font-family: 'Inter', sans-serif; font-size: 14px; outline: none;" />
          </div>
        </div>

        <div>
          <label style="display: block; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; color: #d4af37; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase;">Primary Service Interested In</label>
          <select name="service" style="width: 100%; background: #0c2417; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 10px; padding: 12px 16px; color: #ffffff; font-family: 'Inter', sans-serif; font-size: 14px; outline: none;">
            <option value="Recipe Formulation & R&D">Recipe Formulation &amp; R&amp;D</option>
            <option value="Corporate Masterclass / Workshop">Corporate Masterclass / Workshop</option>
            <option value="Bean-to-Bar Brand Consulting">Bean-to-Bar Brand Consulting</option>
            <option value="Media & Press Partnership">Media &amp; Press Partnership</option>
            <option value="General B2B Inquiry">General B2B Inquiry</option>
          </select>
        </div>

        <div>
          <label style="display: block; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; color: #d4af37; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase;">Message / Inquiry Details *</label>
          <textarea name="message" rows="5" required placeholder="Tell us about your project, timeline, or requirements..." style="width: 100%; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 10px; padding: 14px 16px; color: #ffffff; font-family: 'Inter', sans-serif; font-size: 14px; outline: none; resize: vertical;"></textarea>
        </div>

        <div style="text-align: center; margin-top: 10px;">
          <button type="submit" class="btn-hero-primary" style="padding: 16px 40px; border: none; cursor: pointer; width: 100%; max-width: 320px; font-size: 14px;">
            <span>Submit Inquiry</span>
            <span>&rarr;</span>
          </button>
        </div>
      </form>

    </div>
  </section>

</div>

<?php
  include $pathPrefix . 'includes/footer.php';
?>
