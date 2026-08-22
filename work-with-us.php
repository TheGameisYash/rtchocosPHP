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
  width: 250px !important;
  height: 440px !important;
  object-fit: contain !important;
  -webkit-mask-image: radial-gradient(ellipse 43% 76% at 50% 50%, black 40%, transparent 92%) !important;
  mask-image: radial-gradient(ellipse 43% 76% at 50% 50%, black 40%, transparent 92%) !important;
  filter: drop-shadow(0 0 40px rgba(212, 175, 55, 0.45)) drop-shadow(0 20px 50px rgba(0, 0, 0, 0.95)) !important;
  animation: wwuPodFloat 6s ease-in-out infinite alternate !important;
  transition: transform 0.4s ease, filter 0.4s ease !important;
}

#page-wwu .wwu-cacao-nucleus:hover .wwu-pod-artwork {
  transform: scale(1.05) !important;
  filter: drop-shadow(0 0 55px rgba(212, 175, 55, 0.7)) drop-shadow(0 25px 65px rgba(0, 0, 0, 1)) !important;
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
   SECTION 5: WHO IS THIS FOR (Ultra-Luxury Ivory & Warm Gold)
   ============================================================ */
#page-wwu .wwu-audience {
  position: relative !important;
  padding: 100px 48px !important;
  background: 
    radial-gradient(circle 800px at 85% 20%, rgba(212, 175, 55, 0.09) 0%, transparent 70%),
    radial-gradient(circle 600px at 15% 80%, rgba(139, 90, 43, 0.05) 0%, transparent 60%),
    linear-gradient(180deg, #f7f3eb 0%, #eee6d8 50%, #f4ede0 100%) !important;
  border-top: 1px solid rgba(212, 175, 55, 0.28) !important;
  border-bottom: 1px solid rgba(212, 175, 55, 0.2) !important;
  overflow: hidden !important;
}

#page-wwu .wwu-audience::before {
  content: '' !important;
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  background-image: 
    radial-gradient(circle 1.5px at 10% 20%, rgba(184, 134, 11, 0.2) 0%, transparent 100%),
    radial-gradient(circle 1.5px at 90% 75%, rgba(184, 134, 11, 0.25) 0%, transparent 100%),
    radial-gradient(circle 2px at 50% 90%, rgba(184, 134, 11, 0.15) 0%, transparent 100%) !important;
  pointer-events: none !important;
  z-index: 1 !important;
}

#page-wwu .wwu-audience-inner {
  max-width: 1380px !important;
  margin: 0 auto !important;
  position: relative !important;
  z-index: 2 !important;
}

#page-wwu .wwu-audience-top {
  display: grid !important;
  grid-template-columns: 1fr 1.35fr !important;
  gap: 60px !important;
  align-items: center !important;
}

#page-wwu .wwu-audience-left {
  display: flex !important;
  flex-direction: column !important;
  padding-right: 20px !important;
}

#page-wwu .wwu-audience-left .wwu-aud-tag-pill {
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  align-self: flex-start !important;
  padding: 6px 14px !important;
  background: rgba(139, 90, 43, 0.08) !important;
  border: 1px solid rgba(184, 134, 11, 0.3) !important;
  border-radius: 30px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 10.5px !important;
  font-weight: 800 !important;
  letter-spacing: 2px !important;
  text-transform: uppercase !important;
  color: #8b5a2b !important;
  margin-bottom: 18px !important;
}

#page-wwu .wwu-audience-left .wwu-aud-tag-pill .pill-dot {
  width: 6px !important;
  height: 6px !important;
  border-radius: 50% !important;
  background: #b8860b !important;
  box-shadow: 0 0 6px rgba(184, 134, 11, 0.6) !important;
}

#page-wwu .wwu-audience h2 {
  font-size: clamp(32px, 3.4vw, 48px) !important;
  color: #1a1712 !important;
  line-height: 1.12 !important;
  letter-spacing: -0.02em !important;
  margin-bottom: 18px !important;
  font-weight: 700 !important;
}

#page-wwu .wwu-audience h2 .hl-cream-gold {
  color: #8b5a2b !important;
  background: linear-gradient(135deg, #8b5a2b 0%, #c89524 50%, #6b4010 100%) !important;
  -webkit-background-clip: text !important;
  -webkit-text-fill-color: transparent !important;
  display: inline-block !important;
}

#page-wwu .wwu-audience-lead {
  font-family: 'Inter', sans-serif !important;
  font-size: 15px !important;
  line-height: 1.65 !important;
  color: #574e40 !important;
  margin-bottom: 22px !important;
  font-weight: 400 !important;
}

#page-wwu .wwu-audience-badges {
  display: flex !important;
  flex-wrap: wrap !important;
  gap: 10px !important;
  margin-bottom: 24px !important;
}

#page-wwu .wwu-aud-trait {
  display: inline-flex !important;
  align-items: center !important;
  gap: 6px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 11.5px !important;
  font-weight: 700 !important;
  letter-spacing: 0.5px !important;
  color: #6b4d1b !important;
  padding: 6px 12px !important;
  background: rgba(212, 175, 55, 0.12) !important;
  border-radius: 6px !important;
  border: 1px solid rgba(184, 134, 11, 0.22) !important;
}

#page-wwu .wwu-aud-cta-wrap {
  display: flex !important;
}

#page-wwu .wwu-aud-btn {
  display: inline-flex !important;
  align-items: center !important;
  gap: 10px !important;
  padding: 13px 26px !important;
  background: linear-gradient(135deg, #8b5a2b 0%, #b8860b 100%) !important;
  color: #ffffff !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 12px !important;
  font-weight: 700 !important;
  letter-spacing: 1.5px !important;
  text-transform: uppercase !important;
  border: none !important;
  border-radius: 8px !important;
  cursor: pointer !important;
  box-shadow: 0 4px 18px rgba(139, 90, 43, 0.25) !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

#page-wwu .wwu-aud-btn:hover {
  background: linear-gradient(135deg, #70441d 0%, #9e7307 100%) !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 8px 24px rgba(139, 90, 43, 0.35) !important;
}

#page-wwu .wwu-aud-btn svg {
  width: 14px !important;
  height: 14px !important;
  stroke: currentColor !important;
  stroke-width: 2.5 !important;
  fill: none !important;
  transition: transform 0.3s ease !important;
}

#page-wwu .wwu-aud-btn:hover svg {
  transform: translateX(3px) !important;
}

/* Right Column: Balanced Luxury Audience Grid */
#page-wwu .wwu-aud-grid {
  display: grid !important;
  grid-template-columns: repeat(2, 1fr) !important;
  gap: 12px !important;
}

#page-wwu .wwu-aud-item {
  display: flex !important;
  align-items: center !important;
  gap: 12px !important;
  padding: 13px 18px !important;
  background: #ffffff !important;
  border: 1px solid rgba(184, 134, 11, 0.22) !important;
  border-radius: 12px !important;
  box-shadow: 0 3px 12px rgba(100, 70, 20, 0.05) !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  cursor: pointer !important;
  position: relative !important;
  overflow: hidden !important;
}

#page-wwu .wwu-aud-item::before {
  content: '' !important;
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  width: 3px !important;
  height: 100% !important;
  background: linear-gradient(180deg, #d4af37 0%, #8b5a2b 100%) !important;
  opacity: 0 !important;
  transition: opacity 0.3s ease !important;
}

#page-wwu .wwu-aud-item:hover {
  transform: translateY(-2.5px) !important;
  border-color: #b8860b !important;
  background: #fffdf9 !important;
  box-shadow: 0 8px 24px rgba(139, 90, 43, 0.12), 0 0 15px rgba(212, 175, 55, 0.15) !important;
}

#page-wwu .wwu-aud-item:hover::before {
  opacity: 1 !important;
}

#page-wwu .wwu-aud-icon-wrap {
  width: 34px !important;
  height: 34px !important;
  flex-shrink: 0 !important;
  border-radius: 8px !important;
  background: rgba(212, 175, 55, 0.1) !important;
  border: 1px solid rgba(184, 134, 11, 0.2) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: #8b5a2b !important;
  transition: all 0.3s ease !important;
}

#page-wwu .wwu-aud-item:hover .wwu-aud-icon-wrap {
  background: #8b5a2b !important;
  color: #ffffff !important;
  border-color: #8b5a2b !important;
}

#page-wwu .wwu-aud-icon-wrap svg {
  width: 17px !important;
  height: 17px !important;
  stroke: currentColor !important;
  stroke-width: 1.8 !important;
  fill: none !important;
}

#page-wwu .wwu-aud-info {
  display: flex !important;
  flex-direction: column !important;
  min-width: 0 !important;
}

#page-wwu .wwu-aud-name {
  font-family: 'Inter', sans-serif !important;
  font-size: 13.5px !important;
  font-weight: 700 !important;
  color: #1a1712 !important;
  line-height: 1.25 !important;
  transition: color 0.3s ease !important;
}

#page-wwu .wwu-aud-item:hover .wwu-aud-name {
  color: #8b5a2b !important;
}

#page-wwu .wwu-aud-role {
  font-family: 'Inter', sans-serif !important;
  font-size: 11px !important;
  font-weight: 400 !important;
  color: #7a7060 !important;
  line-height: 1.3 !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}

#page-wwu .wwu-aud-arrow {
  margin-left: auto !important;
  color: #c4b89a !important;
  transition: all 0.3s ease !important;
  font-size: 12px !important;
  opacity: 0.6 !important;
}

#page-wwu .wwu-aud-item:hover .wwu-aud-arrow {
  color: #8b5a2b !important;
  transform: translateX(3px) !important;
  opacity: 1 !important;
}


/* ============================================================
   SECTION 6: WHY RT CHOCOS (Dark Luxury Editorial & Pillars)
   ============================================================ */
#page-wwu .wwu-why {
  position: relative !important;
  padding: 100px 48px 90px !important;
  background: 
    radial-gradient(circle 900px at 15% 30%, rgba(212, 175, 55, 0.08) 0%, transparent 65%),
    radial-gradient(circle 750px at 85% 70%, rgba(26, 64, 40, 0.45) 0%, transparent 60%),
    linear-gradient(180deg, #050e08 0%, #0a170f 45%, #06110a 100%) !important;
  border-top: 1px solid rgba(212, 175, 55, 0.25) !important;
  border-bottom: 1px solid rgba(212, 175, 55, 0.15) !important;
  overflow: hidden !important;
}

#page-wwu .wwu-why::before {
  content: '' !important;
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  background-image: 
    radial-gradient(circle 1.5px at 15% 25%, rgba(212, 175, 55, 0.35) 0%, transparent 100%),
    radial-gradient(circle 2px at 80% 40%, rgba(212, 175, 55, 0.3) 0%, transparent 100%),
    radial-gradient(circle 1.5px at 45% 85%, rgba(212, 175, 55, 0.25) 0%, transparent 100%) !important;
  pointer-events: none !important;
  z-index: 1 !important;
}

#page-wwu .wwu-why-inner {
  max-width: 1360px !important;
  margin: 0 auto !important;
  position: relative !important;
  z-index: 2 !important;
  display: flex !important;
  flex-direction: column !important;
  gap: 40px !important;
}

/* --- TOP SPLIT: HERO LEFT + 2x2 PILLARS RIGHT --- */
#page-wwu .wwu-why-main-grid {
  display: grid !important;
  grid-template-columns: 1fr 1.28fr !important;
  gap: 52px !important;
  align-items: stretch !important;
}

/* --- LEFT HERO COLUMN --- */
#page-wwu .wwu-why-hero-col {
  display: flex !important;
  flex-direction: column !important;
  justify-content: space-between !important;
}

#page-wwu .wwu-why-eyebrow {
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  font-family: 'Inter', sans-serif !important;
  font-size: 11px !important;
  font-weight: 800 !important;
  letter-spacing: 3px !important;
  text-transform: uppercase !important;
  color: #d4af37 !important;
  margin-bottom: 18px !important;
}

#page-wwu .wwu-why-eyebrow::after {
  content: '' !important;
  display: inline-block !important;
  width: 32px !important;
  height: 1px !important;
  background: rgba(212, 175, 55, 0.45) !important;
}

#page-wwu .wwu-why-title {
  font-family: 'Playfair Display', Georgia, serif !important;
  font-size: clamp(34px, 3.5vw, 50px) !important;
  line-height: 1.12 !important;
  color: #ffffff !important;
  margin-bottom: 20px !important;
  font-weight: 700 !important;
  letter-spacing: -0.015em !important;
}

#page-wwu .wwu-why-title .hl-why-serif {
  color: #d4af37 !important;
  background: linear-gradient(135deg, #d4af37 0%, #f6e6b5 50%, #b8860b 100%) !important;
  -webkit-background-clip: text !important;
  -webkit-text-fill-color: transparent !important;
  font-style: italic !important;
  font-weight: 700 !important;
  display: inline-block !important;
}

#page-wwu .wwu-why-manifesto {
  font-family: 'Inter', sans-serif !important;
  font-size: 11.5px !important;
  font-weight: 700 !important;
  letter-spacing: 1.8px !important;
  text-transform: uppercase !important;
  line-height: 1.75 !important;
  color: #dfd3bf !important;
  margin-bottom: 26px !important;
  max-width: 500px !important;
}

#page-wwu .wwu-why-manifesto .manifesto-hl {
  color: #d4af37 !important;
}

/* Cacao Pod Image Presentation Frame */
#page-wwu .wwu-why-pod-frame {
  position: relative !important;
  border-radius: 16px !important;
  overflow: hidden !important;
  border: 1px solid rgba(212, 175, 55, 0.25) !important;
  background: radial-gradient(circle at center, rgba(30, 20, 10, 0.6) 0%, rgba(10, 15, 10, 0.9) 100%) !important;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6), 0 0 25px rgba(212, 175, 55, 0.1) !important;
  margin-bottom: 24px !important;
  transition: transform 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease !important;
}

#page-wwu .wwu-why-pod-frame:hover {
  transform: translateY(-3px) !important;
  border-color: rgba(212, 175, 55, 0.5) !important;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.75), 0 0 35px rgba(212, 175, 55, 0.22) !important;
}

#page-wwu .wwu-why-pod-img {
  width: 100% !important;
  height: 230px !important;
  object-fit: cover !important;
  display: block !important;
  filter: contrast(1.08) brightness(0.98) !important;
  transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

#page-wwu .wwu-why-pod-frame:hover .wwu-why-pod-img {
  transform: scale(1.03) !important;
}

/* Brand Seal / Logo at bottom left */
#page-wwu .wwu-why-brand-seal {
  display: inline-flex !important;
  align-items: center !important;
  gap: 15px !important;
  padding: 10px 18px !important;
  background: rgba(255, 255, 255, 0.03) !important;
  border: 1px solid rgba(212, 175, 55, 0.2) !important;
  border-radius: 12px !important;
  align-self: flex-start !important;
  backdrop-filter: blur(8px) !important;
}

#page-wwu .seal-icon-wrap {
  width: 40px !important;
  height: 40px !important;
  border-radius: 50% !important;
  border: 1.2px solid #d4af37 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  background: rgba(212, 175, 55, 0.1) !important;
  flex-shrink: 0 !important;
  box-shadow: 0 0 12px rgba(212, 175, 55, 0.25) !important;
}

#page-wwu .seal-icon-wrap svg {
  width: 22px !important;
  height: 22px !important;
  fill: none !important;
  stroke: #d4af37 !important;
  stroke-width: 1.5 !important;
}

#page-wwu .seal-text-group {
  display: flex !important;
  flex-direction: column !important;
  gap: 2px !important;
}

#page-wwu .seal-brand {
  font-family: 'Playfair Display', serif !important;
  font-size: 15px !important;
  font-weight: 700 !important;
  letter-spacing: 2px !important;
  color: #fbf8f0 !important;
  text-transform: uppercase !important;
  line-height: 1.2 !important;
}

#page-wwu .seal-sub {
  font-family: 'Inter', sans-serif !important;
  font-size: 8.5px !important;
  font-weight: 700 !important;
  letter-spacing: 1.5px !important;
  color: #d4af37 !important;
  text-transform: uppercase !important;
}

/* --- RIGHT 2x2 PILLARS GRID WITH GOLD CROSSHAIR LINES --- */
#page-wwu .wwu-why-pillars-grid {
  display: grid !important;
  grid-template-columns: 1fr 1fr !important;
  position: relative !important;
  border: 1px solid rgba(212, 175, 55, 0.22) !important;
  border-radius: 18px !important;
  background: rgba(10, 24, 15, 0.5) !important;
  backdrop-filter: blur(12px) !important;
  -webkit-backdrop-filter: blur(12px) !important;
  overflow: hidden !important;
}

/* Pillar Card */
#page-wwu .wwu-pillar-card {
  padding: 38px 30px !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  text-align: center !important;
  position: relative !important;
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

/* Grid Crosshair Internal Divider Borders */
#page-wwu .wwu-pillar-card:nth-child(1) {
  border-right: 1px solid rgba(212, 175, 55, 0.22) !important;
  border-bottom: 1px solid rgba(212, 175, 55, 0.22) !important;
}
#page-wwu .wwu-pillar-card:nth-child(2) {
  border-bottom: 1px solid rgba(212, 175, 55, 0.22) !important;
}
#page-wwu .wwu-pillar-card:nth-child(3) {
  border-right: 1px solid rgba(212, 175, 55, 0.22) !important;
}
#page-wwu .wwu-pillar-card:nth-child(4) {
  /* bottom-right: no inner borders */
}

#page-wwu .wwu-pillar-card:hover {
  background: rgba(212, 175, 55, 0.06) !important;
}

/* Card Circular Icon */
#page-wwu .pillar-icon-wrap {
  width: 58px !important;
  height: 58px !important;
  border-radius: 50% !important;
  border: 1px solid rgba(212, 175, 55, 0.45) !important;
  background: radial-gradient(circle, rgba(212, 175, 55, 0.12) 0%, rgba(212, 175, 55, 0.02) 70%) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  margin-bottom: 14px !important;
  box-shadow: 0 0 16px rgba(212, 175, 55, 0.15) !important;
  transition: transform 0.35s ease, border-color 0.35s ease, box-shadow 0.35s ease !important;
}

#page-wwu .wwu-pillar-card:hover .pillar-icon-wrap {
  transform: scale(1.1) translateY(-2px) !important;
  border-color: #d4af37 !important;
  box-shadow: 0 0 24px rgba(212, 175, 55, 0.35) !important;
}

#page-wwu .pillar-icon-wrap svg {
  width: 28px !important;
  height: 28px !important;
  stroke: #d4af37 !important;
  fill: none !important;
  stroke-width: 1.5 !important;
  stroke-linecap: round !important;
  stroke-linejoin: round !important;
}

/* Number 01, 02, etc */
#page-wwu .pillar-num {
  font-family: 'Playfair Display', Georgia, serif !important;
  font-size: 16px !important;
  font-weight: 700 !important;
  color: #d4af37 !important;
  margin-bottom: 10px !important;
  letter-spacing: 1px !important;
  display: block !important;
}

/* Card Heading */
#page-wwu .wwu-pillar-card h3 {
  font-family: 'Inter', sans-serif !important;
  font-size: 13.5px !important;
  font-weight: 800 !important;
  letter-spacing: 2px !important;
  text-transform: uppercase !important;
  color: #ffffff !important;
  margin-bottom: 12px !important;
  line-height: 1.3 !important;
}

/* Card Description */
#page-wwu .wwu-pillar-card p {
  font-family: 'Inter', sans-serif !important;
  font-size: 12.5px !important;
  line-height: 1.65 !important;
  color: #b8af9c !important;
  font-weight: 400 !important;
  margin-bottom: 18px !important;
  max-width: 270px !important;
}

/* Card Tagline / Punchline */
#page-wwu .pillar-tagline {
  font-family: 'Inter', sans-serif !important;
  font-size: 9.5px !important;
  font-weight: 800 !important;
  letter-spacing: 1.5px !important;
  text-transform: uppercase !important;
  color: #d4af37 !important;
  margin-top: auto !important;
  line-height: 1.4 !important;
  opacity: 0.95 !important;
}

/* --- BOTTOM FRAMED BANNER STRIP --- */
#page-wwu .wwu-why-banner-strip {
  border: 1px solid rgba(212, 175, 55, 0.45) !important;
  border-radius: 16px !important;
  background: 
    linear-gradient(135deg, rgba(212, 175, 55, 0.07) 0%, rgba(10, 24, 15, 0.85) 50%, rgba(212, 175, 55, 0.05) 100%) !important;
  backdrop-filter: blur(12px) !important;
  -webkit-backdrop-filter: blur(12px) !important;
  padding: 22px 32px !important;
  display: grid !important;
  grid-template-columns: 1.15fr 1.15fr 1fr !important;
  gap: 24px !important;
  align-items: center !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35), 0 0 20px rgba(212, 175, 55, 0.08) !important;
  transition: border-color 0.3s ease, box-shadow 0.3s ease !important;
}

#page-wwu .wwu-why-banner-strip:hover {
  border-color: rgba(212, 175, 55, 0.7) !important;
  box-shadow: 0 12px 36px rgba(0, 0, 0, 0.5), 0 0 28px rgba(212, 175, 55, 0.16) !important;
}

/* Banner Item Columns */
#page-wwu .banner-item {
  display: flex !important;
  align-items: center !important;
  gap: 16px !important;
}

#page-wwu .banner-icon-wrap {
  width: 40px !important;
  height: 40px !important;
  border-radius: 10px !important;
  background: rgba(212, 175, 55, 0.1) !important;
  border: 1px solid rgba(212, 175, 55, 0.3) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  flex-shrink: 0 !important;
  color: #d4af37 !important;
}

#page-wwu .banner-icon-wrap svg {
  width: 20px !important;
  height: 20px !important;
  stroke: currentColor !important;
  fill: none !important;
  stroke-width: 1.8 !important;
}

#page-wwu .banner-text {
  display: flex !important;
  flex-direction: column !important;
  gap: 3px !important;
}

#page-wwu .banner-title {
  font-family: 'Playfair Display', Georgia, serif !important;
  font-size: 14.5px !important;
  font-weight: 600 !important;
  color: #fcf9f2 !important;
  line-height: 1.3 !important;
}

#page-wwu .banner-sub {
  font-family: 'Inter', sans-serif !important;
  font-size: 12px !important;
  color: #b8af9c !important;
  line-height: 1.35 !important;
}

#page-wwu .gold-accent {
  color: #d4af37 !important;
  font-weight: 600 !important;
}

/* Banner Third Column (CTA & Action) */
#page-wwu .banner-cta-col {
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  padding-left: 20px !important;
  border-left: 1px solid rgba(212, 175, 55, 0.25) !important;
  cursor: pointer !important;
  transition: transform 0.3s ease !important;
}

#page-wwu .banner-cta-col:hover {
  transform: translateX(4px) !important;
}

#page-wwu .banner-punch {
  display: flex !important;
  flex-direction: column !important;
}

#page-wwu .punch-sub {
  font-family: 'Playfair Display', Georgia, serif !important;
  font-size: 14px !important;
  font-style: italic !important;
  color: #e8dcc8 !important;
  line-height: 1.25 !important;
}

#page-wwu .punch-main {
  font-family: 'Playfair Display', Georgia, serif !important;
  font-size: 19px !important;
  font-weight: 700 !important;
  color: #d4af37 !important;
  background: linear-gradient(135deg, #d4af37 0%, #f7e7b7 50%, #c18e11 100%) !important;
  -webkit-background-clip: text !important;
  -webkit-text-fill-color: transparent !important;
  line-height: 1.2 !important;
}

#page-wwu .banner-arrow-btn {
  width: 36px !important;
  height: 36px !important;
  border-radius: 50% !important;
  background: rgba(212, 175, 55, 0.12) !important;
  border: 1px solid rgba(212, 175, 55, 0.4) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: #d4af37 !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  flex-shrink: 0 !important;
}

#page-wwu .banner-cta-col:hover .banner-arrow-btn {
  background: #d4af37 !important;
  color: #0c1a11 !important;
  transform: translateX(4px) !important;
  box-shadow: 0 0 16px rgba(212, 175, 55, 0.5) !important;
}

#page-wwu .banner-arrow-btn svg {
  width: 17px !important;
  height: 17px !important;
  stroke: currentColor !important;
  fill: none !important;
  stroke-width: 2 !important;
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
  #page-wwu .wwu-why-main-grid {
    grid-template-columns: 1fr !important;
    gap: 40px !important;
  }
  #page-wwu .wwu-why-banner-strip {
    grid-template-columns: 1fr 1fr !important;
  }
  #page-wwu .banner-cta-col {
    grid-column: span 2 !important;
    border-left: none !important;
    border-top: 1px solid rgba(212, 175, 55, 0.25) !important;
    padding-left: 0 !important;
    padding-top: 16px !important;
  }
  #page-wwu .wwu-audience-left {
    padding-right: 0 !important;
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
  #page-wwu .wwu-aud-grid {
    grid-template-columns: 1fr !important;
  }
  #page-wwu .wwu-why-pillars-grid {
    grid-template-columns: 1fr !important;
  }
  #page-wwu .wwu-pillar-card:nth-child(1),
  #page-wwu .wwu-pillar-card:nth-child(2),
  #page-wwu .wwu-pillar-card:nth-child(3) {
    border-right: none !important;
    border-bottom: 1px solid rgba(212, 175, 55, 0.22) !important;
  }
  #page-wwu .wwu-pillar-card {
    padding: 30px 20px !important;
  }
  #page-wwu .wwu-why-banner-strip {
    grid-template-columns: 1fr !important;
    gap: 18px !important;
    padding: 18px !important;
  }
  #page-wwu .banner-cta-col {
    grid-column: span 1 !important;
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

          <!-- Central Levitating Real Botanical Cacao Pod -->
          <div class="wwu-cacao-nucleus" onclick="openConsultationModal('R&D & Product Innovation')">
            <img src="assets/real_cacao_pod.jpg" alt="Natural Ripe Chocolate Cacao Pod" class="wwu-pod-artwork wwu-real-pod" />
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
   

    <div class="wwu-collab-inner">
      <div class="wwu-collab-left">
        <span class="wwu-tag">HOW WE COLLABORATE</span>
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


  <!-- ====== 5. WHO IS THIS FOR ====== -->
  <section class="wwu-audience">
    <div class="wwu-audience-inner">
      <div class="wwu-audience-top">
        <div class="wwu-audience-left">
          <div class="wwu-aud-tag-pill">
            <span class="pill-dot"></span>
            <span>WHO IS THIS FOR?</span>
          </div>
          <h2>Different disciplines.<br><span class="hl-cream-gold">One shared curiosity.</span></h2>
          <p class="wwu-audience-lead">
            Whether you are crafting single-origin micro-batches, scaling industrial confectionery lines, developing novel functional ingredients, or researching cocoa physics — we provide the formulation depth and scientific framework to accelerate your goals.
          </p>
          <div class="wwu-audience-badges">
            <span class="wwu-aud-trait">✦ 11+ Sectors Supported</span>
            <span class="wwu-aud-trait">✦ Artisan to Industrial Scale</span>
          </div>
          <div class="wwu-aud-cta-wrap">
            <button type="button" onclick="openConsultationModal()" class="wwu-aud-btn">
              <span>EXPLORE COLLABORATION</span>
              <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
          </div>
        </div>

        <div class="wwu-aud-grid">
          <!-- Item 1 -->
          <div class="wwu-aud-item" onclick="openConsultationModal('Chocolate Brands')">
            <div class="wwu-aud-icon-wrap">
              <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            </div>
            <div class="wwu-aud-info">
              <span class="wwu-aud-name">Chocolate Brands</span>
              <span class="wwu-aud-role">Artisan &amp; commercial chocolate makers</span>
            </div>
            <span class="wwu-aud-arrow">→</span>
          </div>

          <!-- Item 2 -->
          <div class="wwu-aud-item" onclick="openConsultationModal('Food Companies')">
            <div class="wwu-aud-icon-wrap">
              <svg viewBox="0 0 24 24"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
            </div>
            <div class="wwu-aud-info">
              <span class="wwu-aud-name">Food Companies</span>
              <span class="wwu-aud-role">FMCG &amp; packaged food innovators</span>
            </div>
            <span class="wwu-aud-arrow">→</span>
          </div>

          <!-- Item 3 -->
          <div class="wwu-aud-item" onclick="openConsultationModal('Ingredient Innovators')">
            <div class="wwu-aud-icon-wrap">
              <svg viewBox="0 0 24 24"><path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/><line x1="8.5" y1="2" x2="15.5" y2="2"/></svg>
            </div>
            <div class="wwu-aud-info">
              <span class="wwu-aud-name">Ingredient Innovators</span>
              <span class="wwu-aud-role">Fats, botanicals &amp; sugar alternatives</span>
            </div>
            <span class="wwu-aud-arrow">→</span>
          </div>

          <!-- Item 4 -->
          <div class="wwu-aud-item" onclick="openConsultationModal('Founders & Startups')">
            <div class="wwu-aud-icon-wrap">
              <svg viewBox="0 0 24 24"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/></svg>
            </div>
            <div class="wwu-aud-info">
              <span class="wwu-aud-name">Founders &amp; Startups</span>
              <span class="wwu-aud-role">Emerging D2C &amp; confectionery ventures</span>
            </div>
            <span class="wwu-aud-arrow">→</span>
          </div>

          <!-- Item 5 -->
          <div class="wwu-aud-item" onclick="openConsultationModal('Chocolatiers')">
            <div class="wwu-aud-icon-wrap">
              <svg viewBox="0 0 24 24"><path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/><line x1="6" y1="17" x2="18" y2="17"/></svg>
            </div>
            <div class="wwu-aud-info">
              <span class="wwu-aud-name">Chocolatiers</span>
              <span class="wwu-aud-role">Bean-to-bar masters &amp; pastry chefs</span>
            </div>
            <span class="wwu-aud-arrow">→</span>
          </div>

          <!-- Item 6 -->
          <div class="wwu-aud-item" onclick="openConsultationModal('Manufacturers')">
            <div class="wwu-aud-icon-wrap">
              <svg viewBox="0 0 24 24"><path d="m2 20 6-6V8l6-6v6l6-6v18H2z"/></svg>
            </div>
            <div class="wwu-aud-info">
              <span class="wwu-aud-name">Manufacturers</span>
              <span class="wwu-aud-role">Industrial processors &amp; co-packers</span>
            </div>
            <span class="wwu-aud-arrow">→</span>
          </div>

          <!-- Item 7 -->
          <div class="wwu-aud-item" onclick="openConsultationModal('Researchers')">
            <div class="wwu-aud-icon-wrap">
              <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            </div>
            <div class="wwu-aud-info">
              <span class="wwu-aud-name">Researchers</span>
              <span class="wwu-aud-role">Food scientists &amp; confectionery labs</span>
            </div>
            <span class="wwu-aud-arrow">→</span>
          </div>

          <!-- Item 8 -->
          <div class="wwu-aud-item" onclick="openConsultationModal('Restaurants & Hospitality')">
            <div class="wwu-aud-icon-wrap">
              <svg viewBox="0 0 24 24"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
            </div>
            <div class="wwu-aud-info">
              <span class="wwu-aud-name">Restaurants &amp; Hospitality</span>
              <span class="wwu-aud-role">Luxury dining &amp; hotel culinary teams</span>
            </div>
            <span class="wwu-aud-arrow">→</span>
          </div>

          <!-- Item 9 -->
          <div class="wwu-aud-item" onclick="openConsultationModal('Educators')">
            <div class="wwu-aud-icon-wrap">
              <svg viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
            <div class="wwu-aud-info">
              <span class="wwu-aud-name">Educators</span>
              <span class="wwu-aud-role">Culinary academies &amp; technical trainers</span>
            </div>
            <span class="wwu-aud-arrow">→</span>
          </div>

          <!-- Item 10 -->
          <div class="wwu-aud-item" onclick="openConsultationModal('Creators & Media')">
            <div class="wwu-aud-icon-wrap">
              <svg viewBox="0 0 24 24"><path d="m12 8-9.04 9.06a2.82 2.82 0 1 0 3.98 3.98L16 12"/><circle cx="17" cy="7" r="5"/></svg>
            </div>
            <div class="wwu-aud-info">
              <span class="wwu-aud-name">Creators &amp; Media</span>
              <span class="wwu-aud-role">Confectionery writers &amp; journalists</span>
            </div>
            <span class="wwu-aud-arrow">→</span>
          </div>

          <!-- Item 11 -->
          <div class="wwu-aud-item" onclick="openConsultationModal('Industry Professionals')">
            <div class="wwu-aud-icon-wrap">
              <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div class="wwu-aud-info">
              <span class="wwu-aud-name">Industry Professionals</span>
              <span class="wwu-aud-role">Suppliers, QA specialists &amp; consultants</span>
            </div>
            <span class="wwu-aud-arrow">→</span>
          </div>

          <!-- Item 12: Custom Collaboration Partner -->
          <div class="wwu-aud-item" onclick="openConsultationModal('Custom Inquiry')">
            <div class="wwu-aud-icon-wrap">
              <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            </div>
            <div class="wwu-aud-info">
              <span class="wwu-aud-name">Visionary Creators</span>
              <span class="wwu-aud-role">Have a novel question? Let's talk</span>
            </div>
            <span class="wwu-aud-arrow">→</span>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- ====== 6. WHY RT CHOCOS ====== -->
  <section class="wwu-why" id="why-rt-chocos">
    <div class="wwu-why-inner">

      <!-- Top Main Grid: Left Narrative + Right 2x2 Pillars -->
      <div class="wwu-why-main-grid">

        <!-- Left Hero Narrative Column -->
        <div class="wwu-why-hero-col">
          <div class="wwu-why-hero-text">
            <span class="wwu-why-eyebrow">WHY RT CHOCOS?</span>
            <h2 class="wwu-why-title">
              We just don't<br>
              work with chocolate,<br>
              <span class="hl-why-serif">we understand it.</span>
            </h2>
            <p class="wwu-why-manifesto">
              WE BRING TOGETHER EXPERIENCE, SCIENCE AND CURIOSITY TO HELP YOU CREATE BETTER CHOCOLATE—WITH <span class="manifesto-hl">CLARITY AND CONFIDENCE.</span>
            </p>
          </div>

          <!-- Real Cacao Pod on Slate Artwork Frame -->
          <div class="wwu-why-pod-frame">
            <img src="assets/why_rt_cacao_pod.jpg" alt="RT Chocos Artisan Cacao Pod, Roasted Nibs & Dark Chocolate" class="wwu-why-pod-img" />
          </div>

          
        </div>

        <!-- Right 2x2 Pillars Grid -->
        <div class="wwu-why-pillars-grid">

          <!-- Pillar 01: Deep Expertise -->
          <div class="wwu-pillar-card">
            <div class="pillar-icon-wrap">
              <!-- Botanical Cacao Fruit SVG -->
              <svg viewBox="0 0 32 32">
                <path d="M16 5 C12 8, 8 13, 8 18 C8 23, 12 27, 16 27 C20 27, 24 23, 24 18 C24 13, 20 8, 16 5 Z"/>
                <path d="M16 5 C13.5 8.5, 12 13, 12 18 C12 23, 13.5 26.5, 16 27"/>
                <path d="M16 5 C18.5 8.5, 20 13, 20 18 C20 23, 18.5 26.5, 16 27"/>
                <path d="M16 5 C16 3, 17 2, 19 2"/>
                <path d="M22 6 C26 8, 27 12, 25 14 C23 16, 21 14, 20 11"/>
              </svg>
            </div>
            <span class="pillar-num">01</span>
            <h3>DEEP EXPERTISE</h3>
            <p>From ingredients to innovation—decades of hands-on experience across formulation, process, texture, flavour and product development.</p>
            <span class="pillar-tagline">WE KNOW CHOCOLATE. IN DETAIL.</span>
          </div>

          <!-- Pillar 02: Science First -->
          <div class="wwu-pillar-card">
            <div class="pillar-icon-wrap">
              <!-- Microscope SVG -->
              <svg viewBox="0 0 32 32">
                <path d="M16 4 L22 10 L19 13 L13 7 Z"/>
                <path d="M14 8 L10 12"/>
                <path d="M18 12 C21 15, 22 19, 21 23 L24 23 C26 23, 26 25, 26 26 L6 26 C6 25, 6 23, 8 23 L18 23"/>
                <circle cx="16" cy="18" r="3"/>
                <line x1="12" y1="20" x2="16" y2="20"/>
              </svg>
            </div>
            <span class="pillar-num">02</span>
            <h3>SCIENCE FIRST</h3>
            <p>We question, test and analyse before we recommend. Decisions backed by evidence, not assumptions.</p>
            <span class="pillar-tagline">WE SEEK THE WHY. THEN FIND THE HOW.</span>
          </div>

          <!-- Pillar 03: Real-World Impact -->
          <div class="wwu-pillar-card">
            <div class="pillar-icon-wrap">
              <!-- Chocolate Bar & Leaf SVG -->
              <svg viewBox="0 0 32 32">
                <rect x="7" y="7" width="14" height="18" rx="2"/>
                <line x1="7" y1="13" x2="21" y2="13"/>
                <line x1="7" y1="19" x2="21" y2="19"/>
                <line x1="14" y1="7" x2="14" y2="25"/>
                <path d="M21 14 C25 14, 27 18, 25 22 C23 26, 19 24, 18 21"/>
                <path d="M20 22 C22 20, 24 18, 25 16"/>
              </svg>
            </div>
            <span class="pillar-num">03</span>
            <h3>REAL-WORLD IMPACT</h3>
            <p>Knowledge means impact only when it works in the real world—practical, scalable and viable.</p>
            <span class="pillar-tagline">FROM IDEAS TO SOLUTIONS THAT WORK.</span>
          </div>

          <!-- Pillar 04: Independent Platform -->
          <div class="wwu-pillar-card">
            <div class="pillar-icon-wrap">
              <!-- Open Book SVG -->
              <svg viewBox="0 0 32 32">
                <path d="M5 8 C9 6, 13 7, 16 9 C19 7, 23 6, 27 8 L27 24 C23 22, 19 23, 16 25 C13 23, 9 22, 5 24 Z"/>
                <line x1="16" y1="9" x2="16" y2="25"/>
              </svg>
            </div>
            <span class="pillar-num">04</span>
            <h3>INDEPENDENT PLATFORM</h3>
            <p>An independent space for research, education and industry knowledge. No agenda. No bias.</p>
            <span class="pillar-tagline">NO AGENDA. JUST ADVANCEMENT.</span>
          </div>

        </div>

      </div>

      <!-- Bottom Interactive Framed Banner Strip -->
      <div class="wwu-why-banner-strip">

        <!-- Column 1: Challenge -->
        <div class="banner-item">
          <div class="banner-icon-wrap">
            <svg viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="7"/>
              <line x1="16.5" y1="16.5" x2="21" y2="21"/>
            </svg>
          </div>
          <div class="banner-text">
            <span class="banner-title">Bring us your challenge.</span>
            <span class="banner-sub">We'll explore what <strong class="gold-accent">lies beneath it.</strong></span>
          </div>
        </div>

        <!-- Column 2: Thinking -->
        <div class="banner-item">
          <div class="banner-icon-wrap">
            <svg viewBox="0 0 24 24">
              <path d="M12 22 V14"/>
              <path d="M12 14 C12 8, 6 6, 4 8 C3 12, 7 14, 12 14 Z"/>
              <path d="M12 14 C12 8, 18 6, 20 8 C21 12, 17 14, 12 14 Z"/>
              <path d="M12 10 C12 4, 8 2, 7 4 C6 7, 9 9, 12 10 Z"/>
            </svg>
          </div>
          <div class="banner-text">
            <span class="banner-title">Your challenge may be specific.</span>
            <span class="banner-sub">Our thinking <strong class="gold-accent">doesn't have to be.</strong></span>
          </div>
        </div>

        <!-- Column 3: Interactive Punchline CTA -->
        <div class="banner-cta-col" onclick="openConsultationModal('General Inquiry')" title="Start a conversation with RT Chocos">
          <div class="banner-punch">
            <span class="punch-sub">Better understanding.</span>
            <span class="punch-main">Better chocolate.</span>
          </div>
          <div class="banner-arrow-btn">
            <svg viewBox="0 0 24 24">
              <line x1="5" y1="12" x2="19" y2="12"/>
              <polyline points="12 5 19 12 12 19"/>
            </svg>
          </div>
        </div>

      </div>

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

<?php
  include $pathPrefix . 'includes/footer.php';
?>
