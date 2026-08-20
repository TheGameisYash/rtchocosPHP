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
  padding: 135px 40px 115px !important;
  background: radial-gradient(ellipse 950px 750px at 68% 45%, rgba(184, 134, 11, 0.14) 0%, rgba(12, 26, 17, 0.96) 55%, #07130b 100%) !important;
  overflow: hidden !important;
  min-height: 90vh !important;
  display: flex !important;
  align-items: center !important;
}

/* Atmospheric golden dust & grid background */
#page-wwu .wwu-hero::before {
  content: '' !important;
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  background-image: 
    radial-gradient(circle 2px at 20% 30%, rgba(212, 175, 55, 0.4) 0%, transparent 100%),
    radial-gradient(circle 2.5px at 75% 25%, rgba(212, 175, 55, 0.5) 0%, transparent 100%),
    radial-gradient(circle 1.5px at 40% 70%, rgba(212, 175, 55, 0.35) 0%, transparent 100%),
    radial-gradient(circle 2px at 85% 65%, rgba(212, 175, 55, 0.4) 0%, transparent 100%),
    radial-gradient(circle 2px at 15% 80%, rgba(212, 175, 55, 0.3) 0%, transparent 100%) !important;
  pointer-events: none !important;
  z-index: 1 !important;
}

/* Background animated celestial coordinate lines */
#page-wwu .wwu-hero-bg-lines {
  position: absolute !important;
  top: 0 !important;
  right: 0 !important;
  width: 65% !important;
  height: 100% !important;
  opacity: 0.18 !important;
  pointer-events: none !important;
  overflow: hidden !important;
  z-index: 1 !important;
}
#page-wwu .wwu-hero-bg-lines svg {
  width: 100% !important;
  height: 100% !important;
}

#page-wwu .wwu-hero-inner {
  max-width: 1320px !important;
  margin: 0 auto !important;
  display: grid !important;
  grid-template-columns: 1.15fr 1fr !important;
  gap: 60px !important;
  align-items: center !important;
  position: relative !important;
  z-index: 2 !important;
  width: 100% !important;
}

/* --- HERO LEFT COLUMN --- */
#page-wwu .wwu-hero-text {
  display: flex !important;
  flex-direction: column !important;
}

#page-wwu .wwu-hero-tag-pill {
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  align-self: flex-start !important;
  padding: 6px 14px !important;
  background: rgba(212, 175, 55, 0.08) !important;
  border: 1px solid rgba(212, 175, 55, 0.3) !important;
  border-radius: 30px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  letter-spacing: 2px !important;
  text-transform: uppercase !important;
  color: #d4af37 !important;
  margin-bottom: 22px !important;
}

#page-wwu .wwu-hero-tag-pill .pill-dot {
  width: 6px !important;
  height: 6px !important;
  border-radius: 50% !important;
  background: #d4af37 !important;
  box-shadow: 0 0 8px rgba(212, 175, 55, 0.9) !important;
}

#page-wwu .wwu-hero h1 {
  font-size: clamp(44px, 5.2vw, 74px) !important;
  line-height: 1.04 !important;
  margin-bottom: 24px !important;
  letter-spacing: -1.5px !important;
  color: #fbf8f0 !important;
  font-weight: 700 !important;
}

#page-wwu .wwu-hero h1 .hl-hero-gold {
  color: #d4af37 !important;
  background: linear-gradient(135deg, #d4af37 0%, #f5e4b2 50%, #b8860b 100%) !important;
  -webkit-background-clip: text !important;
  -webkit-text-fill-color: transparent !important;
  display: inline-block !important;
}

/* Interactive Question Pillars */
#page-wwu .wwu-hero-pillars {
  display: flex !important;
  flex-direction: column !important;
  gap: 10px !important;
  margin-bottom: 30px !important;
}

#page-wwu .hero-pill-item {
  display: flex !important;
  align-items: flex-start !important;
  gap: 12px !important;
  padding: 10px 14px !important;
  background: rgba(255, 255, 255, 0.03) !important;
  border: 1px solid rgba(212, 175, 55, 0.12) !important;
  border-radius: 10px !important;
  cursor: pointer !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

#page-wwu .hero-pill-item:hover {
  background: rgba(212, 175, 55, 0.08) !important;
  border-color: rgba(212, 175, 55, 0.4) !important;
  transform: translateX(5px) !important;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25) !important;
}

#page-wwu .hero-pill-item .pill-marker {
  color: #d4af37 !important;
  font-size: 13px !important;
  margin-top: 2px !important;
  flex-shrink: 0 !important;
  transition: transform 0.3s ease !important;
}

#page-wwu .hero-pill-item:hover .pill-marker {
  transform: scale(1.3) rotate(45deg) !important;
}

#page-wwu .hero-pill-item .pill-txt {
  display: flex !important;
  flex-direction: column !important;
  gap: 2px !important;
}

#page-wwu .hero-pill-item .pill-txt strong {
  font-family: 'Playfair Display', serif !important;
  font-size: 16px !important;
  font-style: italic !important;
  font-weight: 600 !important;
  color: #f0e6d2 !important;
}

#page-wwu .hero-pill-item:hover .pill-txt strong {
  color: #ffffff !important;
}

#page-wwu .hero-pill-item .pill-txt span {
  font-size: 12px !important;
  color: #9f9683 !important;
  line-height: 1.4 !important;
}

/* Tagline Banner */
#page-wwu .wwu-hero-tagline-bar {
  display: flex !important;
  flex-wrap: wrap !important;
  align-items: center !important;
  gap: 8px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 11.5px !important;
  font-weight: 700 !important;
  letter-spacing: 1.5px !important;
  color: #d4af37 !important;
  margin-bottom: 30px !important;
  text-transform: uppercase !important;
}

#page-wwu .wwu-hero-tagline-bar .t-dot {
  opacity: 0.5 !important;
  color: #8b6b3d !important;
}

/* Hero Action Buttons */
#page-wwu .wwu-hero-actions {
  display: flex !important;
  flex-wrap: wrap !important;
  align-items: center !important;
  gap: 16px !important;
  margin-bottom: 35px !important;
}

#page-wwu .wwu-hero-btn-gold {
  display: inline-flex !important;
  align-items: center !important;
  gap: 10px !important;
  padding: 15px 32px !important;
  background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%) !important;
  color: #0c1a11 !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 12.5px !important;
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
  transform: translateY(-3px) !important;
  box-shadow: 0 10px 28px rgba(212, 175, 55, 0.45) !important;
  background: linear-gradient(135deg, #e5c158 0%, #c99718 100%) !important;
}

#page-wwu .wwu-hero-btn-gold svg {
  stroke: #0c1a11 !important;
  stroke-width: 2.5 !important;
  width: 14px !important;
  height: 14px !important;
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
  padding: 14px 28px !important;
  background: rgba(255, 255, 255, 0.04) !important;
  border: 1.5px solid rgba(212, 175, 55, 0.4) !important;
  color: #f5efe1 !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 12.5px !important;
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
  width: 14px !important;
  height: 14px !important;
  fill: none !important;
  transition: transform 0.3s ease !important;
}
#page-wwu .wwu-hero-btn-outline:hover svg {
  transform: translateY(3px) !important;
}

/* Trust / Key Metrics Row */
#page-wwu .wwu-hero-metrics {
  display: grid !important;
  grid-template-columns: repeat(3, 1fr) !important;
  gap: 20px !important;
  padding-top: 24px !important;
  border-top: 1px solid rgba(212, 175, 55, 0.15) !important;
}

#page-wwu .wwu-metric-item {
  display: flex !important;
  flex-direction: column !important;
  gap: 3px !important;
}

#page-wwu .wwu-metric-num {
  font-family: 'Playfair Display', serif !important;
  font-size: 24px !important;
  font-weight: 700 !important;
  color: #d4af37 !important;
}

#page-wwu .wwu-metric-lbl {
  font-size: 11.5px !important;
  color: #9f9683 !important;
  line-height: 1.3 !important;
}

/* --- HERO RIGHT COLUMN: KINETIC CACAO CONSTELLATION --- */
#page-wwu .wwu-hero-visual {
  position: relative !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  min-height: 520px !important;
}

#page-wwu .wwu-pod-constellation {
  position: relative !important;
  width: 500px !important;
  height: 500px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

/* SVG Orbit Backdrop & Spoke Rays */
#page-wwu .wwu-constellation-svg {
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  width: 100% !important;
  height: 100% !important;
  pointer-events: none !important;
  z-index: 1 !important;
  overflow: visible !important;
}

/* Rotating Dash Orbit */
#page-wwu .constellation-orbit-spin {
  transform-origin: 250px 250px !important;
  animation: wwuOrbitClockwise 90s linear infinite !important;
}

@keyframes wwuOrbitClockwise {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes wwuOrbitCounter {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(-360deg); }
}

/* Central Floating Cacao Pod */
#page-wwu .wwu-cacao-nucleus {
  position: absolute !important;
  top: 50% !important;
  left: 50% !important;
  transform: translate(-50%, -50%) !important;
  z-index: 3 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  cursor: pointer !important;
}

#page-wwu .wwu-pod-artwork {
  width: 175px !important;
  height: auto !important;
  filter: drop-shadow(0 15px 35px rgba(212, 175, 55, 0.25)) drop-shadow(0 5px 15px rgba(0, 0, 0, 0.6)) !important;
  animation: wwuPodFloat 6s ease-in-out infinite alternate !important;
  transition: transform 0.4s ease, filter 0.4s ease !important;
}

#page-wwu .wwu-cacao-nucleus:hover .wwu-pod-artwork {
  transform: scale(1.06) !important;
  filter: drop-shadow(0 20px 45px rgba(212, 175, 55, 0.4)) drop-shadow(0 8px 25px rgba(0, 0, 0, 0.7)) !important;
}

@keyframes wwuPodFloat {
  0% { transform: translateY(0px) rotate(0deg); }
  100% { transform: translateY(-10px) rotate(1.5deg); }
}

/* Satellite Science Badges */
#page-wwu .wwu-satellite-node {
  position: absolute !important;
  z-index: 4 !important;
  cursor: pointer !important;
}

#page-wwu .wwu-satellite-pill {
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  padding: 6px 13px !important;
  background: rgba(12, 26, 17, 0.85) !important;
  backdrop-filter: blur(8px) !important;
  -webkit-backdrop-filter: blur(8px) !important;
  border: 1px solid rgba(212, 175, 55, 0.3) !important;
  border-radius: 20px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 9.5px !important;
  font-weight: 800 !important;
  letter-spacing: 2px !important;
  text-transform: uppercase !important;
  color: #dfd3bf !important;
  white-space: nowrap !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4) !important;
}

#page-wwu .wwu-satellite-pill .node-glow-dot {
  width: 5px !important;
  height: 5px !important;
  border-radius: 50% !important;
  background: #d4af37 !important;
  box-shadow: 0 0 6px rgba(212, 175, 55, 0.9) !important;
}

#page-wwu .wwu-satellite-node:hover .wwu-satellite-pill {
  background: #d4af37 !important;
  color: #0c1a11 !important;
  border-color: #d4af37 !important;
  transform: scale(1.08) !important;
  box-shadow: 0 8px 24px rgba(212, 175, 55, 0.45) !important;
}

#page-wwu .wwu-satellite-node:hover .node-glow-dot {
  background: #0c1a11 !important;
  box-shadow: none !important;
}

/* Tooltip descriptor on hover */
#page-wwu .wwu-satellite-tooltip {
  position: absolute !important;
  bottom: 125% !important;
  left: 50% !important;
  transform: translateX(-50%) translateY(6px) !important;
  background: #18281d !important;
  border: 1px solid rgba(212, 175, 55, 0.4) !important;
  border-radius: 8px !important;
  padding: 6px 12px !important;
  font-size: 11px !important;
  font-weight: 500 !important;
  color: #f5efe1 !important;
  white-space: nowrap !important;
  pointer-events: none !important;
  opacity: 0 !important;
  visibility: hidden !important;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5) !important;
  z-index: 10 !important;
}

#page-wwu .wwu-satellite-tooltip::after {
  content: '' !important;
  position: absolute !important;
  top: 100% !important;
  left: 50% !important;
  transform: translateX(-50%) !important;
  border: 4px solid transparent !important;
  border-top-color: #18281d !important;
}

#page-wwu .wwu-satellite-node:hover .wwu-satellite-tooltip {
  opacity: 1 !important;
  visibility: visible !important;
  transform: translateX(-50%) translateY(0) !important;
}

/* --- PRECISE POSITIONS FOR 7 SATELLITE NODES ON 500x500 CANVAS --- */
/* 1. INGREDIENTS (Top Left) */
#page-wwu .node-ingredients { top: 12% !important; left: 6% !important; }

/* 2. FORMULATION (Top Right) */
#page-wwu .node-formulation { top: 12% !important; right: 4% !important; }

/* 3. TEXTURE (Mid Left) */
#page-wwu .node-texture     { top: 40% !important; left: -4% !important; }

/* 4. FLAVOUR (Mid Right) */
#page-wwu .node-flavour     { top: 38% !important; right: -6% !important; }

/* 5. PROCESS (Lower Left) */
#page-wwu .node-process     { bottom: 22% !important; left: 2% !important; }

/* 6. EXPERIMENT (Lower Right) */
#page-wwu .node-experiment  { bottom: 20% !important; right: -4% !important; }

/* 7. SENSORY (Bottom Center) */
#page-wwu .node-sensory     { bottom: 6% !important; left: 50% !important; transform: translateX(-50%) !important; }


/* ============================================================
   SECTION 2: HOW WE COLLABORATE (cream/light with botanical sketch)
   ============================================================ */
#page-wwu .wwu-collab {
  position: relative !important;
  padding: 110px 40px 100px !important;
  background: #f5f0e6 !important;
  border-top: 1px solid rgba(212,175,55,0.15) !important;
  overflow: hidden !important;
}

/* Botanical Sketch in background right corner matching reference */
#page-wwu .wwu-collab-sketch {
  position: absolute !important;
  top: -40px !important;
  right: -30px !important;
  width: 440px !important;
  height: auto !important;
  opacity: 0.88 !important;
  mix-blend-mode: multiply !important;
  pointer-events: none !important;
  z-index: 1 !important;
  filter: contrast(1.08) sepia(0.08) !important;
  transition: opacity 0.4s ease !important;
}

#page-wwu .wwu-collab-inner {
  max-width: 1200px !important;
  margin: 0 auto !important;
  display: grid !important;
  grid-template-columns: 1fr auto 1.35fr !important;
  gap: 45px !important;
  align-items: center !important;
  position: relative !important;
  z-index: 2 !important;
  padding-right: 140px !important;
}

#page-wwu .wwu-collab-left {
  max-width: 460px !important;
}

#page-wwu .wwu-collab .wwu-tag {
  color: #8b6b3d !important;
  margin-bottom: 18px !important;
  letter-spacing: 2.5px !important;
}

#page-wwu .wwu-collab h2 {
  font-size: clamp(34px, 4vw, 54px) !important;
  line-height: 1.1 !important;
  color: #1a1a14 !important;
  margin: 0 !important;
}

/* Center vertical divider */
#page-wwu .wwu-collab-divider {
  width: 1px !important;
  background: #d8cca8 !important;
  align-self: stretch !important;
  min-height: 130px !important;
}

#page-wwu .wwu-collab-right {
  max-width: 580px !important;
}

#page-wwu .wwu-collab-right p {
  font-size: 15px !important;
  line-height: 1.85 !important;
  color: #3d3a30 !important;
  font-weight: 400 !important;
  margin: 0 0 18px 0 !important;
}
#page-wwu .wwu-collab-right p:last-child {
  margin-bottom: 0 !important;
}
#page-wwu .wwu-collab-right strong {
  color: #1a1a14 !important;
  font-weight: 600 !important;
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
  padding: 120px 40px !important;
  background: radial-gradient(ellipse at 70% 50%, #fdfbf7 0%, #f6f0e4 50%, #eee5d3 100%) !important;
  border-top: 1px solid #dfd4bf !important;
  border-bottom: 1px solid #dfd4bf !important;
  overflow: hidden !important;
}

/* Subtle background luxury watermark */
#page-wwu .wwu-areas::before {
  content: '' !important;
  position: absolute !important;
  top: -100px !important;
  right: -100px !important;
  width: 600px !important;
  height: 600px !important;
  background: radial-gradient(circle, rgba(212, 175, 55, 0.08) 0%, rgba(212, 175, 55, 0) 70%) !important;
  pointer-events: none !important;
  z-index: 1 !important;
}

#page-wwu .wwu-areas-inner {
  position: relative !important;
  z-index: 2 !important;
  max-width: 1320px !important;
  margin: 0 auto !important;
  display: grid !important;
  grid-template-columns: 380px 1fr !important;
  gap: 70px !important;
  align-items: center !important;
}

/* Left text panel */
#page-wwu .wwu-areas-left {
  display: flex !important;
  flex-direction: column !important;
  justify-content: center !important;
}

#page-wwu .wwu-areas-left .wwu-tag-pill {
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  align-self: flex-start !important;
  padding: 6px 14px !important;
  background: rgba(139, 90, 43, 0.08) !important;
  border: 1px solid rgba(212, 175, 55, 0.35) !important;
  border-radius: 30px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  letter-spacing: 2px !important;
  text-transform: uppercase !important;
  color: #8b5a2b !important;
  margin-bottom: 18px !important;
}

#page-wwu .wwu-areas-left .wwu-tag-pill .pill-dot {
  width: 6px !important;
  height: 6px !important;
  border-radius: 50% !important;
  background: #d4af37 !important;
  box-shadow: 0 0 6px rgba(212, 175, 55, 0.8) !important;
}

#page-wwu .wwu-areas-left h2 {
  font-size: clamp(32px, 3.4vw, 46px) !important;
  line-height: 1.15 !important;
  color: #1a1a14 !important;
  margin-bottom: 22px !important;
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
  font-size: 15.5px !important;
  line-height: 1.75 !important;
  color: #4a4538 !important;
  margin-bottom: 28px !important;
}

/* Feature bullets */
#page-wwu .wwu-areas-features {
  display: flex !important;
  flex-direction: column !important;
  gap: 12px !important;
  margin-bottom: 34px !important;
  padding-left: 0 !important;
  list-style: none !important;
}

#page-wwu .wwu-areas-features li {
  display: flex !important;
  align-items: center !important;
  gap: 12px !important;
  font-size: 13.5px !important;
  font-weight: 600 !important;
  color: #2c271e !important;
}

#page-wwu .wwu-areas-features li svg {
  width: 18px !important;
  height: 18px !important;
  flex-shrink: 0 !important;
  fill: #b8941e !important;
}

/* Primary CTA Button */
#page-wwu .wwu-btn-areas-cta {
  display: inline-flex !important;
  align-items: center !important;
  gap: 10px !important;
  align-self: flex-start !important;
  padding: 14px 30px !important;
  background: #1a1a14 !important;
  color: #f6f0e4 !important;
  border: 1.5px solid #1a1a14 !important;
  border-radius: 8px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 12.5px !important;
  font-weight: 700 !important;
  letter-spacing: 1.5px !important;
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
  box-shadow: 0 8px 24px rgba(212, 175, 55, 0.35) !important;
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
  max-width: 720px !important;
  min-height: 640px !important;
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
  transform-origin: 360px 320px !important;
  animation: wwuRotateOrbit 120s linear infinite !important;
}

@keyframes wwuRotateOrbit {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Center Core Nucleus */
#page-wwu .wwu-diagram-center {
  position: absolute !important;
  top: 50% !important;
  left: 50% !important;
  transform: translate(-50%, -50%) !important;
  width: 150px !important;
  height: 150px !important;
  border-radius: 50% !important;
  background: radial-gradient(circle at 35% 35%, #ffffff 0%, #f7f2e7 65%, #eadfc9 100%) !important;
  border: 2px solid rgba(212, 175, 55, 0.45) !important;
  box-shadow: 
    0 0 0 6px rgba(212, 175, 55, 0.08),
    0 0 35px rgba(212, 175, 55, 0.22),
    0 12px 30px rgba(26, 26, 20, 0.08) !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  justify-content: center !important;
  text-align: center !important;
  padding: 14px !important;
  z-index: 5 !important;
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

#page-wwu .wwu-diagram-center:hover {
  transform: translate(-50%, -50%) scale(1.05) !important;
  border-color: #d4af37 !important;
  box-shadow: 
    0 0 0 10px rgba(212, 175, 55, 0.12),
    0 0 50px rgba(212, 175, 55, 0.35),
    0 16px 36px rgba(26, 26, 20, 0.12) !important;
}

#page-wwu .wwu-diagram-center .dc-pulse-ring {
  position: absolute !important;
  top: -8px !important;
  left: -8px !important;
  right: -8px !important;
  bottom: -8px !important;
  border-radius: 50% !important;
  border: 1.5px dashed rgba(184, 148, 30, 0.4) !important;
  animation: wwuPulseCore 4s ease-in-out infinite alternate !important;
  pointer-events: none !important;
}

@keyframes wwuPulseCore {
  0% { transform: scale(0.96); opacity: 0.5; }
  100% { transform: scale(1.06); opacity: 0.9; }
}

#page-wwu .wwu-diagram-center .dc-the {
  font-family: 'Inter', sans-serif !important;
  font-size: 8.5px !important;
  font-weight: 800 !important;
  letter-spacing: 2.5px !important;
  text-transform: uppercase !important;
  color: #8b5a2b !important;
  margin-bottom: 2px !important;
}

#page-wwu .wwu-diagram-center .dc-title {
  font-family: 'Playfair Display', serif !important;
  font-size: 16px !important;
  font-weight: 800 !important;
  color: #1a1a14 !important;
  line-height: 1.15 !important;
  letter-spacing: 0.5px !important;
}

#page-wwu .wwu-diagram-center .dc-sub {
  font-size: 9.5px !important;
  color: #6b614e !important;
  font-style: italic !important;
  margin-top: 4px !important;
  line-height: 1.25 !important;
}

/* ============================================================
   COLLABORATION NODE CARDS (Desktop Positions)
   ============================================================ */
#page-wwu .wwu-area-card {
  position: absolute !important;
  width: 220px !important;
  background: rgba(255, 255, 255, 0.88) !important;
  backdrop-filter: blur(12px) !important;
  -webkit-backdrop-filter: blur(12px) !important;
  border: 1px solid rgba(212, 175, 55, 0.3) !important;
  border-radius: 14px !important;
  padding: 14px 16px !important;
  box-shadow: 
    0 6px 20px rgba(26, 26, 20, 0.05),
    0 2px 6px rgba(139, 90, 43, 0.04) !important;
  z-index: 4 !important;
  cursor: pointer !important;
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

#page-wwu .wwu-area-card:hover {
  transform: translateY(-5px) scale(1.03) !important;
  background: rgba(255, 255, 255, 0.98) !important;
  border-color: #d4af37 !important;
  box-shadow: 
    0 14px 32px rgba(212, 175, 55, 0.22),
    0 4px 12px rgba(26, 26, 20, 0.08) !important;
}

/* Card Header (Icon + Tag) */
#page-wwu .wwu-area-card .card-head {
  display: flex !important;
  align-items: center !important;
  gap: 10px !important;
  margin-bottom: 8px !important;
}

#page-wwu .wwu-area-card .area-icon {
  width: 36px !important;
  height: 36px !important;
  border-radius: 10px !important;
  background: linear-gradient(135deg, rgba(212, 175, 55, 0.18) 0%, rgba(139, 90, 43, 0.1) 100%) !important;
  border: 1px solid rgba(212, 175, 55, 0.4) !important;
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
  width: 18px !important;
  height: 18px !important;
  stroke: #8b5a2b !important;
  fill: none !important;
  stroke-width: 1.8 !important;
  transition: stroke 0.3s ease !important;
}

#page-wwu .wwu-area-card:hover .area-icon svg {
  stroke: #d4af37 !important;
}

#page-wwu .wwu-area-card .area-label {
  font-family: 'Inter', sans-serif !important;
  font-size: 9px !important;
  font-weight: 800 !important;
  letter-spacing: 2px !important;
  text-transform: uppercase !important;
  color: #8b5a2b !important;
  display: block !important;
}

#page-wwu .wwu-area-card h4 {
  font-family: 'Playfair Display', serif !important;
  font-size: 14.5px !important;
  color: #1a1a14 !important;
  margin-bottom: 5px !important;
  font-weight: 700 !important;
  line-height: 1.25 !important;
  transition: color 0.3s ease !important;
}

#page-wwu .wwu-area-card:hover h4 {
  color: #8b5a2b !important;
}

#page-wwu .wwu-area-card p {
  font-size: 11px !important;
  line-height: 1.5 !important;
  color: #555043 !important;
  margin: 0 0 8px 0 !important;
}

#page-wwu .wwu-area-card .card-action {
  display: inline-flex !important;
  align-items: center !important;
  gap: 5px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 10.5px !important;
  font-weight: 700 !important;
  letter-spacing: 0.5px !important;
  color: #8b5a2b !important;
  text-transform: uppercase !important;
  transition: all 0.2s ease !important;
}

#page-wwu .wwu-area-card:hover .card-action {
  color: #1a1a14 !important;
  gap: 8px !important;
}

/* --- PRECISE TRIGONOMETRIC POSITIONS (5 Nodes on 640px height canvas) --- */
/* 1. CREATE (0° / Top Center) */
#page-wwu .card-create {
  top: 10px !important;
  left: 50% !important;
  transform: translateX(-50%) !important;
  text-align: center !important;
  width: 250px !important;
}
#page-wwu .card-create .card-head {
  justify-content: center !important;
}

/* 2. SOLVE (72° / Top Right) */
#page-wwu .card-solve {
  top: 130px !important;
  right: 0px !important;
  text-align: left !important;
}

/* 3. EXPLORE (144° / Bottom Right) */
#page-wwu .card-explore {
  bottom: 20px !important;
  right: 35px !important;
  text-align: left !important;
}

/* 4. CONNECT (216° / Bottom Left) */
#page-wwu .card-connect {
  bottom: 20px !important;
  left: 35px !important;
  text-align: left !important;
}

/* 5. LEARN (288° / Top Left) */
#page-wwu .card-learn {
  top: 130px !important;
  left: 0px !important;
  text-align: left !important;
}

/* Mobile Timeline Container (hidden on desktop) */
#page-wwu .wwu-mobile-areas-flow {
  display: none !important;
}


/* ============================================================
   SECTION 5: WHO IS THIS FOR (warm cream)
   ============================================================ */
#page-wwu .wwu-audience {
  padding: 80px 40px !important;
  background: #ede7d9 !important;
  border-top: 1px solid #ddd4c2 !important;
}
#page-wwu .wwu-audience-inner {
  max-width: 1100px !important;
  margin: 0 auto !important;
}
#page-wwu .wwu-audience-top {
  display: grid !important;
  grid-template-columns: 1fr 1.5fr !important;
  gap: 50px !important;
  align-items: center !important;
}
#page-wwu .wwu-audience .wwu-tag {
  color: #8b6b3d !important;
  margin-bottom: 12px !important;
}
#page-wwu .wwu-audience h2 {
  font-size: clamp(28px, 3vw, 40px) !important;
  color: #1a1a14 !important;
  line-height: 1.15 !important;
}
#page-wwu .wwu-aud-tags {
  display: flex !important;
  flex-wrap: wrap !important;
  gap: 10px !important;
}
#page-wwu .wwu-aud-tags .at {
  font-family: 'Inter', sans-serif !important;
  font-size: 13px !important;
  font-weight: 500 !important;
  color: #3d3a30 !important;
  padding: 10px 22px !important;
  border-radius: 30px !important;
  border: 1px solid #c4b89a !important;
  background: #f5f0e6 !important;
  transition: all 0.3s ease !important;
  cursor: default !important;
}
#page-wwu .wwu-aud-tags .at:hover {
  border-color: #d4af37 !important;
  background: rgba(212,175,55,0.08) !important;
  color: #1a1a14 !important;
}


/* ============================================================
   SECTION 6: WHY RT CHOCOS (dark)
   ============================================================ */
#page-wwu .wwu-why {
  padding: 90px 40px !important;
  background: #0c1a11 !important;
  border-top: 1px solid rgba(212,175,55,0.12) !important;
}
#page-wwu .wwu-why-inner {
  max-width: 1100px !important;
  margin: 0 auto !important;
  display: grid !important;
  grid-template-columns: 1fr 1.2fr !important;
  gap: 80px !important;
  align-items: start !important;
}
#page-wwu .wwu-why h2 {
  font-size: clamp(28px, 3.2vw, 44px) !important;
  line-height: 1.15 !important;
  color: #f5efe1 !important;
}
#page-wwu .wwu-why-right {
  display: grid !important;
  grid-template-columns: repeat(3,1fr) !important;
  gap: 28px !important;
}
#page-wwu .wwu-why-pillar {
  padding: 0 !important;
}
#page-wwu .wwu-why-pillar h4 {
  font-family: 'Inter', sans-serif !important;
  font-size: 13px !important;
  font-weight: 700 !important;
  letter-spacing: 1px !important;
  text-transform: uppercase !important;
  color: #d4af37 !important;
  margin-bottom: 12px !important;
}
#page-wwu .wwu-why-pillar p {
  font-size: 13.5px !important;
  line-height: 1.7 !important;
  color: #a09882 !important;
  font-weight: 400 !important;
  margin: 0 !important;
}


/* ============================================================
   SECTION 7: THE CHOCOLATE CHALLENGE (dark, bottom CTA with sketch watermark)
   ============================================================ */
#page-wwu .wwu-challenge {
  padding: 100px 40px !important;
  background: #0a1610 !important;
  border-top: 1px solid rgba(212,175,55,0.08) !important;
  position: relative !important;
  overflow: hidden !important;
}

/* Botanical Sketch watermark on bottom left matching reference */
#page-wwu .wwu-challenge-sketch {
  position: absolute !important;
  bottom: -30px !important;
  left: -20px !important;
  width: 280px !important;
  height: auto !important;
  opacity: 0.12 !important;
  pointer-events: none !important;
  filter: invert(1) brightness(1.5) sepia(1) hue-rotate(5deg) !important;
  mix-blend-mode: screen !important;
}

#page-wwu .wwu-challenge-inner {
  max-width: 1100px !important;
  margin: 0 auto !important;
  display: grid !important;
  grid-template-columns: 1fr 1fr !important;
  gap: 80px !important;
  align-items: start !important;
  position: relative !important;
  z-index: 1 !important;
}
#page-wwu .wwu-challenge h2 {
  font-size: clamp(24px, 2.8vw, 36px) !important;
  line-height: 1.3 !important;
  color: #f5efe1 !important;
  margin-bottom: 20px !important;
}
#page-wwu .wwu-challenge h2 strong {
  color: #d4af37 !important;
}
#page-wwu .wwu-challenge-left > p {
  font-size: 14px !important;
  line-height: 1.7 !important;
  color: #a09882 !important;
  margin-bottom: 28px !important;
}

/* Gold CTA button */
#page-wwu .wwu-btn-gold {
  display: inline-flex !important;
  align-items: center !important;
  gap: 10px !important;
  padding: 14px 32px !important;
  background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%) !important;
  color: #0c1a11 !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 12px !important;
  font-weight: 700 !important;
  letter-spacing: 1px !important;
  text-transform: uppercase !important;
  border-radius: 6px !important;
  border: none !important;
  cursor: pointer !important;
  transition: all 0.3s ease !important;
}
#page-wwu .wwu-btn-gold span {
  color: #0c1a11 !important;
}
#page-wwu .wwu-btn-gold:hover {
  box-shadow: 0 8px 30px rgba(212,175,55,0.35) !important;
  transform: translateY(-2px) !important;
}

/* Challenge right-side pillars */
#page-wwu .wwu-ch-pillars {
  display: grid !important;
  grid-template-columns: 1fr 1fr !important;
  gap: 20px !important;
}
#page-wwu .wwu-ch-pill {
  padding: 24px !important;
  border-radius: 12px !important;
  background: rgba(212,175,55,0.03) !important;
  border: 1px solid rgba(212,175,55,0.1) !important;
}
#page-wwu .wwu-ch-pill h4 {
  font-family: 'Inter', sans-serif !important;
  font-size: 12px !important;
  font-weight: 700 !important;
  letter-spacing: 1px !important;
  text-transform: uppercase !important;
  color: #d4af37 !important;
  margin-bottom: 8px !important;
}
#page-wwu .wwu-ch-pill p {
  font-size: 12.5px !important;
  line-height: 1.6 !important;
  color: #8a8272 !important;
  margin: 0 !important;
}


/* ============================================================
   SECTION 8: CONSULTATION FORM (dark)
   ============================================================ */
#page-wwu .wwu-form-sec {
  padding: 100px 40px !important;
  background: #0c1a11 !important;
  border-top: 1px solid rgba(212,175,55,0.12) !important;
}
#page-wwu .wwu-form-card {
  max-width: 820px !important;
  margin: 0 auto !important;
  background: rgba(10, 26, 17, 0.95) !important;
  border: 1px solid rgba(212, 175, 55, 0.28) !important;
  border-radius: 20px !important;
  padding: 56px 44px !important;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5) !important;
}
#page-wwu .wwu-form-card .wwu-tag {
  text-align: center !important;
  margin-bottom: 8px !important;
}
#page-wwu .wwu-form-card h2 {
  text-align: center !important;
  font-size: clamp(28px, 3.5vw, 38px) !important;
  margin-bottom: 12px !important;
  color: #ffffff !important;
}
#page-wwu .wwu-form-subtitle {
  text-align: center !important;
  font-size: 14.5px !important;
  color: #a09882 !important;
  font-weight: 400 !important;
  max-width: 560px !important;
  margin: 0 auto 36px !important;
  line-height: 1.7 !important;
}
#page-wwu .wwu-form-card label {
  display: block !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  letter-spacing: 1.5px !important;
  text-transform: uppercase !important;
  color: #d4af37 !important;
  margin-bottom: 8px !important;
}
#page-wwu .wwu-form-card input,
#page-wwu .wwu-form-card select,
#page-wwu .wwu-form-card textarea {
  width: 100% !important;
  background: rgba(255,255,255,0.05) !important;
  border: 1px solid rgba(212,175,55,0.22) !important;
  color: #f5efe1 !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 14px !important;
  border-radius: 8px !important;
  padding: 13px 16px !important;
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
}
#page-wwu .wwu-form-card select option {
  background: #0c1a11 !important;
  color: #f5efe1 !important;
}
#page-wwu .form-grid {
  display: grid !important;
  gap: 22px !important;
}
#page-wwu .form-row {
  display: grid !important;
  grid-template-columns: 1fr 1fr !important;
  gap: 20px !important;
}
#page-wwu .form-submit-btn {
  display: block !important;
  width: 100% !important;
  max-width: 340px !important;
  margin: 12px auto 0 !important;
  padding: 16px 40px !important;
  background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%) !important;
  color: #0c1a11 !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 14px !important;
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
  padding: 20px 24px !important;
  border-radius: 12px !important;
  text-align: center !important;
  font-size: 15px !important;
  font-weight: 600 !important;
  margin-bottom: 28px !important;
}
#page-wwu .alert-err {
  background: rgba(255,107,107,0.12) !important;
  border: 1px solid #ff6b6b !important;
  color: #ff6b6b !important;
  padding: 16px 20px !important;
  border-radius: 12px !important;
  text-align: center !important;
  font-size: 14px !important;
  font-weight: 600 !important;
  margin-bottom: 28px !important;
}


/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 1024px) {
  #page-wwu .wwu-hero-inner {
    grid-template-columns: 1fr !important;
    text-align: center !important;
    gap: 50px !important;
  }
  #page-wwu .wwu-hero-text {
    max-width: 100% !important;
    align-items: center !important;
  }
  #page-wwu .wwu-hero-tag-pill {
    align-self: center !important;
  }
  #page-wwu .wwu-hero-actions {
    justify-content: center !important;
  }
  #page-wwu .wwu-hero-tagline-bar {
    justify-content: center !important;
  }
  #page-wwu .wwu-hero-pillars {
    text-align: left !important;
    max-width: 580px !important;
    margin-left: auto !important;
    margin-right: auto !important;
  }
  #page-wwu .wwu-hero-metrics {
    max-width: 540px !important;
    margin: 0 auto !important;
    width: 100% !important;
  }
  #page-wwu .wwu-hero-visual {
    min-height: 440px !important;
  }
  #page-wwu .wwu-pod-constellation {
    width: 420px !important;
    height: 420px !important;
  }
  #page-wwu .wwu-collab-sketch {
    width: 300px !important;
    opacity: 0.5 !important;
    right: -50px !important;
  }
  #page-wwu .wwu-collab-inner {
    grid-template-columns: 1fr !important;
    padding-right: 0 !important;
    gap: 30px !important;
  }
  #page-wwu .wwu-collab-divider {
    display: none !important;
  }
  #page-wwu .wwu-audience-top,
  #page-wwu .wwu-why-inner,
  #page-wwu .wwu-challenge-inner {
    grid-template-columns: 1fr !important;
    gap: 40px !important;
  }
  #page-wwu .wwu-begin-grid {
    grid-template-columns: 1fr 1fr !important;
  }
  #page-wwu .wwu-areas-inner {
    grid-template-columns: 1fr !important;
    gap: 50px !important;
  }
  #page-wwu .wwu-why-right {
    grid-template-columns: repeat(3,1fr) !important;
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
    padding: 110px 20px 70px !important;
    min-height: auto !important;
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
  #page-wwu .wwu-hero-metrics {
    grid-template-columns: 1fr !important;
    gap: 16px !important;
  }
  #page-wwu .wwu-pod-constellation {
    width: 320px !important;
    height: 330px !important;
  }
  #page-wwu .wwu-pod-artwork {
    width: 120px !important;
  }
  #page-wwu .wwu-satellite-pill {
    font-size: 8px !important;
    padding: 4px 8px !important;
    letter-spacing: 1px !important;
  }
  #page-wwu .wwu-collab-sketch {
    display: none !important;
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
  #page-wwu .wwu-why-right {
    grid-template-columns: 1fr !important;
  }
  #page-wwu .wwu-collab,
  #page-wwu .wwu-begin,
  #page-wwu .wwu-areas,
  #page-wwu .wwu-audience,
  #page-wwu .wwu-why,
  #page-wwu .wwu-challenge,
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

      <!-- Left Column: Editorial & Conversion -->
      <div class="wwu-hero-text">
        <div class="wwu-hero-tag-pill">
          <span class="pill-dot"></span>
          <span>B2B CHOCOLATE R&amp;D &amp; FORMULATION PLATFORM</span>
        </div>

        <h1>Bring us<br><span class="hl-hero-gold">the question.</span></h1>

        <!-- Interactive Question Pillars -->
        <div class="wwu-hero-pillars">
          <div class="hero-pill-item" onclick="selectCollabArea('R&D & Product Innovation')">
            <span class="pill-marker">✦</span>
            <div class="pill-txt">
              <strong>A product to rethink.</strong>
              <span>Formulation, sensory profile &amp; market repositioning.</span>
            </div>
          </div>
          <div class="hero-pill-item" onclick="selectCollabArea('Ingredients & Application Lab')">
            <span class="pill-marker">✦</span>
            <div class="pill-txt">
              <strong>An ingredient to explore.</strong>
              <span>Novel fats, clean-label sweeteners &amp; functional inclusions.</span>
            </div>
          </div>
          <div class="hero-pill-item" onclick="selectCollabArea('Chocolate Problem Solving')">
            <span class="pill-marker">✦</span>
            <div class="pill-txt">
              <strong>A problem to solve.</strong>
              <span>Fat bloom, viscosity drift, tempering &amp; shelf-life defects.</span>
            </div>
          </div>
          <div class="hero-pill-item" onclick="selectCollabArea('R&D & Product Innovation')">
            <span class="pill-marker">✦</span>
            <div class="pill-txt">
              <strong>An idea that hasn't been tried yet.</strong>
              <span>Translating blue-sky concepts into pilot prototypes.</span>
            </div>
          </div>
        </div>

        <!-- Tagline Banner -->
        <div class="wwu-hero-tagline-bar">
          <span>Chocolate Science</span>
          <span class="t-dot">•</span>
          <span>Formulation R&amp;D</span>
          <span class="t-dot">•</span>
          <span>Application Lab</span>
          <span class="t-dot">•</span>
          <span>Knowledge Collabs</span>
        </div>

        <!-- Action Buttons -->
        <div class="wwu-hero-actions">
          <a href="#inquiry-form" class="wwu-hero-btn-gold">
            <span>START A CONSULTATION</span>
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <a href="#collaboration-areas" class="wwu-hero-btn-outline">
            <span>EXPLORE 5 AREAS</span>
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
          </a>
        </div>

        <!-- Trust / Metrics Bar -->
        <div class="wwu-hero-metrics">
          <div class="wwu-metric-item">
            <span class="wwu-metric-num">10+ Years</span>
            <span class="wwu-metric-lbl">Formulation R&amp;D Mastery</span>
          </div>
          <div class="wwu-metric-item">
            <span class="wwu-metric-num">50+ Projects</span>
            <span class="wwu-metric-lbl">Custom Formulations Tested</span>
          </div>
          <div class="wwu-metric-item">
            <span class="wwu-metric-num">100% IP</span>
            <span class="wwu-metric-lbl">Strict NDA &amp; Confidentiality</span>
          </div>
        </div>
      </div>

      <!-- Right Column: Interactive Cacao Constellation -->
      <div class="wwu-hero-visual">
        <div class="wwu-pod-constellation">
          
          <!-- SVG Orbit Tracks & Laser Spokes -->
          <svg class="wwu-constellation-svg" viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient id="podSpokeGrad" x1="250" y1="250" x2="0" y2="0" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#d4af37" stop-opacity="0.8"/>
                <stop offset="100%" stop-color="#8b6b3d" stop-opacity="0.1"/>
              </linearGradient>
            </defs>

            <!-- Outer Celestial Guide Ring -->
            <circle cx="250" cy="250" r="235" stroke="#d4af37" stroke-width="0.8" stroke-opacity="0.15" stroke-dasharray="4 8"/>
            
            <!-- Rotating Dash Orbit -->
            <g class="constellation-orbit-spin">
              <circle cx="250" cy="250" r="185" stroke="#d4af37" stroke-width="1.2" stroke-opacity="0.3" stroke-dasharray="6 6"/>
              <circle cx="250" cy="65" r="3.5" fill="#d4af37" opacity="0.8"/>
              <circle cx="426" cy="193" r="3.5" fill="#d4af37" opacity="0.8"/>
              <circle cx="359" cy="399" r="3.5" fill="#d4af37" opacity="0.8"/>
              <circle cx="141" cy="399" r="3.5" fill="#d4af37" opacity="0.8"/>
              <circle cx="74" cy="193" r="3.5" fill="#d4af37" opacity="0.8"/>
            </g>

            <!-- Inner Pulsing Orbit -->
            <circle cx="250" cy="250" r="110" stroke="#d4af37" stroke-width="0.8" stroke-opacity="0.25" stroke-dasharray="2 4"/>

            <!-- 7 Fine Laser Spokes to Satellite Nodes -->
            <line x1="250" y1="250" x2="100" y2="90" stroke="rgba(212,175,55,0.35)" stroke-width="1" stroke-dasharray="3 3"/>
            <line x1="250" y1="250" x2="400" y2="90" stroke="rgba(212,175,55,0.35)" stroke-width="1" stroke-dasharray="3 3"/>
            <line x1="250" y1="250" x2="70" y2="220" stroke="rgba(212,175,55,0.35)" stroke-width="1" stroke-dasharray="3 3"/>
            <line x1="250" y1="250" x2="430" y2="210" stroke="rgba(212,175,55,0.35)" stroke-width="1" stroke-dasharray="3 3"/>
            <line x1="250" y1="250" x2="95" y2="380" stroke="rgba(212,175,55,0.35)" stroke-width="1" stroke-dasharray="3 3"/>
            <line x1="250" y1="250" x2="410" y2="385" stroke="rgba(212,175,55,0.35)" stroke-width="1" stroke-dasharray="3 3"/>
            <line x1="250" y1="250" x2="250" y2="455" stroke="rgba(212,175,55,0.35)" stroke-width="1" stroke-dasharray="3 3"/>
          </svg>

          <!-- Central Levitating Botanical Cacao Pod -->
          <div class="wwu-cacao-nucleus" onclick="selectCollabArea('R&D & Product Innovation')">
            <svg class="wwu-pod-artwork" viewBox="0 0 140 300" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <linearGradient id="podGoldBody" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" stop-color="#4a3014"/>
                  <stop offset="25%" stop-color="#7c5324"/>
                  <stop offset="50%" stop-color="#b8860b"/>
                  <stop offset="75%" stop-color="#936427"/>
                  <stop offset="100%" stop-color="#3d240d"/>
                </linearGradient>
                <radialGradient id="podCoreGlow" cx="45%" cy="45%" r="55%">
                  <stop offset="0%" stop-color="rgba(255,230,150,0.35)"/>
                  <stop offset="60%" stop-color="rgba(212,175,55,0.12)"/>
                  <stop offset="100%" stop-color="transparent"/>
                </radialGradient>
              </defs>

              <!-- Golden Stem & Leaf -->
              <path d="M70 2 C67 12, 64 28, 66 46 C68 56, 70 60, 70 64" stroke="#d4af37" stroke-width="3" fill="none" stroke-linecap="round"/>
              <path d="M68 22 C60 14, 50 16, 48 24 C50 26, 60 26, 68 22Z" fill="#3a5a30" stroke="#d4af37" stroke-width="0.8" opacity="0.7"/>

              <!-- Outer Cacao Pod Shell -->
              <path d="M70 55 C34 78, 18 128, 22 178 C24 220, 40 262, 70 294 C100 262, 116 220, 118 178 C122 128, 106 78, 70 55Z" fill="url(#podGoldBody)" stroke="#d4af37" stroke-width="1.6"/>

              <!-- Ambient Highlight Oval -->
              <ellipse cx="52" cy="168" rx="16" ry="74" fill="url(#podCoreGlow)" transform="rotate(-4 52 168)"/>

              <!-- Embossed Seed Ridges with Gilded Lines -->
              <path d="M70 60 C68 125, 68 220, 70 288" stroke="#f5e4b2" stroke-width="1.4" fill="none" opacity="0.85"/>
              <path d="M70 60 C52 94, 34 152, 32 190 C31 218, 41 256, 70 288" stroke="#d4af37" stroke-width="1.1" fill="none" opacity="0.65"/>
              <path d="M70 60 C88 94, 106 152, 108 190 C109 218, 99 256, 70 288" stroke="#d4af37" stroke-width="1.1" fill="none" opacity="0.65"/>
              <path d="M70 60 C44 90, 24 142, 23 180 C22 212, 36 262, 70 288" stroke="#b8860b" stroke-width="0.9" fill="none" opacity="0.45"/>
              <path d="M70 60 C96 90, 116 142, 117 180 C118 212, 104 262, 70 288" stroke="#b8860b" stroke-width="0.9" fill="none" opacity="0.45"/>

              <!-- Golden Micro Seed Pores -->
              <circle cx="48" cy="135" r="1.5" fill="#f5e4b2" opacity="0.6"/>
              <circle cx="84" cy="155" r="1.5" fill="#f5e4b2" opacity="0.5"/>
              <circle cx="58" cy="225" r="1.5" fill="#f5e4b2" opacity="0.5"/>
              <circle cx="80" cy="115" r="1.2" fill="#f5e4b2" opacity="0.5"/>
            </svg>
          </div>

          <!-- 7 Satellite Science Nodes -->
          <!-- 1. INGREDIENTS -->
          <div class="wwu-satellite-node node-ingredients" onclick="selectCollabArea('Ingredients & Application Lab')">
            <div class="wwu-satellite-pill">
              <span class="node-glow-dot"></span>
              <span>INGREDIENTS</span>
            </div>
            <div class="wwu-satellite-tooltip">Fats, Sugar Substitutes &amp; Botanicals</div>
          </div>

          <!-- 2. FORMULATION -->
          <div class="wwu-satellite-node node-formulation" onclick="selectCollabArea('R&D & Product Innovation')">
            <div class="wwu-satellite-pill">
              <span class="node-glow-dot"></span>
              <span>FORMULATION</span>
            </div>
            <div class="wwu-satellite-tooltip">Matrix Architecture &amp; Pilot Recipes</div>
          </div>

          <!-- 3. TEXTURE -->
          <div class="wwu-satellite-node node-texture" onclick="selectCollabArea('Chocolate Problem Solving')">
            <div class="wwu-satellite-pill">
              <span class="node-glow-dot"></span>
              <span>TEXTURE</span>
            </div>
            <div class="wwu-satellite-tooltip">Snap, Melt Curve &amp; Rheology</div>
          </div>

          <!-- 4. FLAVOUR -->
          <div class="wwu-satellite-node node-flavour" onclick="selectCollabArea('R&D & Product Innovation')">
            <div class="wwu-satellite-pill">
              <span class="node-glow-dot"></span>
              <span>FLAVOUR</span>
            </div>
            <div class="wwu-satellite-tooltip">Single-Origin Roast Profiles &amp; Notes</div>
          </div>

          <!-- 5. PROCESS -->
          <div class="wwu-satellite-node node-process" onclick="selectCollabArea('Chocolate Problem Solving')">
            <div class="wwu-satellite-pill">
              <span class="node-glow-dot"></span>
              <span>PROCESS</span>
            </div>
            <div class="wwu-satellite-tooltip">Conching, Tempering &amp; Crystallization</div>
          </div>

          <!-- 6. EXPERIMENT -->
          <div class="wwu-satellite-node node-experiment" onclick="selectCollabArea('Ingredients & Application Lab')">
            <div class="wwu-satellite-pill">
              <span class="node-glow-dot"></span>
              <span>EXPERIMENT</span>
            </div>
            <div class="wwu-satellite-tooltip">Pilot Batch Trials &amp; Stress Testing</div>
          </div>

          <!-- 7. SENSORY -->
          <div class="wwu-satellite-node node-sensory" onclick="selectCollabArea('Education & Masterclasses')">
            <div class="wwu-satellite-pill">
              <span class="node-glow-dot"></span>
              <span>SENSORY</span>
            </div>
            <div class="wwu-satellite-tooltip">Mouthfeel, Bloom Resistance &amp; Panels</div>
          </div>

        </div>
      </div>

    </div>
  </section>


  <!-- ====== 2. HOW WE COLLABORATE ====== -->
  <section class="wwu-collab">
    <!-- Botanical Cacao Sketch Illustration in Corner -->
    <img src="assets/cacao_botanical_sketch.jpg" alt="Botanical Cacao Drawing" class="wwu-collab-sketch" />

    <div class="wwu-collab-inner">
      <div class="wwu-collab-left">
        <span class="wwu-tag">HOW WE COLLABORATE</span>
        <h2>Not a predefined answer.<br>A better question.</h2>
      </div>

      <!-- Center Vertical Divider Line -->
      <div class="wwu-collab-divider"></div>

      <div class="wwu-collab-right">
        <p>Every meaningful collaboration begins somewhere different—<br>a formulation that isn't behaving, an ingredient worth investigating,<br>a product waiting to be created, or simply a question worth understanding.</p>
        <p><strong>RT Chocos</strong> brings together chocolate knowledge, experimentation,<br>formulation and practical experience to explore what comes next.</p>
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
        <p class="wwu-areas-desc">Curiosity is where breakthroughs begin. Whether you need deep scientific troubleshooting, novel ingredient testing, or formulation masterclasses, we partner with you to turn ambitious questions into commercially viable reality.</p>
        
        <ul class="wwu-areas-features">
          <li>
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <span>Custom R&amp;D Formulation &amp; Pilot Trials</span>
          </li>
          <li>
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <span>Dedicated Ingredient Behavior Lab</span>
          </li>
          <li>
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <span>Strict Confidentiality &amp; IP Protection</span>
          </li>
        </ul>

        <a href="#inquiry-form" class="wwu-btn-areas-cta">
          <span>START A PROJECT</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      </div>

      <!-- Desktop Radial Orbital Hub (Rendered on >= 880px) -->
      <div class="wwu-diagram-stage">
        
        <!-- SVG Orbit Rings, Radial Glow Rays and Connection Lines -->
        <svg class="wwu-orbital-svg" viewBox="0 0 720 640" fill="none" xmlns="http://www.w3.org/2000/svg">
          <!-- Outer faint guide ring -->
          <circle cx="360" cy="320" r="285" stroke="#d4af37" stroke-width="1" stroke-opacity="0.15" stroke-dasharray="4 8"/>
          
          <!-- Main Orbital Track (Rotating) -->
          <g class="wwu-orbit-dash">
            <circle cx="360" cy="320" r="225" stroke="#8b5a2b" stroke-width="1.5" stroke-opacity="0.25" stroke-dasharray="6 6"/>
            <!-- Orbital satellite node dots -->
            <circle cx="360" cy="95" r="4" fill="#d4af37" opacity="0.7"/>
            <circle cx="574" cy="250" r="4" fill="#d4af37" opacity="0.7"/>
            <circle cx="492" cy="502" r="4" fill="#d4af37" opacity="0.7"/>
            <circle cx="228" cy="502" r="4" fill="#d4af37" opacity="0.7"/>
            <circle cx="146" cy="250" r="4" fill="#d4af37" opacity="0.7"/>
          </g>

          <!-- Inner pulse halo ring -->
          <circle cx="360" cy="320" r="115" stroke="#d4af37" stroke-width="1" stroke-opacity="0.3" stroke-dasharray="2 4"/>

          <!-- 5 Radial Connector Spokes with Gold Gradients -->
          <defs>
            <linearGradient id="spokeGrad1" x1="360" y1="320" x2="360" y2="95" gradientUnits="userSpaceOnUse">
              <stop offset="0%" stop-color="#d4af37" stop-opacity="0.8"/>
              <stop offset="100%" stop-color="#8b5a2b" stop-opacity="0.2"/>
            </linearGradient>
            <linearGradient id="spokeGrad2" x1="360" y1="320" x2="574" y2="250" gradientUnits="userSpaceOnUse">
              <stop offset="0%" stop-color="#d4af37" stop-opacity="0.8"/>
              <stop offset="100%" stop-color="#8b5a2b" stop-opacity="0.2"/>
            </linearGradient>
            <linearGradient id="spokeGrad3" x1="360" y1="320" x2="492" y2="502" gradientUnits="userSpaceOnUse">
              <stop offset="0%" stop-color="#d4af37" stop-opacity="0.8"/>
              <stop offset="100%" stop-color="#8b5a2b" stop-opacity="0.2"/>
            </linearGradient>
            <linearGradient id="spokeGrad4" x1="360" y1="320" x2="228" y2="502" gradientUnits="userSpaceOnUse">
              <stop offset="0%" stop-color="#d4af37" stop-opacity="0.8"/>
              <stop offset="100%" stop-color="#8b5a2b" stop-opacity="0.2"/>
            </linearGradient>
            <linearGradient id="spokeGrad5" x1="360" y1="320" x2="146" y2="250" gradientUnits="userSpaceOnUse">
              <stop offset="0%" stop-color="#d4af37" stop-opacity="0.8"/>
              <stop offset="100%" stop-color="#8b5a2b" stop-opacity="0.2"/>
            </linearGradient>
          </defs>

          <!-- Spokes -->
          <line x1="360" y1="245" x2="360" y2="125" stroke="url(#spokeGrad1)" stroke-width="1.5" stroke-dasharray="3 3"/>
          <line x1="425" y1="295" x2="520" y2="260" stroke="url(#spokeGrad2)" stroke-width="1.5" stroke-dasharray="3 3"/>
          <line x1="410" y1="375" x2="470" y2="445" stroke="url(#spokeGrad3)" stroke-width="1.5" stroke-dasharray="3 3"/>
          <line x1="310" y1="375" x2="250" y2="445" stroke="url(#spokeGrad4)" stroke-width="1.5" stroke-dasharray="3 3"/>
          <line x1="295" y1="295" x2="200" y2="260" stroke="url(#spokeGrad5)" stroke-width="1.5" stroke-dasharray="3 3"/>
        </svg>

        <!-- Center Core: THE QUESTION -->
        <div class="wwu-diagram-center">
          <div class="dc-pulse-ring"></div>
          <span class="dc-the">THE NUCLEUS</span>
          <span class="dc-title">THE QUESTION</span>
          <span class="dc-sub">Curiosity is the starting point.</span>
        </div>

        <!-- Node 1: CREATE -->
        <div class="wwu-area-card card-create" onclick="selectCollabArea('R&D & Product Innovation')">
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
        <div class="wwu-area-card card-solve" onclick="selectCollabArea('Chocolate Problem Solving')">
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
        <div class="wwu-area-card card-explore" onclick="selectCollabArea('Ingredients & Application Lab')">
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
        <div class="wwu-area-card card-connect" onclick="selectCollabArea('Knowledge & Industry Collaboration')">
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
        <div class="wwu-area-card card-learn" onclick="selectCollabArea('Education & Masterclasses')">
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


  <!-- ====== 5. WHO IS THIS FOR ====== -->
  <section class="wwu-audience">
    <div class="wwu-audience-inner">
      <div class="wwu-audience-top">
        <div>
          <span class="wwu-tag">WHO IS THIS FOR?</span>
          <h2>Different disciplines.<br>One shared curiosity.</h2>
        </div>
        <div class="wwu-aud-tags">
          <span class="at">Chocolate Brands</span>
          <span class="at">Food Companies</span>
          <span class="at">Ingredient Innovators</span>
          <span class="at">Founders &amp; Startups</span>
          <span class="at">Chocolatiers</span>
          <span class="at">Manufacturers</span>
          <span class="at">Researchers</span>
          <span class="at">Restaurants &amp; Hospitality</span>
          <span class="at">Educators</span>
          <span class="at">Creators &amp; Media</span>
          <span class="at">Industry Professionals</span>
        </div>
      </div>
    </div>
  </section>


  <!-- ====== 6. WHY RT CHOCOS ====== -->
  <section class="wwu-why">
    <div class="wwu-why-inner">
      <div>
        <span class="wwu-tag">WHY RT CHOCOS?</span>
        <h2>Knowledge is only useful when it can be applied.</h2>
      </div>
      <div class="wwu-why-right">
        <div class="wwu-why-pillar">
          <h4>Experience</h4>
          <p>10+ years working with chocolate formulation, product development and experimentation across brands, formats and categories.</p>
        </div>
        <div class="wwu-why-pillar">
          <h4>R&D Mindset</h4>
          <p>Questions first. Hypotheses next. Testing before assumptions. Learning that leads to measurable improvement.</p>
        </div>
        <div class="wwu-why-pillar">
          <h4>Knowledge Platform</h4>
          <p>A space where research, education and industry knowledge meet practical application and real-world outcomes.</p>
        </div>
      </div>
    </div>
  </section>


  <!-- ====== 7. THE CHOCOLATE CHALLENGE ====== -->
  <section class="wwu-challenge">
    <!-- Botanical Watermark Sketch on bottom-left matching reference -->
    <img src="assets/cacao_botanical_sketch.jpg" alt="Botanical Sketch" class="wwu-challenge-sketch" />

    <div class="wwu-challenge-inner">
      <div class="wwu-challenge-left">
        <h2>Something isn't working?<br>Something could work better?<br>Or perhaps nobody has tried it yet.<br><strong>Bring us the question.</strong></h2>
        <p>You don't need to have the answer.<br>That's what the R&D is for.</p>
        <a href="#inquiry-form" class="wwu-btn-gold">
          <span>START A CHOCOLATE CHALLENGE</span>
          <span>→</span>
        </a>
      </div>
      <div class="wwu-ch-pillars">
        <div class="wwu-ch-pill">
          <h4>Any Challenge</h4>
          <p>Product, formulation, ingredient, process, concept or idea.</p>
        </div>
        <div class="wwu-ch-pill">
          <h4>Expert Review</h4>
          <p>We review, understand and identify the areas worth exploring.</p>
        </div>
        <div class="wwu-ch-pill">
          <h4>R&D Approach</h4>
          <p>Experimentation, analysis and validation through chocolate science.</p>
        </div>
        <div class="wwu-ch-pill">
          <h4>Clear Direction</h4>
          <p>Insights, possibilities and practical next steps for you.</p>
        </div>
      </div>
    </div>
  </section>


  <!-- ====== 8. CONSULTATION FORM ====== -->
  <section id="inquiry-form" class="wwu-form-sec">
    <div class="wwu-form-card">
      <span class="wwu-tag">GET STARTED</span>
      <h2>Start a Conversation</h2>
      <p class="wwu-form-subtitle">Tell us about your challenge, idea, or question. We'll review it and get back to you with how we can help.</p>

      <?php if ($submissionSuccess): ?>
        <div class="alert-ok">✓ Thank you! Your inquiry has been received. We will get in touch with you within 24 hours.</div>
      <?php elseif (!empty($submissionError)): ?>
        <div class="alert-err"><?php echo htmlspecialchars($submissionError); ?></div>
      <?php endif; ?>

      <form method="POST" action="work-with-us.php#inquiry-form">
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
              <select name="service">
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
            <textarea name="message" rows="5" required placeholder="What's the question you're trying to answer? Tell us about the product, formulation, or idea you're working on..."></textarea>
          </div>
          <div style="text-align:center; margin-top:8px;">
            <button type="submit" class="form-submit-btn">Submit Inquiry →</button>
          </div>
        </div>
      </form>
    </div>
  </section>

</div>

<script>
/**
 * Smoothly scrolls to the consultation form, auto-selects the chosen collaboration area,
 * and adds an ambient golden glow pulse to the select field.
 */
function selectCollabArea(areaName) {
  const formSec = document.getElementById('inquiry-form');
  if (!formSec) return;
  
  const selectElem = formSec.querySelector('select[name="service"]');
  if (selectElem) {
    for (let i = 0; i < selectElem.options.length; i++) {
      const opt = selectElem.options[i];
      if (opt.value.toLowerCase().includes(areaName.toLowerCase()) || 
          opt.text.toLowerCase().includes(areaName.toLowerCase())) {
        selectElem.selectedIndex = i;
        break;
      }
    }
    
    // Add visual spotlight pulse to the select element
    selectElem.style.transition = 'all 0.4s ease';
    selectElem.style.borderColor = '#d4af37';
    selectElem.style.boxShadow = '0 0 20px rgba(212, 175, 55, 0.6)';
    setTimeout(() => {
      selectElem.style.borderColor = '';
      selectElem.style.boxShadow = '';
    }, 2500);
  }
  
  // Smooth scroll to form
  formSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<?php
  include $pathPrefix . 'includes/footer.php';
?>
