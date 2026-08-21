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
  padding: 100px 48px 75px !important;
  background: 
    radial-gradient(circle 650px at 70% 50%, rgba(212, 175, 55, 0.15) 0%, rgba(184, 134, 11, 0.05) 50%, transparent 80%),
    radial-gradient(circle 500px at 20% 30%, rgba(26, 54, 34, 0.6) 0%, transparent 70%),
    linear-gradient(180deg, #06110a 0%, #0a1b11 50%, #07130b 100%) !important;
  overflow: hidden !important;
  min-height: calc(100vh - 75px) !important;
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
    radial-gradient(circle 2px at 20% 30%, rgba(212, 175, 55, 0.45) 0%, transparent 100%),
    radial-gradient(circle 2.5px at 75% 25%, rgba(212, 175, 55, 0.55) 0%, transparent 100%),
    radial-gradient(circle 1.5px at 40% 70%, rgba(212, 175, 55, 0.35) 0%, transparent 100%),
    radial-gradient(circle 2px at 85% 65%, rgba(212, 175, 55, 0.45) 0%, transparent 100%),
    radial-gradient(circle 2px at 15% 80%, rgba(212, 175, 55, 0.35) 0%, transparent 100%) !important;
  pointer-events: none !important;
  z-index: 1 !important;
}

/* Background animated celestial coordinate lines */
#page-wwu .wwu-hero-bg-lines {
  position: absolute !important;
  top: 0 !important;
  right: 0 !important;
  width: 70% !important;
  height: 100% !important;
  opacity: 0.22 !important;
  pointer-events: none !important;
  overflow: hidden !important;
  z-index: 1 !important;
}
#page-wwu .wwu-hero-bg-lines svg {
  width: 100% !important;
  height: 100% !important;
}

#page-wwu .wwu-hero-inner {
  max-width: 1420px !important;
  margin: 0 auto !important;
  display: grid !important;
  grid-template-columns: 1.08fr 1fr !important;
  gap: 40px !important;
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

/* Sub-headline Paragraph */
#page-wwu .wwu-hero-sublead {
  font-family: 'Inter', sans-serif !important;
  font-size: 14.5px !important;
  line-height: 1.6 !important;
  color: #b8af9c !important;
  max-width: 580px !important;
  margin-bottom: 22px !important;
  font-weight: 400 !important;
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
  padding: 13px 15px !important;
  background: rgba(255, 255, 255, 0.035) !important;
  border: 1px solid rgba(212, 175, 55, 0.16) !important;
  border-radius: 12px !important;
  cursor: pointer !important;
  backdrop-filter: blur(8px) !important;
  -webkit-backdrop-filter: blur(8px) !important;
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
  background: rgba(255, 255, 255, 0.04) !important;
  border: 1.5px solid rgba(212, 175, 55, 0.4) !important;
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
}

/* --- HERO RIGHT COLUMN: KINETIC CACAO CONSTELLATION --- */
#page-wwu .wwu-hero-visual {
  position: relative !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  min-height: 680px !important;
  width: 100% !important;
}

#page-wwu .wwu-pod-constellation {
  position: relative !important;
  width: 680px !important;
  height: 680px !important;
  max-width: 100% !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

/* Ambient Golden Energy Halo */
#page-wwu .wwu-pod-constellation::before {
  content: '' !important;
  position: absolute !important;
  top: 50% !important;
  left: 50% !important;
  transform: translate(-50%, -50%) !important;
  width: 580px !important;
  height: 580px !important;
  border-radius: 50% !important;
  background: radial-gradient(circle, rgba(212, 175, 55, 0.16) 0%, rgba(184, 134, 11, 0.08) 45%, transparent 72%) !important;
  pointer-events: none !important;
  z-index: 0 !important;
  filter: blur(28px) !important;
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
  width: 255px !important;
  height: auto !important;
  filter: drop-shadow(0 22px 50px rgba(212, 175, 55, 0.38)) drop-shadow(0 10px 28px rgba(0, 0, 0, 0.75)) !important;
  animation: wwuPodFloat 6s ease-in-out infinite alternate !important;
  transition: transform 0.4s ease, filter 0.4s ease !important;
}

#page-wwu .wwu-cacao-nucleus:hover .wwu-pod-artwork {
  transform: scale(1.06) !important;
  filter: drop-shadow(0 30px 65px rgba(212, 175, 55, 0.55)) drop-shadow(0 14px 38px rgba(0, 0, 0, 0.85)) !important;
}

@keyframes wwuPodFloat {
  0% { transform: translateY(0px) rotate(0deg); }
  100% { transform: translateY(-12px) rotate(1.5deg); }
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
  padding: 8px 16px !important;
  background: rgba(12, 26, 17, 0.9) !important;
  backdrop-filter: blur(10px) !important;
  -webkit-backdrop-filter: blur(10px) !important;
  border: 1.2px solid rgba(212, 175, 55, 0.38) !important;
  border-radius: 24px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 10.5px !important;
  font-weight: 800 !important;
  letter-spacing: 2px !important;
  text-transform: uppercase !important;
  color: #dfd3bf !important;
  white-space: nowrap !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.45) !important;
}

#page-wwu .wwu-satellite-pill .node-glow-dot {
  width: 6px !important;
  height: 6px !important;
  border-radius: 50% !important;
  background: #d4af37 !important;
  box-shadow: 0 0 8px rgba(212, 175, 55, 0.95) !important;
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

/* --- PRECISE POSITIONS FOR 7 SATELLITE NODES ON 680x680 CANVAS --- */
/* 1. INGREDIENTS (Top Left) */
#page-wwu .node-ingredients { top: 9% !important; left: 6% !important; }

/* 2. FORMULATION (Top Right) */
#page-wwu .node-formulation { top: 9% !important; right: 4% !important; }

/* 3. TEXTURE (Mid Left) */
#page-wwu .node-texture     { top: 41% !important; left: -4% !important; }

/* 4. FLAVOUR (Mid Right) */
#page-wwu .node-flavour     { top: 39% !important; right: -6% !important; }

/* 5. PROCESS (Lower Left) */
#page-wwu .node-process     { bottom: 19% !important; left: 2% !important; }

/* 6. EXPERIMENT (Lower Right) */
#page-wwu .node-experiment  { bottom: 17% !important; right: -4% !important; }

/* 7. SENSORY (Bottom Center) */
#page-wwu .node-sensory     { bottom: 4% !important; left: 50% !important; transform: translateX(-50%) !important; }


/* ============================================================
   SECTION 2: HOW WE COLLABORATE (compact luxury cream with botanical sketch)
   ============================================================ */
#page-wwu .wwu-collab {
  position: relative !important;
  padding: 48px 44px !important;
  background: #f7f3eb !important;
  border-top: 1px solid rgba(212, 175, 55, 0.22) !important;
  border-bottom: 1px solid rgba(212, 175, 55, 0.14) !important;
  overflow: hidden !important;
}

/* Botanical Sketch in background right corner fitted completely inside */
#page-wwu .wwu-collab-sketch {
  position: absolute !important;
  top: 50% !important;
  right: 55px !important;
  transform: translateY(-50%) !important;
  height: calc(100% - 20px) !important;
  max-height: 165px !important;
  width: auto !important;
  object-fit: contain !important;
  opacity: 0.92 !important;
  mix-blend-mode: multiply !important;
  pointer-events: none !important;
  z-index: 1 !important;
  filter: contrast(1.12) brightness(1.03) sepia(0.04) !important;
  transition: opacity 0.4s ease !important;
}

#page-wwu .wwu-collab-inner {
  max-width: 1260px !important;
  margin: 0 auto !important;
  display: grid !important;
  grid-template-columns: 1fr auto 1.3fr !important;
  gap: 36px !important;
  align-items: center !important;
  position: relative !important;
  z-index: 2 !important;
  padding-right: 180px !important;
}

#page-wwu .wwu-collab-left {
  max-width: 440px !important;
}

#page-wwu .wwu-collab .wwu-tag {
  color: #926926 !important;
  margin-bottom: 8px !important;
  letter-spacing: 2.2px !important;
  font-size: 10px !important;
  font-weight: 800 !important;
}

#page-wwu .wwu-collab h2 {
  font-family: 'Playfair Display', serif !important;
  font-size: clamp(25px, 2.8vw, 35px) !important;
  line-height: 1.15 !important;
  color: #1a1711 !important;
  margin: 0 !important;
  font-weight: 700 !important;
}

#page-wwu .wwu-collab h2 .collab-hl {
  color: #8b5f1a !important;
  font-style: italic !important;
}

/* Center vertical divider */
#page-wwu .wwu-collab-divider {
  width: 1.5px !important;
  background: linear-gradient(180deg, transparent, rgba(184, 134, 11, 0.4) 20%, rgba(184, 134, 11, 0.4) 80%, transparent) !important;
  align-self: stretch !important;
  min-height: 80px !important;
}

#page-wwu .wwu-collab-right {
  max-width: 540px !important;
}

#page-wwu .wwu-collab-right p {
  font-family: 'Inter', sans-serif !important;
  font-size: 13.5px !important;
  line-height: 1.65 !important;
  color: #454035 !important;
  font-weight: 400 !important;
  margin: 0 0 10px 0 !important;
}
#page-wwu .wwu-collab-right p:last-child {
  margin-bottom: 0 !important;
}
#page-wwu .wwu-collab-right strong {
  color: #1a1711 !important;
  font-weight: 700 !important;
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
  #page-wwu .wwu-hero-visual {
    min-height: 480px !important;
  }
  #page-wwu .wwu-pod-constellation {
    width: 460px !important;
    height: 460px !important;
  }
  #page-wwu .wwu-pod-artwork {
    width: 180px !important;
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
          <span>Work With RT Chocos</span>
        </div>

        <h1>Bring us<br><span class="hl-hero-gold">the question.</span></h1>

        <p class="wwu-hero-sublead">
          From precision crystal matrix stabilization to clean-label sweeteners and rapid pilot formulation — we collaborate with visionary confectioners and brands to turn complex chocolate challenges into market-defining realities.
        </p>

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
              <span>START A CONSULTATION</span>
              <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
            <a href="#collaboration-areas" class="wwu-hero-btn-outline">
              <span>EXPLORE 5 AREAS</span>
              <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
            </a>
          </div>

          <div class="wwu-hero-spec-strip">
            <span class="spec-item"><span class="spec-dot">✦</span> Strict NDA Confidentiality</span>
            <span class="spec-sep">•</span>
            <span class="spec-item"><span class="spec-dot">✦</span> Rapid Pilot Formulations</span>
            <span class="spec-sep">•</span>
            <span class="spec-item"><span class="spec-dot">✦</span> ISO-Grade Analytical Lab</span>
          </div>
        </div>
      </div>

      <!-- Right Column: Interactive Cacao Constellation -->
      <div class="wwu-hero-visual">
        <div class="wwu-radar-tag">
          <span class="radar-beacon"></span>
          <span>7 R&amp;D Vectors</span>
        </div>

        <div class="wwu-pod-constellation">
          
          <!-- SVG Orbit Tracks & Laser Spokes -->
          <svg class="wwu-constellation-svg" viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient id="podSpokeGrad" x1="250" y1="250" x2="0" y2="0" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#d4af37" stop-opacity="0.8"/>
                <stop offset="100%" stop-color="#8b6b3d" stop-opacity="0.1"/>
              </linearGradient>
            </defs>

            <!-- Outer Fine Concentric Rings & Crosshair Guides -->
            <circle cx="250" cy="250" r="242" stroke="#d4af37" stroke-width="0.6" stroke-opacity="0.12" stroke-dasharray="2 6"/>
            <circle cx="250" cy="250" r="232" stroke="#d4af37" stroke-width="1" stroke-opacity="0.22" stroke-dasharray="4 8"/>
            <circle cx="250" cy="250" r="140" stroke="#d4af37" stroke-width="0.7" stroke-opacity="0.16" stroke-dasharray="3 6"/>
            
            <!-- Rotating Dash Orbit -->
            <g class="constellation-orbit-spin">
              <circle cx="250" cy="250" r="185" stroke="#d4af37" stroke-width="1.3" stroke-opacity="0.38" stroke-dasharray="6 6"/>
              <circle cx="250" cy="65" r="4" fill="#d4af37" opacity="0.9"/>
              <circle cx="426" cy="193" r="4" fill="#d4af37" opacity="0.9"/>
              <circle cx="359" cy="399" r="4" fill="#d4af37" opacity="0.9"/>
              <circle cx="141" cy="399" r="4" fill="#d4af37" opacity="0.9"/>
              <circle cx="74" cy="193" r="4" fill="#d4af37" opacity="0.9"/>
            </g>

            <!-- Inner Pulsing Orbit -->
            <circle cx="250" cy="250" r="105" stroke="#d4af37" stroke-width="0.9" stroke-opacity="0.28" stroke-dasharray="2 4"/>

            <!-- 7 Fine Laser Spokes to Satellite Nodes -->
            <line x1="250" y1="250" x2="100" y2="90" stroke="rgba(212,175,55,0.4)" stroke-width="1.1" stroke-dasharray="3 3"/>
            <line x1="250" y1="250" x2="400" y2="90" stroke="rgba(212,175,55,0.4)" stroke-width="1.1" stroke-dasharray="3 3"/>
            <line x1="250" y1="250" x2="70" y2="220" stroke="rgba(212,175,55,0.4)" stroke-width="1.1" stroke-dasharray="3 3"/>
            <line x1="250" y1="250" x2="430" y2="210" stroke="rgba(212,175,55,0.4)" stroke-width="1.1" stroke-dasharray="3 3"/>
            <line x1="250" y1="250" x2="95" y2="380" stroke="rgba(212,175,55,0.4)" stroke-width="1.1" stroke-dasharray="3 3"/>
            <line x1="250" y1="250" x2="410" y2="385" stroke="rgba(212,175,55,0.4)" stroke-width="1.1" stroke-dasharray="3 3"/>
            <line x1="250" y1="250" x2="250" y2="455" stroke="rgba(212,175,55,0.4)" stroke-width="1.1" stroke-dasharray="3 3"/>
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
        <span class="wwu-tag">✦ HOW WE COLLABORATE</span>
        <h2>Not a predefined answer.<br><span class="collab-hl">A better question.</span></h2>
      </div>

      <!-- Center Vertical Divider Line -->
      <div class="wwu-collab-divider"></div>

      <div class="wwu-collab-right">
        <p>Every meaningful collaboration begins somewhere different — a formulation that isn't behaving, an ingredient worth investigating, a product waiting to be created, or simply a question worth understanding.</p>
        <p><strong>RT Chocos</strong> brings together deep chocolate science, experimentation, formulation R&amp;D, and hands-on pilot mastery to explore what comes next.</p>
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

        <button type="button" onclick="openConsultationModal()" class="wwu-btn-areas-cta">
          <span>START A PROJECT</span>
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
          <span class="dc-the" id="nucleus-tag">THE NUCLEUS</span>
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


  <!-- ====== LUXURY CONSULTATION MODAL POPUP ====== -->
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

<?php
  include $pathPrefix . 'includes/footer.php';
?>
