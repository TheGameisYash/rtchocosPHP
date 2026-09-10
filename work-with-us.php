<?php
  $pageTitle = "Work With Us — Chocolate R&D, Formulation & Knowledge Collaboration | RT Chocos";
  $pageDescription = "Bring us the question. Partner with RT Chocos for chocolate R&D, product innovation, formulation consulting, ingredient exploration, and industry knowledge collaboration.";
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
              $pdo = get_db();
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

<style>
/* ======================================================================
   WORK WITH US — PROFESSIONAL CHOCOLATE R&D KNOWLEDGE PLATFORM
   All styles use !important to override global theme CSS variables
   ====================================================================== */

/* --- RESET ALL GLOBAL OVERRIDES FOR THIS PAGE --- */
#page-wwu,
#page-wwu * {
  box-sizing: border-box !important;
}

#page-wwu {
  background-color: #0c1a11 !important;
  color: #e8dcc8 !important;
  font-family: 'Inter', sans-serif !important;
  overflow-x: hidden !important;
}

/* Force all text elements to inherit page colors, not theme variables */
#page-wwu h1,
#page-wwu h2,
#page-wwu h3,
#page-wwu h4,
#page-wwu h5 {
  font-family: 'Playfair Display', Georgia, serif !important;
  font-weight: 700 !important;
  color: #ffffff !important;
  letter-spacing: -0.01em !important;
}
#page-wwu p,
#page-wwu span,
#page-wwu li,
#page-wwu div {
  font-family: 'Inter', sans-serif !important;
}
#page-wwu a {
  text-decoration: none !important;
  color: inherit !important;
}

/* Tag label style */
#page-wwu .wwu-tag {
  font-family: 'Inter', sans-serif !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  letter-spacing: 3px !important;
  text-transform: uppercase !important;
  color: #d4af37 !important;
  display: block !important;
}

/* ============================================================
   SECTION 1: HERO (Ultra-Luxury Chocolate R&D Constellation)
   ============================================================ */
#page-wwu .wwu-hero {
  position: relative !important;
  padding: 110px 48px 80px !important;
  background: #06110a !important;
  overflow: hidden !important;
  min-height: calc(100vh - 75px) !important;
  display: flex !important;
  align-items: center !important;
}

/* Background video filling the entire hero - opacity removed (100% solid) */
#page-wwu .wwu-hero-bg-video {
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  width: 100% !important;
  height: 100% !important;
  object-fit: cover !important;
  object-position: 70% center !important;
  z-index: 1 !important;
  pointer-events: none !important;
  opacity: 1 !important;
}

/* Gradient overlay completely removed */
#page-wwu .wwu-hero-video-overlay {
  display: none !important;
}

/* Atmospheric dust background removed */
#page-wwu .wwu-hero::before {
  display: none !important;
}

/* Background animated celestial coordinate lines */
#page-wwu .wwu-hero-bg-lines {
  position: absolute !important;
  top: 0 !important;
  right: 0 !important;
  width: 70% !important;
  height: 100% !important;
  opacity: 0.15 !important;
  pointer-events: none !important;
  overflow: hidden !important;
  z-index: 2 !important;
}
#page-wwu .wwu-hero-bg-lines svg {
  width: 100% !important;
  height: 100% !important;
}

#page-wwu .wwu-hero-inner {
  max-width: 1420px !important;
  margin: 0 auto !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: flex-start !important;
  position: relative !important;
  z-index: 3 !important;
  width: 100% !important;
}

/* --- HERO LEFT COLUMN --- */
#page-wwu .wwu-hero-text {
  display: flex !important;
  flex-direction: column !important;
  max-width: 700px !important;
  width: 100% !important;
}

#page-wwu .wwu-hero-tag-pill {
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  align-self: flex-start !important;
  padding: 6px 15px !important;
  background: rgba(212, 175, 55, 0.1) !important;
  border: 1px solid rgba(212, 175, 55, 0.35) !important;
  border-radius: 30px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 10.5px !important;
  font-weight: 800 !important;
  letter-spacing: 2px !important;
  text-transform: uppercase !important;
  color: #d4af37 !important;
  margin-bottom: 18px !important;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2) !important;
}

#page-wwu .wwu-hero-tag-pill .pill-dot {
  width: 6px !important;
  height: 6px !important;
  border-radius: 50% !important;
  background: #d4af37 !important;
  box-shadow: 0 0 8px rgba(212, 175, 55, 0.9) !important;
}

#page-wwu .wwu-hero h1 {
  font-size: clamp(40px, 4.6vw, 68px) !important;
  line-height: 1.05 !important;
  margin-bottom: 14px !important;
  letter-spacing: -1.5px !important;
  color: #ffffff !important;
  font-weight: 700 !important;
  text-shadow: 0 2px 14px rgba(0, 0, 0, 0.65) !important;
}

#page-wwu .wwu-hero h1 .hl-hero-gold {
  color: #e5b358 !important;
  display: inline-block !important;
  text-shadow: 0 2px 14px rgba(0, 0, 0, 0.65) !important;
}

/* Sub-headline Paragraph */
#page-wwu .wwu-hero-sublead {
  font-family: 'Inter', sans-serif !important;
  font-size: 14.5px !important;
  line-height: 1.6 !important;
  color: #f5efe1 !important;
  max-width: 580px !important;
  margin-bottom: 22px !important;
  font-weight: 500 !important;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.9) !important;
}

/* 2x2 Grid of Interactive Question Cards */
#page-wwu .wwu-hero-pillars-grid {
  display: grid !important;
  grid-template-columns: 1fr 1fr !important;
  gap: 12px !important;
  margin-bottom: 24px !important;
  max-width: 620px !important;
}

#page-wwu .hero-pillar-card {
  display: flex !important;
  flex-direction: column !important;
  gap: 5px !important;
  padding: 14px 16px !important;
  background: rgba(7, 22, 14, 0.65) !important;
  border: 1px solid rgba(212, 175, 55, 0.22) !important;
  border-radius: 12px !important;
  cursor: pointer !important;
  backdrop-filter: blur(12px) !important;
  -webkit-backdrop-filter: blur(12px) !important;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35) !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  position: relative !important;
  overflow: hidden !important;
}

#page-wwu .hero-pillar-card::before {
  content: '' !important;
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  width: 3px !important;
  height: 100% !important;
  background: #d4af37 !important;
  opacity: 0.35 !important;
  transition: all 0.3s ease !important;
}

#page-wwu .hero-pillar-card:hover {
  background: rgba(212, 175, 55, 0.09) !important;
  border-color: rgba(212, 175, 55, 0.45) !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 8px 22px rgba(0, 0, 0, 0.35), 0 0 14px rgba(212, 175, 55, 0.12) !important;
}

#page-wwu .hero-pillar-card:hover::before {
  opacity: 1 !important;
  width: 4px !important;
}

#page-wwu .card-icon-tag {
  font-family: 'Inter', sans-serif !important;
  font-size: 9px !important;
  font-weight: 800 !important;
  letter-spacing: 1.5px !important;
  color: #d4af37 !important;
  text-transform: uppercase !important;
  display: flex !important;
  align-items: center !important;
  gap: 4px !important;
}

#page-wwu .hero-pillar-card strong {
  font-family: 'Playfair Display', serif !important;
  font-size: 14.5px !important;
  font-weight: 600 !important;
  color: #fcf8f0 !important;
  line-height: 1.25 !important;
  transition: color 0.3s ease !important;
}

#page-wwu .hero-pillar-card:hover strong {
  color: #d4af37 !important;
}

#page-wwu .hero-pillar-card p {
  font-family: 'Inter', sans-serif !important;
  font-size: 11px !important;
  color: #9f9683 !important;
  line-height: 1.35 !important;
  margin: 0 !important;
}

/* Actions Wrap & Trust Spec Strip */
#page-wwu .wwu-hero-actions-wrap {
  display: flex !important;
  flex-direction: column !important;
  gap: 14px !important;
}

#page-wwu .wwu-hero-actions {
  display: flex !important;
  flex-wrap: wrap !important;
  align-items: center !important;
  gap: 14px !important;
}

#page-wwu .wwu-hero-btn-gold {
  display: inline-flex !important;
  align-items: center !important;
  gap: 10px !important;
  padding: 13px 28px !important;
  background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%) !important;
  color: #0c1a11 !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 12px !important;
  font-weight: 800 !important;
  letter-spacing: 1.5px !important;
  text-transform: uppercase !important;
  border-radius: 8px !important;
  border: none !important;
  cursor: pointer !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  box-shadow: 0 6px 20px rgba(212, 175, 55, 0.28) !important;
}

#page-wwu .wwu-hero-btn-gold:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 10px 28px rgba(212, 175, 55, 0.45) !important;
  background: linear-gradient(135deg, #e5c158 0%, #c99718 100%) !important;
}

#page-wwu .wwu-hero-btn-gold svg {
  stroke: #0c1a11 !important;
  stroke-width: 2.5 !important;
  width: 13px !important;
  height: 13px !important;
  fill: none !important;
  transition: transform 0.3s ease !important;
}
#page-wwu .wwu-hero-btn-gold:hover svg {
  transform: translateX(4px) !important;
}

#page-wwu .wwu-hero-btn-outline {
  display: inline-flex !important;
  align-items: center !important;
  gap: 10px !important;
  padding: 12px 24px !important;
  background: rgba(7, 22, 14, 0.75) !important;
  backdrop-filter: blur(8px) !important;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35) !important;
  border: 1.5px solid rgba(212, 175, 55, 0.5) !important;
  color: #f5efe1 !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 12px !important;
  font-weight: 700 !important;
  letter-spacing: 1px !important;
  text-transform: uppercase !important;
  border-radius: 8px !important;
  cursor: pointer !important;
  transition: all 0.3s ease !important;
}

#page-wwu .wwu-hero-btn-outline:hover {
  background: rgba(212, 175, 55, 0.12) !important;
  border-color: #d4af37 !important;
  color: #ffffff !important;
  transform: translateY(-2px) !important;
}

#page-wwu .wwu-hero-btn-outline svg {
  stroke: #d4af37 !important;
  stroke-width: 2.2 !important;
  width: 13px !important;
  height: 13px !important;
  fill: none !important;
  transition: transform 0.3s ease !important;
}
#page-wwu .wwu-hero-btn-outline:hover svg {
  transform: translateY(3px) !important;
}

#page-wwu .wwu-hero-spec-strip {
  display: flex !important;
  flex-wrap: wrap !important;
  align-items: center !important;
  gap: 9px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 11px !important;
  color: #8c826e !important;
  letter-spacing: 0.5px !important;
}

#page-wwu .wwu-hero-spec-strip .spec-item {
  display: inline-flex !important;
  align-items: center !important;
  gap: 5px !important;
}

#page-wwu .wwu-hero-spec-strip .spec-dot {
  color: #d4af37 !important;
  font-size: 9px !important;
}

#page-wwu .wwu-hero-spec-strip .spec-sep {
  opacity: 0.35 !important;
  color: #d4af37 !important;
}

/* Radar Badge on Visual */
#page-wwu .wwu-radar-tag {
  position: absolute !important;
  top: 10px !important;
  right: 10px !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 7px !important;
  padding: 5px 12px !important;
  background: rgba(12, 26, 17, 0.75) !important;
  backdrop-filter: blur(8px) !important;
  border: 1px solid rgba(212, 175, 55, 0.25) !important;
  border-radius: 20px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 9px !important;
  font-weight: 700 !important;
  letter-spacing: 1.5px !important;
  text-transform: uppercase !important;
  color: #d4af37 !important;
  z-index: 5 !important;
  pointer-events: none !important;
}

#page-wwu .wwu-radar-tag .radar-beacon {
  width: 6px !important;
  height: 6px !important;
  border-radius: 50% !important;
  background: #2ecc71 !important;
  box-shadow: 0 0 8px #2ecc71 !important;
  animation: wwuBeaconPulse 2s infinite !important;
}

@keyframes wwuBeaconPulse {
  0% { transform: scale(0.9); opacity: 0.7; }
  50% { transform: scale(1.3); opacity: 1; }
  100% { transform: scale(0.9); opacity: 0.7; }
}/* --- FLOATING LAB VIDEO BADGE & CONTROLS --- */
#page-wwu .wwu-hero-video-bar {
  position: absolute !important;
  bottom: 28px !important;
  right: 48px !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 10px !important;
  z-index: 4 !important;
}

#page-wwu .wwu-video-badge {
  display: inline-flex !important;
  align-items: center !important;
  gap: 7px !important;
  padding: 7px 15px !important;
  background: rgba(12, 26, 17, 0.85) !important;
  backdrop-filter: blur(12px) !important;
  -webkit-backdrop-filter: blur(12px) !important;
  border: 1px solid rgba(212, 175, 55, 0.35) !important;
  border-radius: 30px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 10.5px !important;
  font-weight: 700 !important;
  letter-spacing: 1.2px !important;
  text-transform: uppercase !important;
  color: #f5e4b2 !important;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.45) !important;
}

#page-wwu .wwu-video-badge .radar-beacon {
  width: 7px !important;
  height: 7px !important;
  border-radius: 50% !important;
  background: #25d366 !important;
  box-shadow: 0 0 8px #25d366 !important;
  animation: wwuBeaconPulse 2s infinite !important;
}

#page-wwu .wwu-video-ctrls {
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  pointer-events: auto !important;
}

#page-wwu .wwu-video-ctrl-btn {
  width: 32px !important;
  height: 32px !important;
  border-radius: 50% !important;
  background: rgba(12, 26, 17, 0.85) !important;
  backdrop-filter: blur(12px) !important;
  -webkit-backdrop-filter: blur(12px) !important;
  border: 1px solid rgba(212, 175, 55, 0.35) !important;
  color: #f5e4b2 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  cursor: pointer !important;
  transition: all 0.25s ease !important;
  outline: none !important;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.45) !important;
}

#page-wwu .wwu-video-ctrl-btn:hover {
  background: rgba(212, 175, 55, 0.25) !important;
  border-color: #d4af37 !important;
  color: #ffffff !important;
  transform: scale(1.08) !important;
}

@media (max-width: 991px) {
  #page-wwu .wwu-hero {
    padding: 85px 24px 50px !important;
    min-height: auto !important;
  }
  #page-wwu .wwu-hero-video-overlay {
    background: 
      linear-gradient(to bottom, rgba(6, 17, 10, 0.94) 0%, rgba(6, 17, 10, 0.85) 60%, rgba(6, 17, 10, 0.96) 100%) !important;
  }
  #page-wwu .wwu-hero-text {
    max-width: 100% !important;
  }
  #page-wwu .wwu-hero-video-bar {
    position: static !important;
    margin-top: 24px !important;
    align-self: flex-start !important;
  }
}


/* ============================================================
   SECTION 2: HOW WE COLLABORATE & WHAT WE HELP WITH
   ============================================================ */
#page-wwu .wwu-collab-section {
  position: relative !important;
  padding: 38px 40px 42px !important;
  background: #faf6ef !important;
  border-top: 1px solid rgba(212, 175, 55, 0.22) !important;
  border-bottom: 1px solid rgba(212, 175, 55, 0.16) !important;
  overflow: hidden !important;
}

#page-wwu .wwu-collab-container {
  max-width: 1360px !important;
  margin: 0 auto !important;
}

/* TOP PART: Intro + 6-Step Journey Flow */
#page-wwu .wwu-collab-top-row {
  display: grid !important;
  grid-template-columns: 280px 1fr !important;
  gap: 36px !important;
  align-items: center !important;
  margin-bottom: 28px !important;
}

#page-wwu .wwu-collab-intro {
  display: flex !important;
  flex-direction: column !important;
}

#page-wwu .wwu-collab-tag {
  color: #8b6b23 !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 10px !important;
  font-weight: 800 !important;
  letter-spacing: 2.2px !important;
  text-transform: uppercase !important;
  margin-bottom: 8px !important;
}

#page-wwu .wwu-collab-heading {
  font-family: 'Playfair Display', serif !important;
  font-size: clamp(23px, 2.3vw, 29px) !important;
  line-height: 1.18 !important;
  color: #1a1711 !important;
  margin: 0 0 10px 0 !important;
  font-weight: 700 !important;
}

#page-wwu .wwu-collab-gold-dash {
  width: 32px !important;
  height: 2px !important;
  background: #c59b27 !important;
  margin-bottom: 12px !important;
}

#page-wwu .wwu-collab-subtext {
  font-family: 'Inter', sans-serif !important;
  font-size: 12.5px !important;
  line-height: 1.55 !important;
  color: #4a453b !important;
  margin: 0 !important;
  font-weight: 400 !important;
}

/* 6-Step Process Flow */
#page-wwu .wwu-process-track-wrap {
  position: relative !important;
  width: 100% !important;
}

#page-wwu .wwu-process-line {
  position: absolute !important;
  top: 41px !important;
  left: 6% !important;
  right: 6% !important;
  height: 1px !important;
  background: rgba(184, 134, 11, 0.28) !important;
  z-index: 1 !important;
}

#page-wwu .wwu-process-steps {
  display: grid !important;
  grid-template-columns: repeat(6, 1fr) !important;
  gap: 10px !important;
  position: relative !important;
  z-index: 2 !important;
}

#page-wwu .wwu-step-node {
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  text-align: center !important;
  padding: 0 4px !important;
}

#page-wwu .wwu-step-num {
  font-family: 'Playfair Display', serif !important;
  font-size: 13px !important;
  font-weight: 700 !important;
  color: #b8860b !important;
  margin-bottom: 6px !important;
  line-height: 1 !important;
}

#page-wwu .wwu-step-badge {
  width: 48px !important;
  height: 48px !important;
  border-radius: 50% !important;
  background: #052014 !important;
  border: 1.5px solid rgba(212, 175, 55, 0.5) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  margin-bottom: 10px !important;
  box-shadow: 0 4px 14px rgba(4, 25, 15, 0.22) !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  cursor: default !important;
}

#page-wwu .wwu-step-node:hover .wwu-step-badge {
  transform: translateY(-3px) scale(1.06) !important;
  border-color: #d4af37 !important;
  box-shadow: 0 6px 18px rgba(212, 175, 55, 0.35) !important;
  background: #072e1d !important;
}

#page-wwu .wwu-step-badge svg {
  width: 20px !important;
  height: 20px !important;
  stroke: #e8e2d5 !important;
}

#page-wwu .wwu-step-title {
  font-family: 'Inter', sans-serif !important;
  font-size: 11px !important;
  font-weight: 800 !important;
  letter-spacing: 1.2px !important;
  color: #1a1711 !important;
  text-transform: uppercase !important;
  margin: 0 0 5px 0 !important;
}

#page-wwu .wwu-step-desc {
  font-family: 'Inter', sans-serif !important;
  font-size: 11px !important;
  line-height: 1.4 !important;
  color: #554f43 !important;
  margin: 0 !important;
  font-weight: 400 !important;
}

/* MIDDLE DIVIDER: WHAT WE HELP WITH */
#page-wwu .wwu-help-divider-wrap {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 20px !important;
  margin: 24px 0 22px 0 !important;
}

#page-wwu .wwu-help-rule-line {
  flex: 1 !important;
  height: 1px !important;
  background: rgba(184, 134, 11, 0.22) !important;
}

#page-wwu .wwu-help-tag {
  font-family: 'Playfair Display', serif !important;
  font-size: 12px !important;
  font-weight: 700 !important;
  letter-spacing: 2.5px !important;
  color: #7d5f1c !important;
  text-transform: uppercase !important;
  white-space: nowrap !important;
}

/* BOTTOM PART: 6 CAPABILITY CARDS WITH SLEEK VERTICAL DIVIDERS */
#page-wwu .wwu-help-grid {
  display: grid !important;
  grid-template-columns: repeat(6, 1fr) !important;
  gap: 0 !important;
}

#page-wwu .wwu-help-col {
  padding: 4px 14px !important;
  border-right: 1px solid rgba(184, 134, 11, 0.18) !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  text-align: center !important;
  transition: all 0.3s ease !important;
}

#page-wwu .wwu-help-col:last-child {
  border-right: none !important;
}

#page-wwu .wwu-help-icon {
  width: 38px !important;
  height: 38px !important;
  margin-bottom: 10px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: #5c5242 !important;
  transition: all 0.3s ease !important;
}

#page-wwu .wwu-help-icon svg {
  width: 30px !important;
  height: 30px !important;
  stroke: #5c5242 !important;
  transition: all 0.3s ease !important;
}

#page-wwu .wwu-help-col:hover .wwu-help-icon svg {
  stroke: #b8860b !important;
  transform: translateY(-2px) scale(1.05) !important;
}

#page-wwu .wwu-help-col h4 {
  font-family: 'Inter', sans-serif !important;
  font-size: 10.5px !important;
  font-weight: 800 !important;
  letter-spacing: 0.8px !important;
  color: #1a1711 !important;
  text-transform: uppercase !important;
  margin: 0 0 6px 0 !important;
  line-height: 1.3 !important;
  min-height: 28px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

#page-wwu .wwu-help-col p {
  font-family: 'Inter', sans-serif !important;
  font-size: 11px !important;
  line-height: 1.45 !important;
  color: #554f43 !important;
  margin: 0 !important;
  font-weight: 400 !important;
}


/* ============================================================
   SECTION 3: WHERE SHOULD WE BEGIN (cream)
   ============================================================ */
#page-wwu .wwu-begin {
  padding: 80px 40px 100px !important;
  background: #f5f0e6 !important;
  border-top: 1px solid #e2d8c6 !important;
}

#page-wwu .wwu-begin-header {
  text-align: center !important;
  margin-bottom: 56px !important;
}

/* Center rule title matching reference */
#page-wwu .wwu-heading-rule {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 24px !important;
  max-width: 820px !important;
  margin: 0 auto 12px !important;
}

#page-wwu .wwu-heading-rule .rule-line {
  flex: 1 !important;
  height: 1px !important;
  background: #cbbfa9 !important;
}

#page-wwu .wwu-heading-rule .rule-title {
  font-family: 'Playfair Display', serif !important;
  font-size: clamp(22px, 2.6vw, 32px) !important;
  font-weight: 700 !important;
  letter-spacing: 2px !important;
  color: #1a1a14 !important;
  text-transform: uppercase !important;
  margin: 0 !important;
  white-space: nowrap !important;
}

#page-wwu .wwu-begin-header p {
  font-size: 15px !important;
  color: #6b6559 !important;
  font-weight: 400 !important;
  margin: 0 !important;
}

#page-wwu .wwu-begin-grid {
  max-width: 1100px !important;
  margin: 0 auto !important;
  display: grid !important;
  grid-template-columns: repeat(4, 1fr) !important;
  gap: 20px !important;
}

#page-wwu .wwu-card {
  background: #ffffff !important;
  border: 1px solid #e0d6c4 !important;
  border-radius: 16px !important;
  padding: 36px 24px 30px !important;
  text-align: center !important;
  transition: all 0.35s cubic-bezier(0.16,1,0.3,1) !important;
  cursor: default !important;
}
#page-wwu .wwu-card:hover {
  border-color: #d4af37 !important;
  box-shadow: 0 12px 40px rgba(139,90,43,0.1) !important;
  transform: translateY(-6px) !important;
}
#page-wwu .wwu-card .card-icon {
  width: 56px !important;
  height: 56px !important;
  margin: 0 auto 20px !important;
  border-radius: 50% !important;
  background: rgba(212,175,55,0.08) !important;
  border: 1.5px solid rgba(212,175,55,0.22) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}
#page-wwu .wwu-card .card-icon svg {
  width: 24px !important;
  height: 24px !important;
  stroke: #b8941e !important;
  fill: none !important;
  stroke-width: 1.6 !important;
  stroke-linecap: round !important;
  stroke-linejoin: round !important;
}
#page-wwu .wwu-card h4 {
  font-size: 17px !important;
  color: #1a1a14 !important;
  margin-bottom: 8px !important;
  font-weight: 700 !important;
  letter-spacing: 0.2px !important;
}
#page-wwu .wwu-card .card-desc {
  font-size: 13.5px !important;
  color: #6b6559 !important;
  font-weight: 400 !important;
  font-style: italic !important;
  margin-bottom: 18px !important;
  line-height: 1.5 !important;
}
#page-wwu .wwu-card .card-tags {
  display: flex !important;
  flex-wrap: wrap !important;
  gap: 5px !important;
  justify-content: center !important;
}
#page-wwu .wwu-card .card-tags span {
  font-size: 10.5px !important;
  font-weight: 500 !important;
  color: #5a5647 !important;
  padding: 4px 10px !important;
  border-radius: 20px !important;
  background: #f0ebe0 !important;
  border: 1px solid #e0d6c4 !important;
  line-height: 1.3 !important;
}


/* ============================================================
   SECTION 4: COLLABORATION AREAS (Ultra-Premium Orbital Hub)
   ============================================================ */
#page-wwu .wwu-areas {
  position: relative !important;
  padding: 60px 36px 65px !important;
  background: radial-gradient(ellipse at 70% 50%, #fdfbf7 0%, #f7f2e8 50%, #ede4d2 100%) !important;
  border-top: 1px solid #dfd4bf !important;
  border-bottom: 1px solid #dfd4bf !important;
  overflow: hidden !important;
}

/* Subtle background luxury watermark & coordinate grid */
#page-wwu .wwu-areas::before {
  content: '' !important;
  position: absolute !important;
  top: -80px !important;
  right: -80px !important;
  width: 650px !important;
  height: 650px !important;
  background: radial-gradient(circle, rgba(212, 175, 55, 0.09) 0%, rgba(212, 175, 55, 0) 70%) !important;
  pointer-events: none !important;
  z-index: 1 !important;
}

/* Left background ambient glow */
#page-wwu .wwu-areas::after {
  content: '' !important;
  position: absolute !important;
  bottom: -60px !important;
  left: -60px !important;
  width: 500px !important;
  height: 500px !important;
  background: radial-gradient(circle, rgba(139, 90, 43, 0.06) 0%, transparent 70%) !important;
  pointer-events: none !important;
  z-index: 1 !important;
}

#page-wwu .wwu-areas-inner {
  position: relative !important;
  z-index: 2 !important;
  max-width: 1320px !important;
  margin: 0 auto !important;
  display: grid !important;
  grid-template-columns: 360px 1fr !important;
  gap: 46px !important;
  align-items: center !important;
}

/* Left text panel */
#page-wwu .wwu-areas-left {
  display: flex !important;
  flex-direction: column !important;
  justify-content: center !important;
  position: relative !important;
}

#page-wwu .wwu-areas-left .wwu-tag-pill {
  display: inline-flex !important;
  align-items: center !important;
  gap: 7px !important;
  align-self: flex-start !important;
  padding: 5px 13px !important;
  background: rgba(139, 90, 43, 0.08) !important;
  border: 1px solid rgba(212, 175, 55, 0.35) !important;
  border-radius: 30px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 10px !important;
  font-weight: 800 !important;
  letter-spacing: 2px !important;
  text-transform: uppercase !important;
  color: #8b5a2b !important;
  margin-bottom: 14px !important;
}

#page-wwu .wwu-areas-left .wwu-tag-pill .pill-dot {
  width: 5px !important;
  height: 5px !important;
  border-radius: 50% !important;
  background: #d4af37 !important;
  box-shadow: 0 0 6px rgba(212, 175, 55, 0.8) !important;
}

#page-wwu .wwu-areas-left h2 {
  font-size: clamp(28px, 2.7vw, 38px) !important;
  line-height: 1.15 !important;
  color: #1a1a14 !important;
  margin-bottom: 14px !important;
  font-weight: 700 !important;
}

#page-wwu .wwu-areas-left h2 .hl-gold {
  color: #8b5a2b !important;
  background: linear-gradient(135deg, #8b5a2b 0%, #b8941e 50%, #d4af37 100%) !important;
  -webkit-background-clip: text !important;
  -webkit-text-fill-color: transparent !important;
  display: inline-block !important;
}

#page-wwu .wwu-areas-left .wwu-areas-desc {
  font-size: 14px !important;
  line-height: 1.6 !important;
  color: #524d40 !important;
  margin-bottom: 20px !important;
}

/* Feature bullets */
#page-wwu .wwu-areas-features {
  display: flex !important;
  flex-direction: column !important;
  gap: 10px !important;
  margin-bottom: 24px !important;
  padding-left: 0 !important;
  list-style: none !important;
}

#page-wwu .wwu-areas-features li {
  display: flex !important;
  align-items: center !important;
  gap: 10px !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  color: #2c271e !important;
}

#page-wwu .wwu-areas-features li svg {
  width: 15px !important;
  height: 15px !important;
  flex-shrink: 0 !important;
  fill: #b8941e !important;
}

/* Primary CTA Button */
#page-wwu .wwu-btn-areas-cta {
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  align-self: flex-start !important;
  padding: 12px 24px !important;
  background: #1a1a14 !important;
  color: #f6f0e4 !important;
  border: 1.5px solid #1a1a14 !important;
  border-radius: 7px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 11.5px !important;
  font-weight: 700 !important;
  letter-spacing: 1.2px !important;
  text-transform: uppercase !important;
  cursor: pointer !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  box-shadow: 0 4px 14px rgba(26, 26, 20, 0.15) !important;
}

#page-wwu .wwu-btn-areas-cta:hover {
  background: #d4af37 !important;
  color: #1a1a14 !important;
  border-color: #d4af37 !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 18px rgba(212, 175, 55, 0.35) !important;
}

#page-wwu .wwu-btn-areas-cta svg {
  transition: transform 0.3s ease !important;
}
#page-wwu .wwu-btn-areas-cta:hover svg {
  transform: translateX(4px) !important;
}

/* ============================================================
   DIAGRAM STAGE & ORBITAL ARCHITECTURE (Desktop)
   ============================================================ */
#page-wwu .wwu-diagram-stage {
  position: relative !important;
  width: 100% !important;
  max-width: 680px !important;
  height: 460px !important;
  margin: 0 auto !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

/* Background SVG Orbit & Rays */
#page-wwu .wwu-orbital-svg {
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  width: 100% !important;
  height: 100% !important;
  pointer-events: none !important;
  z-index: 1 !important;
  overflow: visible !important;
}

/* Rotating dashed orbit */
#page-wwu .wwu-orbit-dash {
  transform-origin: 340px 230px !important;
  animation: wwuRotateOrbit 110s linear infinite !important;
}

@keyframes wwuRotateOrbit {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Center Core Nucleus — Refined & Elegant Luxury Seal */
#page-wwu .wwu-diagram-center {
  position: absolute !important;
  top: 50% !important;
  left: 50% !important;
  transform: translate(-50%, -50%) !important;
  width: 98px !important;
  height: 98px !important;
  border-radius: 50% !important;
  background: radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.98) 0%, #fbf8f0 65%, #eee3cb 100%) !important;
  border: 1.5px solid rgba(212, 175, 55, 0.55) !important;
  box-shadow: 
    0 0 0 5px rgba(212, 175, 55, 0.08),
    0 0 24px rgba(212, 175, 55, 0.24),
    0 5px 14px rgba(26, 26, 20, 0.05) !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  justify-content: center !important;
  text-align: center !important;
  padding: 8px !important;
  z-index: 5 !important;
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
  cursor: default !important;
}

#page-wwu .wwu-diagram-center:hover {
  transform: translate(-50%, -50%) scale(1.08) !important;
  border-color: #d4af37 !important;
  box-shadow: 
    0 0 0 7px rgba(212, 175, 55, 0.16),
    0 0 32px rgba(212, 175, 55, 0.38),
    0 8px 20px rgba(26, 26, 20, 0.08) !important;
}

#page-wwu .wwu-diagram-center .dc-pulse-ring {
  position: absolute !important;
  top: -5px !important;
  left: -5px !important;
  right: -5px !important;
  bottom: -5px !important;
  border-radius: 50% !important;
  border: 1px dashed rgba(184, 148, 30, 0.45) !important;
  animation: wwuPulseCore 3.5s ease-in-out infinite alternate !important;
  pointer-events: none !important;
}

@keyframes wwuPulseCore {
  0% { transform: scale(0.96); opacity: 0.4; }
  100% { transform: scale(1.08); opacity: 0.9; }
}

#page-wwu .wwu-diagram-center .dc-the {
  font-family: 'Inter', sans-serif !important;
  font-size: 7px !important;
  font-weight: 800 !important;
  letter-spacing: 1.8px !important;
  text-transform: uppercase !important;
  color: #8b5a2b !important;
  margin-bottom: 2px !important;
  transition: color 0.3s ease !important;
}

#page-wwu .wwu-diagram-center .dc-title {
  font-family: 'Playfair Display', serif !important;
  font-size: 11.5px !important;
  font-weight: 800 !important;
  color: #1a1a14 !important;
  line-height: 1.15 !important;
  letter-spacing: 0.3px !important;
  transition: all 0.3s ease !important;
}

#page-wwu .wwu-diagram-center .dc-sub {
  font-size: 7.5px !important;
  color: #726855 !important;
  font-style: italic !important;
  margin-top: 2px !important;
  line-height: 1.15 !important;
  transition: color 0.3s ease !important;
}

/* ============================================================
   COLLABORATION NODE CARDS (Desktop Positions)
   ============================================================ */
#page-wwu .wwu-area-card {
  position: absolute !important;
  width: 196px !important;
  background: rgba(255, 255, 255, 0.94) !important;
  backdrop-filter: blur(14px) !important;
  -webkit-backdrop-filter: blur(14px) !important;
  border: 1px solid rgba(212, 175, 55, 0.3) !important;
  border-radius: 12px !important;
  padding: 10px 13px !important;
  box-shadow: 
    0 4px 16px rgba(26, 26, 20, 0.04),
    0 1px 4px rgba(139, 90, 43, 0.04) !important;
  z-index: 4 !important;
  cursor: pointer !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

/* Hover on general cards */
#page-wwu .card-solve:hover,
#page-wwu .card-explore:hover,
#page-wwu .card-connect:hover,
#page-wwu .card-learn:hover {
  transform: translateY(-3px) scale(1.02) !important;
  background: rgba(255, 255, 255, 0.98) !important;
  border-color: #d4af37 !important;
  box-shadow: 
    0 8px 24px rgba(212, 175, 55, 0.22),
    0 2px 8px rgba(26, 26, 20, 0.06) !important;
}

/* Card Header (Icon + Tag) */
#page-wwu .wwu-area-card .card-head {
  display: flex !important;
  align-items: center !important;
  gap: 8px !important;
  margin-bottom: 5px !important;
}

#page-wwu .wwu-area-card .area-icon {
  width: 26px !important;
  height: 26px !important;
  border-radius: 7px !important;
  background: linear-gradient(135deg, rgba(212, 175, 55, 0.16) 0%, rgba(139, 90, 43, 0.08) 100%) !important;
  border: 1px solid rgba(212, 175, 55, 0.35) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  flex-shrink: 0 !important;
  transition: all 0.3s ease !important;
}

#page-wwu .wwu-area-card:hover .area-icon {
  background: #1a1a14 !important;
  border-color: #1a1a14 !important;
}

#page-wwu .wwu-area-card .area-icon svg {
  width: 13px !important;
  height: 13px !important;
  stroke: #8b5a2b !important;
  fill: none !important;
  stroke-width: 2 !important;
  transition: stroke 0.3s ease !important;
}

#page-wwu .wwu-area-card:hover .area-icon svg {
  stroke: #d4af37 !important;
}

#page-wwu .wwu-area-card .area-label {
  font-family: 'Inter', sans-serif !important;
  font-size: 8.5px !important;
  font-weight: 800 !important;
  letter-spacing: 1.5px !important;
  text-transform: uppercase !important;
  color: #8b5a2b !important;
  display: block !important;
}

#page-wwu .wwu-area-card h4 {
  font-family: 'Playfair Display', serif !important;
  font-size: 13px !important;
  color: #1a1a14 !important;
  margin: 0 0 3px 0 !important;
  font-weight: 700 !important;
  line-height: 1.2 !important;
  transition: color 0.3s ease !important;
}

#page-wwu .wwu-area-card:hover h4 {
  color: #8b5a2b !important;
}

#page-wwu .wwu-area-card p {
  font-size: 10px !important;
  line-height: 1.38 !important;
  color: #555043 !important;
  margin: 0 0 6px 0 !important;
}

#page-wwu .wwu-area-card .card-action {
  display: inline-flex !important;
  align-items: center !important;
  gap: 4px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 9px !important;
  font-weight: 700 !important;
  letter-spacing: 0.5px !important;
  color: #8b5a2b !important;
  text-transform: uppercase !important;
  transition: all 0.2s ease !important;
}

#page-wwu .wwu-area-card:hover .card-action {
  color: #1a1a14 !important;
  gap: 6px !important;
}

/* --- PRECISE POSITIONS (5 Nodes on 680x460px canvas with center at 340, 230) --- */
/* 1. CREATE (Top Center) — FIXED CENTER HOVER */
#page-wwu .card-create {
  top: 0px !important;
  left: 50% !important;
  transform: translateX(-50%) !important;
  text-align: center !important;
  width: 204px !important;
}
#page-wwu .card-create .card-head {
  justify-content: center !important;
}
#page-wwu .card-create:hover {
  transform: translateX(-50%) translateY(-3px) scale(1.02) !important;
  background: rgba(255, 255, 255, 0.98) !important;
  border-color: #d4af37 !important;
  box-shadow: 
    0 8px 24px rgba(212, 175, 55, 0.22),
    0 2px 8px rgba(26, 26, 20, 0.06) !important;
}

/* 2. SOLVE (Top Right) */
#page-wwu .card-solve {
  top: 70px !important;
  right: 0px !important;
  width: 196px !important;
  text-align: left !important;
}

/* 3. EXPLORE (Bottom Right) */
#page-wwu .card-explore {
  bottom: 0px !important;
  right: 15px !important;
  width: 196px !important;
  text-align: left !important;
}

/* 4. CONNECT (Bottom Left) */
#page-wwu .card-connect {
  bottom: 0px !important;
  left: 15px !important;
  width: 196px !important;
  text-align: left !important;
}

/* 5. LEARN (Top Left) */
#page-wwu .card-learn {
  top: 70px !important;
  left: 0px !important;
  width: 196px !important;
  text-align: left !important;
}

/* Mobile Flow & Stepper Architecture */
#page-wwu .wwu-mobile-areas-flow {
  display: none !important;
  flex-direction: column !important;
  gap: 14px !important;
  width: 100% !important;
}

#page-wwu .wwu-mob-center-badge {
  text-align: center !important;
  padding: 16px 20px !important;
  background: radial-gradient(circle at 50% 50%, #ffffff 0%, #fbf8f0 60%, #eee4d0 100%) !important;
  border: 1.5px solid rgba(212, 175, 55, 0.4) !important;
  border-radius: 12px !important;
  margin-bottom: 8px !important;
  box-shadow: 0 4px 15px rgba(212, 175, 55, 0.12) !important;
}

#page-wwu .mob-badge-tag {
  font-family: 'Inter', sans-serif !important;
  font-size: 8.5px !important;
  font-weight: 800 !important;
  letter-spacing: 2px !important;
  text-transform: uppercase !important;
  color: #8b5a2b !important;
  display: block !important;
  margin-bottom: 2px !important;
}

#page-wwu .mob-badge-title {
  font-family: 'Playfair Display', serif !important;
  font-size: 16px !important;
  font-weight: 800 !important;
  color: #1a1a14 !important;
  margin: 0 0 2px 0 !important;
}

#page-wwu .mob-badge-desc {
  font-size: 9.5px !important;
  color: #6b614e !important;
  font-style: italic !important;
}

#page-wwu .wwu-mob-cards-list {
  display: flex !important;
  flex-direction: column !important;
  gap: 10px !important;
}

#page-wwu .wwu-mob-card {
  display: flex !important;
  align-items: flex-start !important;
  gap: 12px !important;
  background: rgba(255, 255, 255, 0.92) !important;
  border: 1px solid rgba(212, 175, 55, 0.3) !important;
  border-radius: 10px !important;
  padding: 12px 14px !important;
  cursor: pointer !important;
  transition: all 0.25s ease !important;
}

#page-wwu .wwu-mob-card:hover {
  border-color: #d4af37 !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 16px rgba(212, 175, 55, 0.18) !important;
}

#page-wwu .mob-card-side .mob-icon {
  width: 32px !important;
  height: 32px !important;
  border-radius: 8px !important;
  background: rgba(212, 175, 55, 0.12) !important;
  border: 1px solid rgba(212, 175, 55, 0.35) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

#page-wwu .mob-card-side .mob-icon svg {
  width: 15px !important;
  height: 15px !important;
  stroke: #8b5a2b !important;
  fill: none !important;
  stroke-width: 2 !important;
}

#page-wwu .mob-card-content {
  flex: 1 !important;
}

#page-wwu .mob-card-content .mob-tag {
  font-family: 'Inter', sans-serif !important;
  font-size: 8.5px !important;
  font-weight: 800 !important;
  letter-spacing: 1.5px !important;
  color: #8b5a2b !important;
  text-transform: uppercase !important;
  display: block !important;
  margin-bottom: 2px !important;
}

#page-wwu .mob-card-content h4 {
  font-family: 'Playfair Display', serif !important;
  font-size: 13.5px !important;
  color: #1a1a14 !important;
  margin: 0 0 3px 0 !important;
  font-weight: 700 !important;
}

#page-wwu .mob-card-content p {
  font-size: 10.5px !important;
  line-height: 1.4 !important;
  color: #555043 !important;
  margin: 0 0 6px 0 !important;
}

#page-wwu .mob-card-content .mob-btn {
  font-family: 'Inter', sans-serif !important;
  font-size: 9.5px !important;
  font-weight: 700 !important;
  color: #8b5a2b !important;
  text-transform: uppercase !important;
}


/* ============================================================
   SECTION 5: WE COLLABORATE ACROSS THE CHOCOLATE ECOSYSTEM
   ============================================================ */
#page-wwu .wwu-ecosystem-section {
  position: relative !important;
  padding: 44px 36px 46px !important;
  background: #faf6ee !important;
  border-top: 1px solid rgba(212, 175, 55, 0.22) !important;
  border-bottom: 1px solid rgba(212, 175, 55, 0.18) !important;
  overflow: hidden !important;
  text-align: center !important;
}

/* Botanical sketches on left and right borders matching design */
#page-wwu .wwu-eco-sketch-left,
#page-wwu .wwu-eco-sketch-right {
  position: absolute !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
  height: 100% !important;
  max-height: 240px !important;
  width: auto !important;
  object-fit: contain !important;
  opacity: 0.2 !important;
  mix-blend-mode: multiply !important;
  pointer-events: none !important;
  z-index: 1 !important;
  filter: contrast(1.1) brightness(1.02) sepia(0.1) !important;
}

#page-wwu .wwu-eco-sketch-left {
  left: -20px !important;
}

#page-wwu .wwu-eco-sketch-right {
  right: -20px !important;
  transform: translateY(-50%) scaleX(-1) !important;
}

#page-wwu .wwu-ecosystem-inner {
  max-width: 1200px !important;
  margin: 0 auto !important;
  position: relative !important;
  z-index: 2 !important;
}

#page-wwu .wwu-ecosystem-heading {
  font-family: 'Playfair Display', serif !important;
  font-size: clamp(14px, 1.4vw, 17px) !important;
  font-weight: 700 !important;
  letter-spacing: 2.2px !important;
  color: #1a1712 !important;
  text-transform: uppercase !important;
  margin: 0 0 24px 0 !important;
}

/* 7-Column Box Grid with outer border and inner dividers */
#page-wwu .wwu-ecosystem-grid {
  display: grid !important;
  grid-template-columns: repeat(7, 1fr) !important;
  background: rgba(255, 255, 255, 0.4) !important;
  border: 1px solid rgba(184, 134, 11, 0.28) !important;
  border-radius: 4px !important;
  overflow: hidden !important;
  box-shadow: 0 2px 10px rgba(100, 70, 20, 0.03) !important;
}

#page-wwu .wwu-eco-card {
  padding: 22px 10px 18px !important;
  border-right: 1px solid rgba(184, 134, 11, 0.28) !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  justify-content: center !important;
  text-align: center !important;
  cursor: pointer !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  background: transparent !important;
}

#page-wwu .wwu-eco-card:last-child {
  border-right: none !important;
}

#page-wwu .wwu-eco-card:hover {
  background: rgba(255, 255, 255, 0.92) !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 16px rgba(139, 90, 43, 0.08) !important;
}

#page-wwu .wwu-eco-icon {
  width: 44px !important;
  height: 44px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  margin-bottom: 12px !important;
  color: #3b352b !important;
  transition: all 0.3s ease !important;
}

#page-wwu .wwu-eco-icon svg {
  width: 32px !important;
  height: 32px !important;
  stroke: #3b352b !important;
  transition: all 0.3s ease !important;
}

#page-wwu .wwu-eco-card:hover .wwu-eco-icon svg {
  stroke: #b8860b !important;
  transform: scale(1.08) !important;
}

#page-wwu .wwu-eco-label {
  font-family: 'Inter', sans-serif !important;
  font-size: 10px !important;
  font-weight: 800 !important;
  letter-spacing: 0.8px !important;
  color: #1a1712 !important;
  text-transform: uppercase !important;
  line-height: 1.35 !important;
  display: block !important;
  transition: color 0.3s ease !important;
}

#page-wwu .wwu-eco-card:hover .wwu-eco-label {
  color: #8b5a2b !important;
}

#page-wwu .wwu-ecosystem-footer-note {
  font-family: 'Inter', sans-serif !important;
  font-size: 12.5px !important;
  color: #5a5244 !important;
  margin: 18px 0 0 0 !important;
  font-weight: 400 !important;
  letter-spacing: 0.2px !important;
}



/* ============================================================
   CONSULTATION MODAL POPUP (Ultra-Luxury Dialog)
   ============================================================ */
#page-wwu .wwu-modal-backdrop {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  background: rgba(5, 14, 9, 0.84) !important;
  backdrop-filter: blur(16px) !important;
  -webkit-backdrop-filter: blur(16px) !important;
  z-index: 999999 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 24px !important;
  opacity: 0 !important;
  visibility: hidden !important;
  pointer-events: none !important;
  transition: opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.35s !important;
  box-sizing: border-box !important;
}

#page-wwu .wwu-modal-backdrop.is-open {
  opacity: 1 !important;
  visibility: visible !important;
  pointer-events: auto !important;
}

#page-wwu .wwu-modal-dialog {
  position: relative !important;
  width: 100% !important;
  max-width: 760px !important;
  max-height: 90vh !important;
  overflow-y: auto !important;
  background: #0a1a11 !important;
  border: 1.5px solid rgba(212, 175, 55, 0.35) !important;
  border-radius: 20px !important;
  padding: 44px 40px 40px !important;
  box-shadow: 
    0 30px 80px rgba(0, 0, 0, 0.8),
    0 0 50px rgba(212, 175, 55, 0.15) !important;
  transform: scale(0.92) translateY(24px) !important;
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

#page-wwu .wwu-modal-backdrop.is-open .wwu-modal-dialog {
  transform: scale(1) translateY(0) !important;
}

/* Modal Close Button */
#page-wwu .wwu-modal-close {
  position: absolute !important;
  top: 18px !important;
  right: 18px !important;
  width: 36px !important;
  height: 36px !important;
  border-radius: 50% !important;
  background: rgba(255, 255, 255, 0.06) !important;
  border: 1px solid rgba(212, 175, 55, 0.25) !important;
  color: #d4af37 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  cursor: pointer !important;
  transition: all 0.25s ease !important;
  z-index: 10 !important;
  outline: none !important;
}

#page-wwu .wwu-modal-close:hover {
  background: #d4af37 !important;
  color: #0a1a11 !important;
  transform: rotate(90deg) scale(1.08) !important;
  box-shadow: 0 0 15px rgba(212, 175, 55, 0.6) !important;
}

#page-wwu .wwu-modal-close svg {
  width: 18px !important;
  height: 18px !important;
  stroke: currentColor !important;
}

#page-wwu .wwu-form-card {
  max-width: 100% !important;
  margin: 0 auto !important;
  background: transparent !important;
  border: none !important;
  padding: 0 !important;
  box-shadow: none !important;
}
#page-wwu .wwu-form-card .wwu-tag {
  text-align: center !important;
  margin-bottom: 8px !important;
}
#page-wwu .wwu-form-card h2 {
  text-align: center !important;
  font-size: clamp(26px, 3vw, 34px) !important;
  margin-bottom: 8px !important;
  color: #ffffff !important;
}
#page-wwu .wwu-form-subtitle {
  text-align: center !important;
  font-size: 13.5px !important;
  color: #a09882 !important;
  font-weight: 400 !important;
  max-width: 520px !important;
  margin: 0 auto 28px !important;
  line-height: 1.6 !important;
}
#page-wwu .wwu-form-card label {
  display: block !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 10.5px !important;
  font-weight: 700 !important;
  letter-spacing: 1.5px !important;
  text-transform: uppercase !important;
  color: #d4af37 !important;
  margin-bottom: 6px !important;
}
#page-wwu .wwu-form-card input,
#page-wwu .wwu-form-card select,
#page-wwu .wwu-form-card textarea {
  width: 100% !important;
  background: rgba(255,255,255,0.05) !important;
  border: 1px solid rgba(212,175,55,0.22) !important;
  color: #f5efe1 !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 13.5px !important;
  border-radius: 8px !important;
  padding: 11px 14px !important;
  outline: none !important;
  transition: border-color 0.3s ease !important;
}
#page-wwu .wwu-form-card input::placeholder,
#page-wwu .wwu-form-card textarea::placeholder {
  color: rgba(160,152,130,0.6) !important;
}
#page-wwu .wwu-form-card input:focus,
#page-wwu .wwu-form-card select:focus,
#page-wwu .wwu-form-card textarea:focus {
  border-color: #d4af37 !important;
  box-shadow: 0 0 12px rgba(212, 175, 55, 0.25) !important;
}
#page-wwu .wwu-form-card select option {
  background: #0c1a11 !important;
  color: #f5efe1 !important;
}
#page-wwu .form-grid {
  display: grid !important;
  gap: 18px !important;
}
#page-wwu .form-row {
  display: grid !important;
  grid-template-columns: 1fr 1fr !important;
  gap: 18px !important;
}
#page-wwu .form-submit-btn {
  display: block !important;
  width: 100% !important;
  max-width: 320px !important;
  margin: 10px auto 0 !important;
  padding: 14px 36px !important;
  background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%) !important;
  color: #0c1a11 !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 13.5px !important;
  font-weight: 700 !important;
  letter-spacing: 0.5px !important;
  border: none !important;
  border-radius: 8px !important;
  cursor: pointer !important;
  transition: all 0.3s ease !important;
}
#page-wwu .form-submit-btn:hover {
  box-shadow: 0 8px 30px rgba(212,175,55,0.35) !important;
  transform: translateY(-2px) !important;
}
/* Alert boxes */
#page-wwu .alert-ok {
  background: rgba(212,175,55,0.12) !important;
  border: 1px solid #d4af37 !important;
  color: #f5efe1 !important;
  padding: 16px 20px !important;
  border-radius: 10px !important;
  text-align: center !important;
  font-size: 14px !important;
  font-weight: 600 !important;
  margin-bottom: 20px !important;
}
#page-wwu .alert-err {
  background: rgba(255,107,107,0.12) !important;
  border: 1px solid #ff6b6b !important;
  color: #ff6b6b !important;
  padding: 14px 18px !important;
  border-radius: 10px !important;
  text-align: center !important;
  font-size: 13.5px !important;
  font-weight: 600 !important;
  margin-bottom: 20px !important;
}


/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 1024px) {
  #page-wwu .wwu-hero-inner {
    grid-template-columns: 1fr !important;
    text-align: center !important;
    gap: 45px !important;
  }
  #page-wwu .wwu-hero-text {
    max-width: 100% !important;
    align-items: center !important;
  }
  #page-wwu .wwu-hero-tag-pill {
    align-self: center !important;
  }
  #page-wwu .wwu-hero-sublead {
    text-align: center !important;
    margin-left: auto !important;
    margin-right: auto !important;
  }
  #page-wwu .wwu-hero-pillars-grid {
    max-width: 580px !important;
    margin-left: auto !important;
    margin-right: auto !important;
    text-align: left !important;
  }
  #page-wwu .wwu-hero-actions {
    justify-content: center !important;
  }
  #page-wwu .wwu-hero-spec-strip {
    justify-content: center !important;
  }

  #page-wwu .wwu-collab-top-row {
    grid-template-columns: 1fr !important;
    gap: 28px !important;
  }
  #page-wwu .wwu-collab-intro {
    text-align: center !important;
    align-items: center !important;
  }
  #page-wwu .wwu-process-steps {
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 18px 12px !important;
  }
  #page-wwu .wwu-process-line {
    display: none !important;
  }
  #page-wwu .wwu-help-grid {
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 16px 0 !important;
  }
  #page-wwu .wwu-help-col:nth-child(3n) {
    border-right: none !important;
  }
  #page-wwu .wwu-help-col:nth-child(-n+3) {
    border-bottom: 1px solid rgba(184, 134, 11, 0.18) !important;
    padding-bottom: 14px !important;
  }

  #page-wwu .wwu-begin-grid {
    grid-template-columns: 1fr 1fr !important;
  }
  #page-wwu .wwu-areas-inner {
    grid-template-columns: 1fr !important;
    gap: 50px !important;
  }
}

@media (max-width: 880px) {
  #page-wwu .wwu-diagram-stage {
    display: none !important;
  }
  #page-wwu .wwu-mobile-areas-flow {
    display: flex !important;
  }
}

@media (max-width: 680px) {
  #page-wwu .wwu-hero {
    padding: 100px 20px 60px !important;
    min-height: auto !important;
  }
  #page-wwu .wwu-hero-pillars-grid {
    grid-template-columns: 1fr !important;
    width: 100% !important;
  }
  #page-wwu .wwu-hero-actions {
    flex-direction: column !important;
    width: 100% !important;
  }
  #page-wwu .wwu-hero-btn-gold,
  #page-wwu .wwu-hero-btn-outline {
    width: 100% !important;
    justify-content: center !important;
  }
  #page-wwu .wwu-hero-spec-strip {
    flex-direction: column !important;
    gap: 6px !important;
    align-items: center !important;
  }
  #page-wwu .wwu-hero-spec-strip .spec-sep {
    display: none !important;
  }
  #page-wwu .wwu-pod-constellation {
    width: min(320px, 90vw) !important;
    height: min(330px, 90vw) !important;
    max-width: 100% !important;
  }
  #page-wwu .wwu-pod-artwork {
    width: min(120px, 35vw) !important;
  }
  #page-wwu .wwu-satellite-pill {
    font-size: 8px !important;
    padding: 4px 8px !important;
    letter-spacing: 1px !important;
  }
  #page-wwu .wwu-collab-section {
    padding: 34px 18px 36px !important;
  }
  #page-wwu .wwu-process-steps {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 18px 10px !important;
  }
  #page-wwu .wwu-help-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 14px 0 !important;
  }
  #page-wwu .wwu-help-col {
    padding: 6px 10px !important;
  }
  #page-wwu .wwu-help-col:nth-child(2n) {
    border-right: none !important;
  }
  #page-wwu .wwu-help-col:nth-child(2n+1) {
    border-right: 1px solid rgba(184, 134, 11, 0.18) !important;
  }
  #page-wwu .wwu-help-col:nth-child(-n+4) {
    border-bottom: 1px solid rgba(184, 134, 11, 0.18) !important;
    padding-bottom: 12px !important;
  }
  #page-wwu .wwu-heading-rule .rule-title {
    font-size: 18px !important;
  }
  #page-wwu .wwu-heading-rule {
    gap: 12px !important;
  }
  #page-wwu .wwu-begin-grid {
    grid-template-columns: 1fr !important;
  }

  #page-wwu .form-row {
    grid-template-columns: 1fr !important;
  }
  #page-wwu .wwu-form-card {
    padding: 36px 24px !important;
  }
  #page-wwu .wwu-ch-pillars {
    grid-template-columns: 1fr !important;
  }
  #page-wwu .wwu-collab-section,
  #page-wwu .wwu-ecosystem-section {
    padding: 34px 18px !important;
  }
  #page-wwu .wwu-begin,
  #page-wwu .wwu-areas,
  #page-wwu .wwu-form-sec {
    padding: 60px 20px !important;
  }
}
</style>

<!-- ================================================================
     WORK WITH US — PAGE CONTENT
     ================================================================ -->
<div id="page-wwu" class="page active" style="padding-top:76px;">

  <!-- ====== 1. HERO ====== -->
  <section class="wwu-hero">
    <!-- Full-bleed background video -->
    <video class="wwu-hero-bg-video" id="wwuHeroVideo" autoplay loop muted playsinline preload="auto" poster="assets/workwithusvid_thumb.jpg">
      <source src="assets/workwithusvid.mp4" type="video/mp4">
      Your browser does not support HTML5 video.
    </video>

    <!-- Luxury Dark Gradient Vignette for perfect text contrast -->
    <div class="wwu-hero-video-overlay" aria-hidden="true"></div>

    <!-- Decorative celestial coordinate web lines -->
    <div class="wwu-hero-bg-lines">
      <svg viewBox="0 0 700 700" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="350" cy="350" r="100" stroke="#d4af37" stroke-width="0.5" opacity="0.35"/>
        <circle cx="350" cy="350" r="200" stroke="#d4af37" stroke-width="0.5" opacity="0.25" stroke-dasharray="3 6"/>
        <circle cx="350" cy="350" r="310" stroke="#d4af37" stroke-width="0.3" opacity="0.18"/>
        <line x1="350" y1="350" x2="350" y2="40" stroke="#d4af37" stroke-width="0.4" opacity="0.25"/>
        <line x1="350" y1="350" x2="620" y2="200" stroke="#d4af37" stroke-width="0.4" opacity="0.25"/>
        <line x1="350" y1="350" x2="630" y2="450" stroke="#d4af37" stroke-width="0.4" opacity="0.25"/>
        <line x1="350" y1="350" x2="440" y2="650" stroke="#d4af37" stroke-width="0.3" opacity="0.2"/>
        <line x1="350" y1="350" x2="180" y2="620" stroke="#d4af37" stroke-width="0.3" opacity="0.2"/>
        <line x1="350" y1="350" x2="70" y2="400" stroke="#d4af37" stroke-width="0.3" opacity="0.15"/>
        <line x1="350" y1="350" x2="120" y2="160" stroke="#d4af37" stroke-width="0.3" opacity="0.15"/>
      </svg>
    </div>

    <div class="wwu-hero-inner">

      <!-- Editorial & Conversion Content -->
      <div class="wwu-hero-text">
        <div class="wwu-hero-tag-pill">
          <span class="pill-dot"></span>
          <span>Work With RT Chocos</span>
        </div>

        <h1>Bring us<br><span class="hl-hero-gold">the question.</span></h1>

        <!-- 2x2 Interactive Question Pillars Grid -->
        <div class="wwu-hero-pillars-grid">
          <div class="hero-pillar-card" onclick="selectCollabArea('R&D & Product Innovation')">
            <span class="card-icon-tag">✦ ARCHITECTURE</span>
            <strong>A product to rethink.</strong>
            <p>Formulation, sensory profiling &amp; market repositioning.</p>
          </div>
          <div class="hero-pillar-card" onclick="selectCollabArea('Ingredients & Application Lab')">
            <span class="card-icon-tag">✦ INGREDIENTS</span>
            <strong>An ingredient to explore.</strong>
            <p>Novel fats, clean-label sweeteners &amp; botanicals.</p>
          </div>
          <div class="hero-pillar-card" onclick="selectCollabArea('Chocolate Problem Solving')">
            <span class="card-icon-tag">✦ STABILITY</span>
            <strong>A problem to solve.</strong>
            <p>Fat bloom, viscosity drift &amp; shelf-life stabilization.</p>
          </div>
          <div class="hero-pillar-card" onclick="selectCollabArea('R&D & Product Innovation')">
            <span class="card-icon-tag">✦ INNOVATION</span>
            <strong>An idea not tried yet.</strong>
            <p>Translating blue-sky concepts into pilot lab prototypes.</p>
          </div>
        </div>

        <!-- Action Buttons & Quick Specs -->
        <div class="wwu-hero-actions-wrap">
          <div class="wwu-hero-actions">
            <button type="button" onclick="openConsultationModal()" class="wwu-hero-btn-gold">
              <span>DISCUSS YOUR IDEA</span>
              <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
            <a href="#collaboration-areas" class="wwu-hero-btn-outline">
              <span>EXPLORE 5 AREAS</span>
              <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
            </a>
          </div>
        </div>
      </div>

    </div>

    <!-- Floating Video Controls in Bottom Right of Hero -->
    <div class="wwu-hero-video-bar">
      <div class="wwu-video-badge">
        <span class="radar-beacon"></span>
        <span>Chocolate R&amp;D Lab</span>
      </div>
      <div class="wwu-video-ctrls">
        <button type="button" class="wwu-video-ctrl-btn" id="wwuVidPlayBtn" onclick="toggleWwuVideoPlay()" title="Play / Pause" aria-label="Toggle Playback">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" id="wwuVidPauseIcon"><rect x="6" y="4" width="4" height="16" rx="1"></rect><rect x="14" y="4" width="4" height="16" rx="1"></rect></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" id="wwuVidPlayIcon" style="display:none;"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
        </button>
        <button type="button" class="wwu-video-ctrl-btn" id="wwuVidMuteBtn" onclick="toggleWwuVideoSound()" title="Mute / Unmute" aria-label="Toggle Audio">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="wwuVidMutedIcon"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5" fill="currentColor"></polygon><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="wwuVidSoundIcon" style="display:none;"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5" fill="currentColor"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
        </button>
      </div>
    </div>
  </section>


  <!-- ====== 2. HOW WE COLLABORATE & WHAT WE HELP WITH ====== -->
  <section class="wwu-collab-section" id="how-we-collaborate">
    <div class="wwu-collab-container">
      
      <!-- TOP PART: HOW WE COLLABORATE PROCESS JOURNEY -->
      <div class="wwu-collab-top-row">
        <!-- Left Side: Title & Statement -->
        <div class="wwu-collab-intro">
          <span class="wwu-collab-tag">HOW WE COLLABORATE</span>
          <h2 class="wwu-collab-heading">A thoughtful journey<br>from question to impact.</h2>
          <div class="wwu-collab-gold-dash"></div>
          <p class="wwu-collab-subtext">Every collaboration is unique, but our approach is always rooted in curiosity, rigour and clarity.</p>
        </div>

        <!-- Right Side: 6-Step Horizontal Process Flow -->
        <div class="wwu-process-track-wrap">
          <div class="wwu-process-line"></div>
          <div class="wwu-process-steps">
            
            <!-- Step 01 -->
            <div class="wwu-step-node">
              <span class="wwu-step-num">01</span>
              <div class="wwu-step-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
              </div>
              <h4 class="wwu-step-title">CONNECT</h4>
              <p class="wwu-step-desc">We understand your challenge, objectives and context.</p>
            </div>

            <!-- Step 02 -->
            <div class="wwu-step-node">
              <span class="wwu-step-num">02</span>
              <div class="wwu-step-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
              </div>
              <h4 class="wwu-step-title">EXPLORE</h4>
              <p class="wwu-step-desc">We dive deep into ingredients, data, formulations and constraints.</p>
            </div>

            <!-- Step 03 -->
            <div class="wwu-step-node">
              <span class="wwu-step-num">03</span>
              <div class="wwu-step-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/><line x1="8.5" y1="2" x2="15.5" y2="2"/>
                </svg>
              </div>
              <h4 class="wwu-step-title">EXPERIMENT</h4>
              <p class="wwu-step-desc">We test ideas, challenge assumptions and create possibilities.</p>
            </div>

            <!-- Step 04 -->
            <div class="wwu-step-node">
              <span class="wwu-step-num">04</span>
              <div class="wwu-step-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/>
                  <line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/>
                  <line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/>
                  <line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/>
                </svg>
              </div>
              <h4 class="wwu-step-title">REFINE</h4>
              <p class="wwu-step-desc">We analyse results, refine approaches and improve what matters.</p>
            </div>

            <!-- Step 05 -->
            <div class="wwu-step-node">
              <span class="wwu-step-num">05</span>
              <div class="wwu-step-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                  <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                  <path d="m9 14 2 2 4-4"/>
                </svg>
              </div>
              <h4 class="wwu-step-title">APPLY</h4>
              <p class="wwu-step-desc">We translate learning into practical, scalable solutions.</p>
            </div>

            <!-- Step 06 -->
            <div class="wwu-step-node">
              <span class="wwu-step-num">06</span>
              <div class="wwu-step-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                </svg>
              </div>
              <h4 class="wwu-step-title">EVOLVE</h4>
              <p class="wwu-step-desc">We continue learning together and create long-term impact.</p>
            </div>

          </div>
        </div>
      </div>

      <!-- MIDDLE DIVIDER: WHAT WE HELP WITH -->
      <div class="wwu-help-divider-wrap">
        <span class="wwu-help-rule-line"></span>
        <span class="wwu-help-tag">WHAT WE HELP WITH</span>
        <span class="wwu-help-rule-line"></span>
      </div>

      <!-- BOTTOM PART: 6 CAPABILITY CARDS WITH SLEEK VERTICAL DIVIDERS -->
      <div class="wwu-help-grid">
        
        <!-- Cap 1: Product Development -->
        <div class="wwu-help-col">
          <div class="wwu-help-icon">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 4C11 8 7 13.5 7 19c0 5 4 9 9 9s9-4 9-9c0-5.5-4-11-9-15z"/>
              <path d="M16 4c-2.5 4-4 8.5-4 15 0 4.5 1.5 7.5 4 9"/>
              <path d="M16 4c2.5 4 4 8.5 4 15 0 4.5-1.5 7.5-4 9"/>
              <path d="M16 4V2"/>
            </svg>
          </div>
          <h4>PRODUCT DEVELOPMENT</h4>
          <p>Creating innovative chocolate products that are delightful, differentiated and market-ready.</p>
        </div>

        <!-- Cap 2: Formulation & Ingredient Innovation -->
        <div class="wwu-help-col">
          <div class="wwu-help-icon">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <rect x="5" y="6" width="10" height="9" rx="1.5"/>
              <rect x="17" y="6" width="10" height="9" rx="1.5"/>
              <rect x="5" y="17" width="10" height="9" rx="1.5"/>
              <rect x="17" y="17" width="10" height="9" rx="1.5"/>
            </svg>
          </div>
          <h4>FORMULATION &amp; INGREDIENT INNOVATION</h4>
          <p>Better ingredients. Better combinations. Better outcomes.</p>
        </div>

        <!-- Cap 3: Technology & Process -->
        <div class="wwu-help-col">
          <div class="wwu-help-icon">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="16" cy="16" r="4"/>
              <path d="M16 3v3M16 26v3M3 16h3M26 16h3M6.8 6.8l2.1 2.1M23.1 23.1l2.1 2.1M6.8 25.2l2.1-2.1M23.1 8.9l2.1-2.1"/>
            </svg>
          </div>
          <h4>TECHNOLOGY &amp; PROCESS</h4>
          <p>Improving processes for consistency, scalability and efficiency.</p>
        </div>

        <!-- Cap 4: Quality & Troubleshooting -->
        <div class="wwu-help-col">
          <div class="wwu-help-icon">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 26V18M13 26V12M20 26V15M27 26V8"/>
              <path d="M6 15l7-6 7 4 7-8"/>
              <path d="M23 5h4v4"/>
            </svg>
          </div>
          <h4>QUALITY &amp; TROUBLESHOOTING</h4>
          <p>Solving challenges at every stage to ensure quality and stability.</p>
        </div>

        <!-- Cap 5: Research & Experimentation -->
        <div class="wwu-help-col">
          <div class="wwu-help-icon">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M8 4h11l7 7v17H8z"/>
              <polyline points="19 4 19 11 26 11"/>
              <line x1="12" y1="16" x2="20" y2="16"/>
              <line x1="12" y1="20" x2="20" y2="20"/>
              <line x1="12" y1="24" x2="16" y2="24"/>
            </svg>
          </div>
          <h4>RESEARCH &amp; EXPERIMENTATION</h4>
          <p>Evidence-led exploration to answer questions that matter.</p>
        </div>

        <!-- Cap 6: Knowledge & Training -->
        <div class="wwu-help-col">
          <div class="wwu-help-icon">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 4a9 9 0 0 0-9 9c0 3.2 1.7 6 4.3 7.5V23a2 2 0 0 0 2 2h5.4a2 2 0 0 0 2-2v-2.5C23.3 19 25 16.2 25 13a9 9 0 0 0-9-9z"/>
              <line x1="13" y1="28" x2="19" y2="28"/>
            </svg>
          </div>
          <h4>KNOWLEDGE &amp; TRAINING</h4>
          <p>Workshops, training and knowledge sessions for teams who want to grow.</p>
        </div>

      </div>

    </div>
  </section>


  <!-- ====== 3. WHERE SHOULD WE BEGIN ====== -->
  <section class="wwu-begin">
    <div class="wwu-begin-header">
      <!-- Title with horizontal rule lines -->
      <div class="wwu-heading-rule">
        <span class="rule-line"></span>
        <h2 class="rule-title">WHERE SHOULD WE BEGIN?</h2>
        <span class="rule-line"></span>
      </div>
      <p>Choose the starting point that best describes what you'd like to explore.</p>
    </div>

    <div class="wwu-begin-grid">
      <!-- Card 1 -->
      <div class="wwu-card">
        <div class="card-icon">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <h4>I Have a Problem</h4>
        <div class="card-desc">Something isn't working.</div>
        <div class="card-tags">
          <span>Bloom</span><span>Viscosity</span><span>Texture</span>
          <span>Shelf life</span><span>Processing</span><span>Formulation</span>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="wwu-card">
        <div class="card-icon">
          <svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.5 6-.3.2-.5.5-.5.9V17H9v-1.1c0-.4-.2-.7-.5-.9C6.3 13.7 5 11.5 5 9a7 7 0 0 1 7-7z"/><line x1="9" y1="21" x2="15" y2="21"/></svg>
        </div>
        <h4>I Have an Idea</h4>
        <div class="card-desc">Something doesn't exist yet.</div>
        <div class="card-tags">
          <span>New product</span><span>New format</span>
          <span>Flavour</span><span>Concept</span><span>Experience</span>
        </div>
      </div>

      <!-- Card 3 -->
      <div class="wwu-card">
        <div class="card-icon">
          <svg viewBox="0 0 24 24"><path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/><line x1="8.5" y1="2" x2="15.5" y2="2"/></svg>
        </div>
        <h4>I Have an Ingredient</h4>
        <div class="card-desc">Something worth testing.</div>
        <div class="card-tags">
          <span>Ingredient</span><span>Inclusion</span><span>Cocoa component</span>
          <span>Functional ingredient</span><span>Raw material</span>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="wwu-card">
        <div class="card-icon">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        </div>
        <h4>I Have a Question</h4>
        <div class="card-desc">Something I want to understand.</div>
        <div class="card-tags">
          <span>Research</span><span>Education</span><span>Experimentation</span>
          <span>Industry knowledge</span><span>R&D thinking</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ====== 4. FIVE COLLABORATION AREAS ====== -->
  <section class="wwu-areas" id="collaboration-areas">
    <div class="wwu-areas-inner">

      <!-- Left Column: Vision & Command -->
      <div class="wwu-areas-left">
        <div class="wwu-tag-pill">
          <span class="pill-dot"></span>
          <span>COLLABORATION FRAMEWORK</span>
        </div>
        <h2>Different Needs.<br><span class="hl-gold">One Collaborative</span><br>Mindset.</h2>
        <p class="wwu-areas-desc">
          Five ways we collaborate with you.<br>
          Many outcomes we can create together.
        </p>
        
        

        <button type="button" onclick="openConsultationModal()" class="wwu-btn-areas-cta">
          <span>SEE HOW WE COLLABORATE</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
      </div>

      <!-- Desktop Radial Orbital Hub (Rendered on >= 880px) -->
      <div class="wwu-diagram-stage">
        
        <!-- SVG Orbit Rings, Radial Glow Rays and Connection Lines -->
        <svg class="wwu-orbital-svg" viewBox="0 0 680 460" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="spokeGradGold" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#d4af37" stop-opacity="0.9"/>
              <stop offset="100%" stop-color="#8b5a2b" stop-opacity="0.35"/>
            </linearGradient>
            <radialGradient id="dockGlow" cx="50%" cy="50%" r="50%">
              <stop offset="0%" stop-color="#d4af37" stop-opacity="0.8"/>
              <stop offset="100%" stop-color="#d4af37" stop-opacity="0"/>
            </radialGradient>
          </defs>

          <!-- Outer faint guide ring -->
          <circle cx="340" cy="230" r="205" stroke="#d4af37" stroke-width="0.8" stroke-opacity="0.16" stroke-dasharray="4 6"/>
          
          <!-- Main Orbital Track (Rotating) -->
          <g class="wwu-orbit-dash">
            <circle cx="340" cy="230" r="148" stroke="#8b5a2b" stroke-width="1.2" stroke-opacity="0.22" stroke-dasharray="5 5"/>
          </g>

          <!-- Inner pulse halo ring -->
          <circle cx="340" cy="230" r="60" stroke="#d4af37" stroke-width="1" stroke-opacity="0.35" stroke-dasharray="2 4"/>

          <!-- 5 Radial Connector Spokes with Gold Gradients -->
          <!-- Top: to Card 1 (Create) -->
          <line id="spoke-1" x1="340" y1="181" x2="340" y2="98" stroke="url(#spokeGradGold)" stroke-width="1.5" stroke-dasharray="3 3"/>
          <circle cx="340" cy="98" r="7" fill="url(#dockGlow)"/>
          <circle cx="340" cy="98" r="3" fill="#d4af37"/>

          <!-- Top-Right: to Card 2 (Solve) -->
          <line id="spoke-2" x1="380" y1="201" x2="484" y2="117" stroke="url(#spokeGradGold)" stroke-width="1.5" stroke-dasharray="3 3"/>
          <circle cx="484" cy="117" r="7" fill="url(#dockGlow)"/>
          <circle cx="484" cy="117" r="3" fill="#d4af37"/>

          <!-- Bottom-Right: to Card 3 (Explore) -->
          <line id="spoke-3" x1="380" y1="259" x2="469" y2="365" stroke="url(#spokeGradGold)" stroke-width="1.5" stroke-dasharray="3 3"/>
          <circle cx="469" cy="365" r="7" fill="url(#dockGlow)"/>
          <circle cx="469" cy="365" r="3" fill="#d4af37"/>

          <!-- Bottom-Left: to Card 4 (Connect) -->
          <line id="spoke-4" x1="300" y1="259" x2="211" y2="365" stroke="url(#spokeGradGold)" stroke-width="1.5" stroke-dasharray="3 3"/>
          <circle cx="211" cy="365" r="7" fill="url(#dockGlow)"/>
          <circle cx="211" cy="365" r="3" fill="#d4af37"/>

          <!-- Top-Left: to Card 5 (Learn) -->
          <line id="spoke-5" x1="300" y1="201" x2="196" y2="117" stroke="url(#spokeGradGold)" stroke-width="1.5" stroke-dasharray="3 3"/>
          <circle cx="196" cy="117" r="7" fill="url(#dockGlow)"/>
          <circle cx="196" cy="117" r="3" fill="#d4af37"/>
        </svg>

        <!-- Center Core: THE QUESTION (Interactive Reactive Nucleus) -->
        <div class="wwu-diagram-center" id="orbital-nucleus">
          <div class="dc-pulse-ring"></div>
          
          <span class="dc-title" id="nucleus-title">THE<br>QUESTION</span>
          <span class="dc-sub" id="nucleus-sub">Curiosity starts here</span>
        </div>

        <!-- Node 1: CREATE -->
        <div class="wwu-area-card card-create" 
             onmouseenter="highlightHubNode(1, '01 • CREATE', 'R&amp;D LAB', 'Custom prototypes & trials')"
             onmouseleave="resetHubNode()"
             onclick="selectCollabArea('R&D & Product Innovation')">
          <div class="card-head">
            <div class="area-icon">
              <svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.5 6V17H9v-2c-2.2-1.3-3.5-3.5-3.5-6a7 7 0 0 1 7-7z"/><line x1="9" y1="21" x2="15" y2="21"/></svg>
            </div>
            <div>
              <span class="area-label">01 • CREATE</span>
            </div>
          </div>
          <h4>R&amp;D &amp; Product Innovation</h4>
          <p>Concept to prototypes, formulation, fat matrices, flavour design &amp; sensory trials.</p>
          <span class="card-action">Select Area →</span>
        </div>

        <!-- Node 2: SOLVE -->
        <div class="wwu-area-card card-solve" 
             onmouseenter="highlightHubNode(2, '02 • SOLVE', 'DEFECT LAB', 'Root cause diagnostics')"
             onmouseleave="resetHubNode()"
             onclick="selectCollabArea('Chocolate Problem Solving')">
          <div class="card-head">
            <div class="area-icon">
              <svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            </div>
            <div>
              <span class="area-label">02 • SOLVE</span>
            </div>
          </div>
          <h4>Chocolate Problem Solving</h4>
          <p>Root cause investigation for bloom, viscosity, tempering &amp; shelf-life defects.</p>
          <span class="card-action">Select Area →</span>
        </div>

        <!-- Node 3: EXPLORE -->
        <div class="wwu-area-card card-explore" 
             onmouseenter="highlightHubNode(3, '03 • EXPLORE', 'NOVEL LAB', 'Functional ingredients')"
             onmouseleave="resetHubNode()"
             onclick="selectCollabArea('Ingredients & Application Lab')">
          <div class="card-head">
            <div class="area-icon">
              <svg viewBox="0 0 24 24"><path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/><line x1="8.5" y1="2" x2="15.5" y2="2"/></svg>
            </div>
            <div>
              <span class="area-label">03 • EXPLORE</span>
            </div>
          </div>
          <h4>Ingredients &amp; Application Lab</h4>
          <p>Testing novel inclusions, fats, alternative sugars &amp; functional cacao botanicals.</p>
          <span class="card-action">Select Area →</span>
        </div>

        <!-- Node 4: CONNECT -->
        <div class="wwu-area-card card-connect" 
             onmouseenter="highlightHubNode(4, '04 • CONNECT', 'COLLAB HUB', 'Strategic partnerships')"
             onmouseleave="resetHubNode()"
             onclick="selectCollabArea('Knowledge & Industry Collaboration')">
          <div class="card-head">
            <div class="area-icon">
              <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
              <span class="area-label">04 • CONNECT</span>
            </div>
          </div>
          <h4>Knowledge &amp; Industry Collabs</h4>
          <p>Joint research, roundtables, technical publishing &amp; strategic brand partnerships.</p>
          <span class="card-action">Select Area →</span>
        </div>

        <!-- Node 5: LEARN -->
        <div class="wwu-area-card card-learn" 
             onmouseenter="highlightHubNode(5, '05 • LEARN', 'ACADEMY', 'Formulation & rheology')"
             onmouseleave="resetHubNode()"
             onclick="selectCollabArea('Education & Masterclasses')">
          <div class="card-head">
            <div class="area-icon">
              <svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
            <div>
              <span class="area-label">05 • LEARN</span>
            </div>
          </div>
          <h4>Education &amp; Masterclasses</h4>
          <p>Deep-dive masterclasses on chocolate rheology, crystallization &amp; formulation.</p>
          <span class="card-action">Select Area →</span>
        </div>

      </div>

      <!-- Mobile Flow & Stepper Architecture (Rendered on screens < 880px) -->
      <div class="wwu-mobile-areas-flow">
        <!-- Mobile Center Badge -->
        <div class="wwu-mob-center-badge">
          <span class="mob-badge-tag">THE NUCLEUS</span>
          <h3 class="mob-badge-title">THE QUESTION</h3>
          <span class="mob-badge-desc">Curiosity is the starting point.</span>
        </div>

        <!-- Mobile Stepper Cards -->
        <div class="wwu-mob-cards-list">
          <div class="wwu-mob-card" onclick="selectCollabArea('R&D & Product Innovation')">
            <div class="mob-card-side">
              <div class="mob-icon"><svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.5 6V17H9v-2c-2.2-1.3-3.5-3.5-3.5-6a7 7 0 0 1 7-7z"/><line x1="9" y1="21" x2="15" y2="21"/></svg></div>
            </div>
            <div class="mob-card-content">
              <span class="mob-tag">01 • CREATE</span>
              <h4>R&amp;D &amp; Product Innovation</h4>
              <p>Concept to working prototypes, formulation, fat matrices, flavour design &amp; sensory trials.</p>
              <span class="mob-btn">Explore &amp; Inquire →</span>
            </div>
          </div>

          <div class="wwu-mob-card" onclick="selectCollabArea('Chocolate Problem Solving')">
            <div class="mob-card-side">
              <div class="mob-icon"><svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></div>
            </div>
            <div class="mob-card-content">
              <span class="mob-tag">02 • SOLVE</span>
              <h4>Chocolate Problem Solving</h4>
              <p>Root cause investigation for bloom, viscosity, tempering &amp; shelf-life defects.</p>
              <span class="mob-btn">Explore &amp; Inquire →</span>
            </div>
          </div>

          <div class="wwu-mob-card" onclick="selectCollabArea('Ingredients & Application Lab')">
            <div class="mob-card-side">
              <div class="mob-icon"><svg viewBox="0 0 24 24"><path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/><line x1="8.5" y1="2" x2="15.5" y2="2"/></svg></div>
            </div>
            <div class="mob-card-content">
              <span class="mob-tag">03 • EXPLORE</span>
              <h4>Ingredients &amp; Application Lab</h4>
              <p>Testing novel inclusions, fats, alternative sugars &amp; functional cacao botanicals.</p>
              <span class="mob-btn">Explore &amp; Inquire →</span>
            </div>
          </div>

          <div class="wwu-mob-card" onclick="selectCollabArea('Knowledge & Industry Collaboration')">
            <div class="mob-card-side">
              <div class="mob-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
            </div>
            <div class="mob-card-content">
              <span class="mob-tag">04 • CONNECT</span>
              <h4>Knowledge &amp; Industry Collabs</h4>
              <p>Joint research, roundtables, technical publishing &amp; strategic brand partnerships.</p>
              <span class="mob-btn">Explore &amp; Inquire →</span>
            </div>
          </div>

          <div class="wwu-mob-card" onclick="selectCollabArea('Education & Masterclasses')">
            <div class="mob-card-side">
              <div class="mob-icon"><svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
            </div>
            <div class="mob-card-content">
              <span class="mob-tag">05 • LEARN</span>
              <h4>Education &amp; Masterclasses</h4>
              <p>Deep-dive masterclasses on chocolate rheology, crystallization &amp; formulation.</p>
              <span class="mob-btn">Explore &amp; Inquire →</span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>


  <!-- ====== 5. WE COLLABORATE ACROSS THE CHOCOLATE ECOSYSTEM ====== -->
  <section class="wwu-ecosystem-section" id="who-we-work-with">
    <!-- Subtle Botanical Sketch Accents on Left & Right -->
    <img src="assets/cacao_botanical_sketch.jpg" alt="" class="wwu-eco-sketch-left" />
    <img src="assets/cacao_botanical_sketch.jpg" alt="" class="wwu-eco-sketch-right" />

    <div class="wwu-ecosystem-inner">
      <h2 class="wwu-ecosystem-heading">WE COLLABORATE ACROSS THE CHOCOLATE ECOSYSTEM</h2>

      <!-- 7-Column Enclosed Box Grid -->
      <div class="wwu-ecosystem-grid">
        
        <!-- 1. Brands & Manufacturers -->
        <div class="wwu-eco-card" onclick="openConsultationModal('Brands & Manufacturers')">
          <div class="wwu-eco-icon">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 26V13l6 4V11l7 5V7l8 6v13H5z"/>
              <path d="M9 26v-4M15 26v-4M21 26v-4"/>
            </svg>
          </div>
          <span class="wwu-eco-label">BRANDS &amp;<br>MANUFACTURERS</span>
        </div>

        <!-- 2. Ingredient Companies -->
        <div class="wwu-eco-card" onclick="openConsultationModal('Ingredient Companies')">
          <div class="wwu-eco-icon">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 26C6 26 8 13 18 7c7-4 8-1 8-1s3 1 0 8c-6 10-20 12-20 12z"/>
              <path d="M6 26c4-6 10-12 16-15"/>
            </svg>
          </div>
          <span class="wwu-eco-label">INGREDIENT<br>COMPANIES</span>
        </div>

        <!-- 3. R&D & Product Developers -->
        <div class="wwu-eco-card" onclick="openConsultationModal('R&D & Product Developers')">
          <div class="wwu-eco-icon">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 4v7L5.5 24A2 2 0 0 0 7.2 27h17.6a2 2 0 0 0 1.7-3L20 11V4"/>
              <line x1="10" y1="4" x2="22" y2="4"/>
              <path d="M9 20h14"/>
              <circle cx="16" cy="18" r="1.5"/>
              <path d="M22 6l3-2M23 10l3 1"/>
            </svg>
          </div>
          <span class="wwu-eco-label">R&amp;D &amp; PRODUCT<br>DEVELOPERS</span>
        </div>

        <!-- 4. Chocolatiers & Chefs -->
        <div class="wwu-eco-card" onclick="openConsultationModal('Chocolatiers & Chefs')">
          <div class="wwu-eco-icon">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M8 24l7-7"/>
              <path d="M13 15c-1.5-1.5-1-4.5 1-6.5s5-2.5 6.5-1 1 4.5-1 6.5-5 2.5-6.5 1z"/>
              <path d="M16 12c-0.8-0.8-0.5-2.5 0.5-3.5s2.7-1.3 3.5-0.5 0.5 2.5-0.5 3.5-2.7 1.3-3.5 0.5z"/>
              <path d="M6 26l3-3"/>
            </svg>
          </div>
          <span class="wwu-eco-label">CHOCOLATIERS &amp;<br>CHEFS</span>
        </div>

        <!-- 5. Startups & Founders -->
        <div class="wwu-eco-card" onclick="openConsultationModal('Startups & Founders')">
          <div class="wwu-eco-icon">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19.5 5.5C18 7 14 11 12.5 15.5l4 4c4.5-1.5 8.5-5.5 10-7 1.5-1.5 1-4-1-6s-4.5-2.5-6-1z"/>
              <path d="M12.5 15.5L7 17l4 4-1.5 5.5 5.5-1.5 4 4 1.5-5.5"/>
              <circle cx="19" cy="13" r="1.5"/>
            </svg>
          </div>
          <span class="wwu-eco-label">STARTUPS &amp;<br>FOUNDERS</span>
        </div>

        <!-- 6. Researchers & Academia -->
        <div class="wwu-eco-card" onclick="openConsultationModal('Researchers & Academia')">
          <div class="wwu-eco-icon">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 12l12-6 12 6-12 6-12-6z"/>
              <path d="M8 14.5v7c0 2 3.5 4.5 8 4.5s8-2.5 8-4.5v-7"/>
              <path d="M28 12v9"/>
            </svg>
          </div>
          <span class="wwu-eco-label">RESEARCHERS &amp;<br>ACADEMIA</span>
        </div>

        <!-- 7. Hotels & Horeca -->
        <div class="wwu-eco-card" onclick="openConsultationModal('Hotels & Horeca')">
          <div class="wwu-eco-icon">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 23h22"/>
              <path d="M7 23c0-6 4-11 9-11s9 5 9 11"/>
              <circle cx="16" cy="10" r="1.5"/>
              <line x1="4" y1="26" x2="28" y2="26"/>
            </svg>
          </div>
          <span class="wwu-eco-label">HOTELS &amp;<br>HORECA</span>
        </div>

      </div>

      <p class="wwu-ecosystem-footer-note">If your world touches chocolate, we can create value together.</p>
    </div>
  </section>

  <div id="consultation-modal" class="wwu-modal-backdrop" aria-hidden="true" onclick="handleModalBackdropClick(event)">
    <div class="wwu-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      
      <!-- Close Button -->
      <button class="wwu-modal-close" onclick="closeConsultationModal()" aria-label="Close Consultation Form">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>

      <div class="wwu-form-card">
        <span class="wwu-tag">GET STARTED</span>
        <h2 id="modal-title">Start a Conversation</h2>
        <p class="wwu-form-subtitle">Tell us about your challenge, idea, or question. We'll review it and get back to you with how we can help.</p>

        <?php if ($submissionSuccess): ?>
          <div class="alert-ok">✓ Thank you! Your inquiry has been received. We will get in touch with you within 24 hours.</div>
        <?php elseif (!empty($submissionError)): ?>
          <div class="alert-err"><?php echo htmlspecialchars($submissionError); ?></div>
        <?php endif; ?>

        <form method="POST" action="work-with-us.php" id="inquiry-form">
          <div class="form-grid">
            <div class="form-row">
              <div>
                <label>Your Full Name *</label>
                <input type="text" name="name" required placeholder="e.g. Aarti Sahni" />
              </div>
              <div>
                <label>Work Email Address *</label>
                <input type="email" name="email" required placeholder="name@company.com" />
              </div>
            </div>
            <div class="form-row">
              <div>
                <label>Phone / WhatsApp</label>
                <input type="text" name="phone" placeholder="+91 98765 43210" />
              </div>
              <div>
                <label>Company / Brand Name</label>
                <input type="text" name="company" placeholder="e.g. Artisan Cacao Ltd." />
              </div>
            </div>
            <div class="form-row">
              <div>
                <label>Primary Area of Interest</label>
                <select name="service" id="modal-service-select">
                  <option value="R&D & Product Innovation">🔬 R&D &amp; Product Innovation</option>
                  <option value="Chocolate Problem Solving">🧩 Chocolate Problem Solving</option>
                  <option value="Ingredients & Application Lab">⚗️ Ingredients &amp; Application Lab</option>
                  <option value="Education & Masterclasses">🎓 Education &amp; Masterclasses</option>
                  <option value="Knowledge & Industry Collaboration">🤝 Knowledge &amp; Industry Collaboration</option>
                  <option value="General Inquiry">✨ General Inquiry</option>
                </select>
              </div>
              <div>
                <label>Budget Range</label>
                <select name="budget">
                  <option value="₹50,000 - ₹1,50,000">₹50,000 – ₹1,50,000</option>
                  <option value="₹1,50,000 - ₹3,50,000">₹1,50,000 – ₹3,50,000</option>
                  <option value="₹3,50,000+">₹3,50,000+ (Enterprise)</option>
                  <option value="Flexible / Undecided">Flexible / Undecided</option>
                </select>
              </div>
            </div>
            <div>
              <label>Describe Your Challenge or Question *</label>
              <textarea name="message" rows="4" required placeholder="What's the question you're trying to answer? Tell us about the product, formulation, or idea you're working on..."></textarea>
            </div>
            <div style="text-align:center; margin-top:8px;">
              <button type="submit" class="form-submit-btn">Submit Inquiry →</button>
            </div>
          </div>
        </form>
      </div>

    </div>
  </div>

</div>

<script>
/**
 * Interactive Hub Nucleus Dynamic Focus on Card Hover
 */
function highlightHubNode(nodeId, tag, title, sub) {
  const nucleus = document.getElementById('orbital-nucleus');
  const tagEl = document.getElementById('nucleus-tag');
  const titleEl = document.getElementById('nucleus-title');
  const subEl = document.getElementById('nucleus-sub');
  const spoke = document.getElementById('spoke-' + nodeId);
  
  if (nucleus && tagEl && titleEl && subEl) {
    nucleus.style.borderColor = '#d4af37';
    nucleus.style.boxShadow = '0 0 0 7px rgba(212, 175, 55, 0.2), 0 0 35px rgba(212, 175, 55, 0.45)';
    nucleus.style.transform = 'translate(-50%, -50%) scale(1.08)';
    tagEl.innerText = tag;
    tagEl.style.color = '#d4af37';
    titleEl.innerHTML = title;
    subEl.innerText = sub;
    subEl.style.color = '#8b5a2b';
  }
  if (spoke) {
    spoke.style.stroke = '#d4af37';
    spoke.style.strokeWidth = '2.2';
    spoke.style.strokeDasharray = 'none';
  }
}

function resetHubNode() {
  const nucleus = document.getElementById('orbital-nucleus');
  const tagEl = document.getElementById('nucleus-tag');
  const titleEl = document.getElementById('nucleus-title');
  const subEl = document.getElementById('nucleus-sub');
  
  if (nucleus && tagEl && titleEl && subEl) {
    nucleus.style.borderColor = '';
    nucleus.style.boxShadow = '';
    nucleus.style.transform = '';
    tagEl.innerText = 'THE NUCLEUS';
    tagEl.style.color = '';
    titleEl.innerHTML = 'THE<br>QUESTION';
    subEl.innerText = 'Curiosity starts here';
    subEl.style.color = '';
  }
  for (let i = 1; i <= 5; i++) {
    const spoke = document.getElementById('spoke-' + i);
    if (spoke) {
      spoke.style.stroke = '';
      spoke.style.strokeWidth = '';
      spoke.style.strokeDasharray = '';
    }
  }
}

/**
 * Consultation Modal Management (Open, Close, Pre-select)
 */
function openConsultationModal(serviceArea) {
  const modal = document.getElementById('consultation-modal');
  if (!modal) return;

  if (serviceArea) {
    const selectElem = document.getElementById('modal-service-select');
    if (selectElem) {
      for (let i = 0; i < selectElem.options.length; i++) {
        const opt = selectElem.options[i];
        if (opt.value.toLowerCase().includes(serviceArea.toLowerCase()) || 
            opt.text.toLowerCase().includes(serviceArea.toLowerCase())) {
          selectElem.selectedIndex = i;
          break;
        }
      }
    }
  }

  modal.classList.add('is-open');
  modal.setAttribute('aria-hidden', 'false');
  document.body.style.overflow = 'hidden';
}

function closeConsultationModal() {
  const modal = document.getElementById('consultation-modal');
  if (!modal) return;
  modal.classList.remove('is-open');
  modal.setAttribute('aria-hidden', 'true');
  document.body.style.overflow = '';
}

function handleModalBackdropClick(e) {
  if (e.target.id === 'consultation-modal') {
    closeConsultationModal();
  }
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeConsultationModal();
  }
});

// Alias for backwards-compatibility with existing card click handlers
function selectCollabArea(areaName) {
  openConsultationModal(areaName);
}

// Auto-open modal if there was a form submission response
<?php if ($submissionSuccess || !empty($submissionError)): ?>
window.addEventListener('DOMContentLoaded', function() {
  openConsultationModal();
});
<?php endif; ?>
</script>

<!-- ================================================================
     HERO LAB VIDEO CONTROLS
     ================================================================ -->
<script>
function toggleWwuVideoPlay() {
  const vid = document.getElementById('wwuHeroVideo');
  const playIcon = document.getElementById('wwuVidPlayIcon');
  const pauseIcon = document.getElementById('wwuVidPauseIcon');
  if (!vid) return;

  if (vid.paused) {
    vid.play().then(() => {
      if (playIcon) playIcon.style.display = 'none';
      if (pauseIcon) pauseIcon.style.display = 'block';
    }).catch(() => {});
  } else {
    vid.pause();
    if (playIcon) playIcon.style.display = 'block';
    if (pauseIcon) pauseIcon.style.display = 'none';
  }
}

function toggleWwuVideoSound() {
  const vid = document.getElementById('wwuHeroVideo');
  const mutedIcon = document.getElementById('wwuVidMutedIcon');
  const soundIcon = document.getElementById('wwuVidSoundIcon');
  if (!vid) return;

  vid.muted = !vid.muted;
  if (vid.muted) {
    if (mutedIcon) mutedIcon.style.display = 'block';
    if (soundIcon) soundIcon.style.display = 'none';
  } else {
    if (mutedIcon) mutedIcon.style.display = 'none';
    if (soundIcon) soundIcon.style.display = 'block';
  }
}

// Auto-sync control icons if user or browser interacts
document.addEventListener('DOMContentLoaded', function() {
  const vid = document.getElementById('wwuHeroVideo');
  if (!vid) return;
  
  vid.addEventListener('play', function() {
    const playIcon = document.getElementById('wwuVidPlayIcon');
    const pauseIcon = document.getElementById('wwuVidPauseIcon');
    if (playIcon) playIcon.style.display = 'none';
    if (pauseIcon) pauseIcon.style.display = 'block';
  });

  vid.addEventListener('pause', function() {
    const playIcon = document.getElementById('wwuVidPlayIcon');
    const pauseIcon = document.getElementById('wwuVidPauseIcon');
    if (playIcon) playIcon.style.display = 'block';
    if (pauseIcon) pauseIcon.style.display = 'none';
  });
});
</script>

<?php
  include $pathPrefix . 'includes/footer.php';
?>
