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
/* ==========================================================================
   WORK WITH US — NON-LINEAR DYNAMIC UI/UX KNOWLEDGE PLATFORM
   Built with RT Chocos Luxury Theme System & Fluid Responsive Geometry
   ========================================================================== */

#page-wwu {
  background-color: var(--bg-primary);
  color: var(--text-primary);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  overflow-x: hidden;
  transition: background-color 0.4s ease, color 0.4s ease;
  padding-top: 76px;
}

#page-wwu *,
#page-wwu *::before,
#page-wwu *::after {
  box-sizing: border-box;
}

/* Typography Harmony */
#page-wwu h1,
#page-wwu h2,
#page-wwu h3,
#page-wwu h4 {
  font-family: 'Playfair Display', Georgia, serif;
  color: var(--text-heading);
  letter-spacing: -0.015em;
  font-weight: 700;
  line-height: 1.22;
}

#page-wwu .hl-hero-gold {
  font-style: italic;
  color: var(--accent);
  font-weight: 600;
  display: inline-block;
}

#page-wwu .hl-gold {
  color: var(--accent);
  font-style: italic;
}

/* Base Wrapper */
.wwu-wrap {
  max-width: 1260px;
  margin: 0 auto;
  padding: 0 28px;
}

/* Section Header Shared Standards */
.wwu-sec-header-center {
  text-align: center;
  max-width: 720px;
  margin: 0 auto 52px;
}

.wwu-tag-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 18px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 2.2px;
  text-transform: uppercase;
  background: var(--accent-glow);
  color: var(--accent);
  border: 1px solid var(--border-accent);
  border-radius: 50px;
  margin-bottom: 16px;
}

.wwu-tag-badge .dot-beacon {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 0 8px var(--accent);
  animation: wwuDotPulse 2s infinite ease-in-out;
}

@keyframes wwuDotPulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.45; transform: scale(0.85); }
}

.wwu-sec-title-h2 {
  font-size: clamp(28px, 3.6vw, 42px);
  margin-bottom: 14px;
}

.wwu-sec-desc-lead {
  font-size: 15.5px;
  line-height: 1.65;
  color: var(--text-secondary);
}

.wwu-gold-dash-center {
  width: 48px;
  height: 2px;
  background: var(--accent);
  margin: 16px auto 0;
}

/* ==========================================================================
   1. HERO SECTION (Dynamic Split Hero: Editorial Narrative + Question Lab)
   ========================================================================== */
.wwu-hero {
  position: relative;
  min-height: calc(100vh - 76px);
  display: flex;
  align-items: center;
  overflow: hidden;
  padding: 70px 0 65px;
  background: #09160e;
}

/* Video Background with Cinema Vignette */
.wwu-hero-bg-video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center right;
  z-index: 1;
  pointer-events: none;
  opacity: 0.88;
}

.wwu-hero-video-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    95deg,
    rgba(7, 18, 11, 0.97) 0%,
    rgba(7, 18, 11, 0.90) 50%,
    rgba(7, 18, 11, 0.65) 82%,
    rgba(7, 18, 11, 0.85) 100%
  );
  z-index: 2;
  pointer-events: none;
}

.wwu-hero-inner {
  position: relative;
  z-index: 3;
  width: 100%;
}

.wwu-hero-split-grid {
  display: grid;
  grid-template-columns: 1.08fr 0.92fr;
  gap: 52px;
  align-items: center;
}

.wwu-hero-text h1 {
  font-size: clamp(40px, 5vw, 62px);
  line-height: 1.1;
  color: #FFFFFF;
  margin-bottom: 22px;
  text-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}

.wwu-hero-sublead {
  font-size: clamp(16px, 1.8vw, 18px);
  line-height: 1.7;
  color: rgba(247, 242, 232, 0.88);
  margin-bottom: 34px;
  max-width: 580px;
}

/* Action Buttons & Trust Strip */
.wwu-hero-actions-wrap {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.wwu-hero-actions {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.wwu-hero-btn-gold {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 15px 34px;
  background: linear-gradient(135deg, var(--accent), var(--accent-dark));
  color: #FFFFFF !important;
  font-size: 12.5px;
  font-weight: 700;
  letter-spacing: 1.4px;
  text-transform: uppercase;
  border-radius: 50px;
  border: none;
  cursor: pointer;
  box-shadow: 0 6px 24px var(--accent-glow);
  transition: all 0.3s ease;
  text-decoration: none;
}

.wwu-hero-btn-gold:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 32px var(--accent-glow);
  filter: brightness(1.1);
  color: #FFFFFF !important;
}

.wwu-hero-btn-gold svg {
  width: 15px;
  height: 15px;
  stroke: currentColor;
  stroke-width: 2.2;
}

.wwu-hero-btn-outline {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 14px 30px;
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  color: #FFFFFF !important;
  font-size: 12.5px;
  font-weight: 700;
  letter-spacing: 1.4px;
  text-transform: uppercase;
  border-radius: 50px;
  border: 1px solid rgba(255, 255, 255, 0.22);
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
}

.wwu-hero-btn-outline:hover {
  background: rgba(255, 255, 255, 0.16);
  border-color: var(--accent);
  color: var(--accent) !important;
  transform: translateY(-3px);
}

.wwu-hero-btn-outline svg {
  width: 15px;
  height: 15px;
  stroke: currentColor;
  stroke-width: 2.2;
}

.wwu-hero-trust-strip {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  padding: 8px 20px;
  background: rgba(255, 255, 255, 0.06);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 50px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: rgba(247, 242, 232, 0.85);
  width: fit-content;
}

.wwu-hero-trust-strip .strip-sep {
  color: var(--accent);
  opacity: 0.8;
  font-size: 14px;
}

/* Right Column: 4 Interactive Question Cards with Dynamic Elevation */
.wwu-hero-deck {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.hero-pillar-card {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 18px 22px;
  background: rgba(255, 255, 255, 0.07);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 18px;
  cursor: pointer;
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  position: relative;
  overflow: hidden;
}

.hero-pillar-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: var(--accent);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.hero-pillar-card:hover {
  background: rgba(255, 255, 255, 0.12);
  border-color: var(--accent);
  transform: translateX(6px);
  box-shadow: 0 14px 34px rgba(0, 0, 0, 0.35), 0 0 20px var(--accent-glow);
}

.hero-pillar-card:hover::before {
  opacity: 1;
}

.hero-card-icon-badge {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform 0.3s ease;
}

.hero-pillar-card:hover .hero-card-icon-badge {
  transform: scale(1.1);
}

.hero-card-icon-badge svg {
  width: 22px;
  height: 22px;
}

.badge-cacao {
  background: rgba(184, 134, 11, 0.22);
  color: #e5c158;
  border: 1px solid rgba(184, 134, 11, 0.4);
}

.badge-ingredients {
  background: rgba(46, 204, 113, 0.22);
  color: #5cdb95;
  border: 1px solid rgba(46, 204, 113, 0.4);
}

.badge-stability {
  background: rgba(52, 152, 219, 0.22);
  color: #74b9ff;
  border: 1px solid rgba(52, 152, 219, 0.4);
}

.badge-innovation {
  background: rgba(241, 196, 15, 0.22);
  color: #ffeaa7;
  border: 1px solid rgba(241, 196, 15, 0.4);
}

.hero-card-body {
  flex: 1;
}

.hero-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 4px;
}

.card-icon-tag {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 1.4px;
  text-transform: uppercase;
  color: rgba(247, 242, 232, 0.75);
}

.card-arrow {
  color: rgba(255, 255, 255, 0.5);
  font-size: 14px;
  transition: transform 0.25s ease, color 0.25s ease;
}

.hero-pillar-card:hover .card-arrow {
  color: var(--accent);
  transform: translateX(4px);
}

.hero-card-body strong {
  display: block;
  font-size: 14.5px;
  font-weight: 700;
  color: #FFFFFF;
  margin-bottom: 4px;
}

.hero-card-body p {
  font-size: 12.5px;
  line-height: 1.45;
  color: rgba(247, 242, 232, 0.78);
  margin: 0;
}

/* Video Control Pill */
.wwu-hero-video-bar {
  position: absolute;
  bottom: 24px;
  right: 28px;
  display: flex;
  align-items: center;
  gap: 12px;
  z-index: 4;
}

.wwu-video-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  background: rgba(12, 26, 17, 0.85);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 50px;
  color: #F7F2E8;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
}

.radar-beacon {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #2ecc71;
  box-shadow: 0 0 8px #2ecc71;
  animation: wwuDotPulse 2s infinite;
}

.wwu-video-ctrls {
  display: flex;
  gap: 6px;
}

.wwu-video-ctrl-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(12, 26, 17, 0.85);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.18);
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.25s ease;
}

.wwu-video-ctrl-btn:hover {
  background: var(--accent);
  color: #FFFFFF;
  transform: scale(1.08);
}

/* ==========================================================================
   2. HOW WE COLLABORATE (Interactive Connected Journey Stepper)
   ========================================================================== */
.wwu-collab-section {
  padding: 90px 0 95px;
  background: var(--bg-surface);
  border-top: 1px solid var(--border-subtle);
  border-bottom: 1px solid var(--border-subtle);
}

.wwu-collab-intro-box {
  text-align: center;
  max-width: 720px;
  margin: 0 auto 52px;
}

.wwu-collab-tag {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 2.2px;
  text-transform: uppercase;
  color: var(--accent);
  display: block;
  margin-bottom: 12px;
}

.wwu-collab-heading {
  font-size: clamp(30px, 3.8vw, 44px);
  line-height: 1.18;
  margin-bottom: 16px;
}

.wwu-collab-gold-dash {
  width: 50px;
  height: 2px;
  background: var(--accent);
  margin: 14px auto 18px;
}

.wwu-collab-subtext {
  font-size: 16px;
  line-height: 1.65;
  color: var(--text-secondary);
}

/* ==========================================================================
   2. HOW WE COLLABORATE (Interactive Connected Journey Stepper + Milestone Dossier)
   ========================================================================== */
.wwu-journey-track {
  position: relative;
  margin-bottom: 30px;
}

.wwu-journey-line {
  position: absolute;
  top: 36px;
  left: 6%;
  right: 6%;
  height: 2px;
  background: var(--border-accent);
  z-index: 1;
}

.wwu-journey-line-fill {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  width: 16.6%;
  background: linear-gradient(90deg, var(--accent), var(--accent-light));
  transition: width 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 0 10px var(--accent-glow);
}

.wwu-process-steps {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 14px;
  position: relative;
  z-index: 2;
}

.wwu-step-node {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  position: relative;
  cursor: pointer;
  padding: 12px 8px;
  border-radius: 18px;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  border: 1px solid transparent;
}

.wwu-step-node:hover,
.wwu-step-node.is-active {
  background: var(--bg-card);
  border-color: var(--border-accent);
  box-shadow: var(--shadow-sm);
  transform: translateY(-3px);
}

.wwu-step-num {
  font-size: 11px;
  font-weight: 800;
  color: var(--accent);
  letter-spacing: 1.5px;
  margin-bottom: 8px;
}

.wwu-step-badge {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--bg-card);
  border: 1.5px solid var(--border-accent);
  color: var(--accent);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
  box-shadow: var(--shadow-sm);
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.wwu-step-node:hover .wwu-step-badge,
.wwu-step-node.is-active .wwu-step-badge {
  background: var(--accent);
  color: #FFFFFF;
  transform: scale(1.12);
  box-shadow: 0 6px 20px var(--accent-glow);
}

.wwu-step-badge svg {
  width: 22px;
  height: 22px;
}

.wwu-step-title {
  font-size: 12.5px;
  font-weight: 800;
  letter-spacing: 1.2px;
  color: var(--text-heading);
  margin-bottom: 6px;
  text-transform: uppercase;
}

.wwu-step-desc {
  font-size: 12px;
  line-height: 1.45;
  color: var(--text-muted);
  max-width: 160px;
  margin: 0;
}

/* Dynamic Milestone Dossier Card Beneath Stepper */
.wwu-milestone-dossier-wrap {
  margin-top: 24px;
}

.wwu-milestone-dossier {
  background: var(--bg-card);
  border: 1.5px solid var(--border-accent);
  border-radius: 22px;
  padding: 28px 32px;
  box-shadow: var(--shadow-md), 0 0 25px var(--accent-glow);
  position: relative;
  overflow: hidden;
  transition: all 0.35s ease;
}

.wwu-milestone-dossier::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 5px;
  height: 100%;
  background: var(--accent);
}

.wwu-dossier-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--border-subtle);
}

.wwu-dossier-phase-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--accent);
  background: var(--accent-glow);
  padding: 5px 14px;
  border-radius: 50px;
  border: 1px solid var(--border-accent);
}

.wwu-dossier-client-role {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted);
}

.wwu-dossier-content-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 28px;
  align-items: center;
}

.wwu-dossier-scope h4 {
  font-size: 18px;
  color: var(--text-heading);
  margin-bottom: 8px;
}

.wwu-dossier-scope p {
  font-size: 14px;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
}

.wwu-dossier-deliverable-card {
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: 14px;
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.wwu-dossier-del-label {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--accent);
}

.wwu-dossier-del-title {
  font-size: 13.5px;
  font-weight: 700;
  color: var(--text-heading);
}

/* ==========================================================================
   3. INTERACTIVE COLLABORATION STUDIO & SOLUTION EXPLORER
   ========================================================================== */
.wwu-studio-section {
  padding: 95px 0 105px;
  background: var(--bg-primary);
  position: relative;
}

.wwu-studio-header {
  text-align: center;
  max-width: 780px;
  margin: 0 auto 40px;
}

/* Dual-Mode Segmented Switcher */
.wwu-studio-mode-switcher {
  display: inline-flex;
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: 50px;
  padding: 4px;
  gap: 6px;
  margin-bottom: 34px;
}

.wwu-studio-mode-btn {
  padding: 9px 24px;
  border-radius: 50px;
  border: none;
  background: transparent;
  font-size: 12.5px;
  font-weight: 700;
  letter-spacing: 0.8px;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.25s ease;
}

.wwu-studio-mode-btn.is-active-mode {
  background: var(--bg-card);
  color: var(--text-heading);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-accent);
}

/* 4 Selectable Master Track Cards */
.wwu-studio-track-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 36px;
}

.wwu-studio-track-card {
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: 20px;
  padding: 26px 22px;
  box-shadow: var(--shadow-sm);
  cursor: pointer;
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}

.wwu-studio-track-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: var(--accent);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.wwu-studio-track-card:hover {
  transform: translateY(-6px);
  border-color: var(--border-accent);
  box-shadow: var(--shadow-md), 0 0 20px var(--accent-glow);
}

.wwu-studio-track-card.is-active-track {
  border-color: var(--accent);
  background: linear-gradient(180deg, var(--bg-card) 0%, var(--bg-surface) 100%);
  box-shadow: var(--shadow-md), 0 0 0 2px var(--accent);
  transform: translateY(-4px);
}

.wwu-studio-track-card.is-active-track::before {
  opacity: 1;
}

.wwu-track-icon-box {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  background: var(--accent-glow);
  color: var(--accent);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 18px;
  transition: all 0.3s ease;
}

.wwu-studio-track-card:hover .wwu-track-icon-box,
.wwu-studio-track-card.is-active-track .wwu-track-icon-box {
  background: var(--accent);
  color: #FFFFFF;
  transform: scale(1.08);
}

.wwu-track-icon-box svg {
  width: 22px;
  height: 22px;
  stroke: currentColor;
  stroke-width: 1.8;
  fill: none;
}

.wwu-track-badge {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 1.4px;
  text-transform: uppercase;
  color: var(--accent);
  display: block;
  margin-bottom: 6px;
}

.wwu-studio-track-card h4 {
  font-size: 17px;
  margin-bottom: 6px;
  color: var(--text-heading);
}

.wwu-track-synopsis {
  font-size: 13px;
  line-height: 1.5;
  color: var(--text-secondary);
  margin-bottom: 16px;
  flex-grow: 1;
}

.wwu-track-tag-list {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  margin-top: auto;
}

.wwu-track-tag-list span {
  font-size: 10.5px;
  padding: 3px 8px;
  border-radius: 6px;
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  color: var(--text-muted);
  font-weight: 500;
}

/* Live Asymmetric Interactive Canvas */
.wwu-studio-canvas {
  background: var(--bg-card);
  border: 1.5px solid var(--border-accent);
  border-radius: 26px;
  padding: 44px 46px;
  box-shadow: var(--shadow-lg), 0 0 35px var(--accent-glow);
  display: grid;
  grid-template-columns: 1.15fr 0.85fr;
  gap: 46px;
  align-items: stretch;
  position: relative;
  overflow: hidden;
}

.wwu-canvas-left {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.wwu-canvas-focus-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1.8px;
  text-transform: uppercase;
  color: var(--accent);
  margin-bottom: 12px;
}

.wwu-canvas-title {
  font-size: clamp(24px, 2.8vw, 34px);
  line-height: 1.25;
  margin-bottom: 14px;
}

.wwu-canvas-narrative {
  font-size: 15px;
  line-height: 1.68;
  color: var(--text-secondary);
  margin-bottom: 26px;
}

/* Technical Parameters Grid */
.wwu-params-title {
  font-size: 11.5px;
  font-weight: 800;
  letter-spacing: 1.4px;
  text-transform: uppercase;
  color: var(--text-heading);
  margin-bottom: 14px;
  display: block;
}

.wwu-params-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px;
}

.wwu-param-item {
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: 12px;
  padding: 14px 16px;
}

.wwu-param-item strong {
  display: block;
  font-size: 13px;
  font-weight: 700;
  color: var(--text-heading);
  margin-bottom: 4px;
}

.wwu-param-item p {
  font-size: 12px;
  line-height: 1.45;
  color: var(--text-muted);
  margin: 0;
}

/* Right Side: Deliverables & Action */
.wwu-canvas-right {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background: var(--bg-surface);
  border: 1px solid var(--border-accent);
  border-radius: 20px;
  padding: 32px 28px;
}

.wwu-deliverables-header {
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--accent);
  margin-bottom: 18px;
  display: block;
}

.wwu-deliverables-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 28px;
}

.wwu-deliverable-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.wwu-del-check {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: var(--accent);
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 11px;
  font-weight: 800;
  margin-top: 1px;
}

.wwu-del-text strong {
  display: block;
  font-size: 13.5px;
  color: var(--text-heading);
  margin-bottom: 2px;
}

.wwu-del-text span {
  font-size: 12px;
  color: var(--text-secondary);
  line-height: 1.4;
  display: block;
}

.wwu-timeline-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: 12px;
  padding: 12px 18px;
  margin-bottom: 20px;
}

.wwu-timeline-strip .time-label {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: var(--text-muted);
}

.wwu-timeline-strip .time-val {
  font-size: 13px;
  font-weight: 800;
  color: var(--accent);
}

.wwu-canvas-cta-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 15px 28px;
  background: linear-gradient(135deg, var(--accent), var(--accent-dark));
  color: #FFFFFF !important;
  font-size: 12.5px;
  font-weight: 700;
  letter-spacing: 1.4px;
  text-transform: uppercase;
  border-radius: 50px;
  border: none;
  cursor: pointer;
  box-shadow: 0 6px 20px var(--accent-glow);
  transition: all 0.3s ease;
  width: 100%;
}

.wwu-canvas-cta-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 28px var(--accent-glow);
  filter: brightness(1.1);
}

/* ==========================================================================
   4. FIVE COLLABORATION AREAS (Radial Hub & Constellation Matrix)
   ========================================================================== */
.wwu-areas {
  padding: 95px 0 105px;
  background: var(--bg-surface);
  border-top: 1px solid var(--border-subtle);
  border-bottom: 1px solid var(--border-subtle);
}

.wwu-areas-inner {
  display: grid;
  grid-template-columns: 0.82fr 1.18fr;
  gap: 50px;
  align-items: center;
}

.wwu-areas-left {
  max-width: 460px;
}

.wwu-areas-left .wwu-tag-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 16px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  background: var(--accent-glow);
  color: var(--accent);
  border: 1px solid var(--border-accent);
  border-radius: 50px;
  margin-bottom: 18px;
}

.wwu-areas-left .pill-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 0 8px var(--accent);
}

.wwu-areas-left h2 {
  font-size: clamp(32px, 3.8vw, 46px);
  line-height: 1.15;
  margin-bottom: 18px;
}

.wwu-areas-desc {
  font-size: 16px;
  line-height: 1.65;
  color: var(--text-secondary);
  margin-bottom: 32px;
}

.wwu-btn-areas-cta {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 15px 32px;
  background: linear-gradient(135deg, var(--accent), var(--accent-dark));
  color: #FFFFFF !important;
  font-size: 12.5px;
  font-weight: 700;
  letter-spacing: 1.4px;
  text-transform: uppercase;
  border-radius: 50px;
  border: none;
  cursor: pointer;
  box-shadow: 0 6px 22px var(--accent-glow);
  transition: all 0.3s ease;
  text-decoration: none;
}

.wwu-btn-areas-cta:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 30px var(--accent-glow);
  filter: brightness(1.1);
}

/* Desktop Radial Orbital Hub Stage */
.wwu-diagram-stage {
  position: relative;
  width: 100%;
  height: 520px;
}

.wwu-orbital-svg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
}

/* Center Nucleus */
.wwu-diagram-center {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 130px;
  height: 130px;
  border-radius: 50%;
  background: var(--bg-card);
  border: 2px solid var(--border-accent);
  box-shadow: 0 0 30px var(--accent-glow);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  z-index: 3;
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  padding: 10px;
}

.dc-pulse-ring {
  position: absolute;
  inset: -8px;
  border-radius: 50%;
  border: 1px dashed var(--accent);
  opacity: 0.4;
  animation: wwuSpin 30s linear infinite;
}

@keyframes wwuSpin {
  100% { transform: rotate(360deg); }
}

.dc-title {
  font-family: 'Playfair Display', serif;
  font-size: 14px;
  font-weight: 700;
  color: var(--text-heading);
  line-height: 1.2;
}

.dc-sub {
  font-size: 10px;
  color: var(--text-muted);
  margin-top: 4px;
}

/* Orbit Node Cards */
.wwu-area-card {
  position: absolute;
  width: 220px;
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: 16px;
  padding: 16px 18px;
  box-shadow: var(--shadow-sm);
  z-index: 4;
  cursor: pointer;
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.wwu-area-card:hover {
  transform: translateY(-5px) scale(1.02);
  border-color: var(--border-accent);
  box-shadow: var(--shadow-md), 0 0 20px var(--accent-glow);
}

.wwu-area-card .card-head {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.wwu-area-card .area-icon {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  background: var(--accent-glow);
  color: var(--accent);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.wwu-area-card .area-icon svg {
  width: 17px;
  height: 17px;
  stroke: currentColor;
  stroke-width: 2;
  fill: none;
}

.wwu-area-card .area-label {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  color: var(--accent);
}

.wwu-area-card h4 {
  font-size: 14px;
  margin-bottom: 6px;
  color: var(--text-heading);
}

.wwu-area-card p {
  font-size: 11.5px;
  line-height: 1.45;
  color: var(--text-secondary);
  margin: 0 0 8px 0;
}

.wwu-area-card .card-action {
  font-size: 10.5px;
  font-weight: 700;
  color: var(--accent);
  display: block;
}

/* Node Positions in Orbital Stage */
.card-create {
  top: 10px;
  left: 50%;
  transform: translateX(-50%);
}
.card-solve {
  top: 110px;
  right: 15px;
}
.card-explore {
  bottom: 25px;
  right: 50px;
}
.card-connect {
  bottom: 25px;
  left: 50px;
}
.card-learn {
  top: 110px;
  left: 15px;
}

.wwu-mobile-areas-flow {
  display: none;
}

/* ==========================================================================
   5. WE COLLABORATE ACROSS THE CHOCOLATE ECOSYSTEM (Interactive Partner Hub)
   ========================================================================== */
.wwu-ecosystem-section {
  padding: 95px 0 100px;
  position: relative;
  overflow: hidden;
}

.wwu-ecosystem-inner {
  text-align: center;
  position: relative;
  z-index: 2;
}

.wwu-ecosystem-heading {
  font-size: clamp(24px, 3vw, 32px);
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--text-heading);
  margin-bottom: 48px;
}

.wwu-ecosystem-grid-tiered {
  display: flex;
  flex-direction: column;
  gap: 18px;
  max-width: 1140px;
  margin: 0 auto 30px;
}

.wwu-eco-tier-row {
  display: grid;
  gap: 18px;
}

.wwu-eco-tier-top {
  grid-template-columns: repeat(4, 1fr);
}

.wwu-eco-tier-bottom {
  grid-template-columns: repeat(3, 1fr);
  max-width: 860px;
  margin: 0 auto;
  width: 100%;
}

.wwu-eco-card {
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: 18px;
  padding: 24px 16px;
  box-shadow: var(--shadow-sm);
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  cursor: pointer;
  position: relative;
}

.wwu-eco-card:hover {
  transform: translateY(-6px);
  border-color: var(--border-accent);
  box-shadow: var(--shadow-md), 0 0 22px var(--accent-glow);
}

.wwu-eco-card.is-active-eco {
  border-color: var(--accent);
  background: linear-gradient(180deg, var(--bg-card) 0%, var(--bg-surface) 100%);
  box-shadow: var(--shadow-md), 0 0 0 2px var(--accent);
}

.wwu-eco-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: var(--accent-glow);
  color: var(--accent);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 14px;
  transition: all 0.3s ease;
}

.wwu-eco-card:hover .wwu-eco-icon,
.wwu-eco-card.is-active-eco .wwu-eco-icon {
  background: var(--accent);
  color: #FFFFFF;
  transform: scale(1.08);
}

.wwu-eco-icon svg {
  width: 26px;
  height: 26px;
  stroke: currentColor;
}

.wwu-eco-label {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.8px;
  text-transform: uppercase;
  color: var(--text-heading);
  line-height: 1.4;
}

/* Interactive Ecosystem Spotlight Drawer */
.wwu-eco-spotlight-drawer {
  max-width: 960px;
  margin: 0 auto 36px;
  background: var(--bg-card);
  border: 1.5px solid var(--border-accent);
  border-radius: 22px;
  padding: 30px 36px;
  box-shadow: var(--shadow-md), 0 0 25px var(--accent-glow);
  text-align: left;
  transition: all 0.35s ease;
}

.wwu-eco-drawer-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 14px;
}

.wwu-eco-drawer-badge {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--accent);
  background: var(--accent-glow);
  padding: 4px 12px;
  border-radius: 50px;
  border: 1px solid var(--border-accent);
}

.wwu-eco-drawer-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 28px;
  align-items: center;
}

.wwu-eco-drawer-desc h4 {
  font-size: 18px;
  color: var(--text-heading);
  margin-bottom: 6px;
}

.wwu-eco-drawer-desc p {
  font-size: 13.5px;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
}

.wwu-eco-drawer-action {
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: 14px;
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.wwu-eco-drawer-action span {
  font-size: 12.5px;
  color: var(--text-secondary);
}

.wwu-eco-drawer-link {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: var(--accent);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: none;
  background: transparent;
  padding: 0;
}

.wwu-eco-drawer-link:hover {
  text-decoration: underline;
}

.wwu-ecosystem-footer-note {
  font-size: 15px;
  color: var(--text-secondary);
  font-style: italic;
  margin-top: 12px;
}

.wwu-eco-sketch-bg {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 720px;
  height: auto;
  opacity: 0.04;
  pointer-events: none;
  z-index: 1;
}

/* ==========================================================================
   6. ON-PAGE CONSULTATION DESK (Direct Engagement, Mutual NDA)
   ========================================================================== */
.wwu-desk-section {
  padding: 95px 0 105px;
  background: radial-gradient(circle at 50% 15%, var(--accent-glow) 0%, transparent 68%);
}

.wwu-desk-card {
  background: var(--bg-card);
  border: 1.5px solid var(--border-accent);
  border-radius: 28px;
  padding: 52px 56px;
  box-shadow: var(--shadow-lg), 0 0 45px var(--accent-glow);
  max-width: 920px;
  margin: 0 auto;
}

.wwu-desk-header {
  text-align: center;
  max-width: 640px;
  margin: 0 auto 32px;
}

.wwu-confidential-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 16px;
  background: rgba(35, 84, 54, 0.08);
  border: 1px solid var(--border-accent);
  border-radius: 50px;
  font-size: 11.5px;
  font-weight: 600;
  color: var(--accent);
  margin-top: 12px;
}

.wwu-desk-chips-wrap {
  margin-bottom: 26px;
}

.wwu-desk-chips-label {
  display: block;
  font-size: 11.5px;
  font-weight: 700;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  color: var(--text-heading);
  margin-bottom: 12px;
}

.wwu-desk-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.wwu-desk-chip-btn {
  padding: 8px 16px;
  border-radius: 50px;
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  color: var(--text-secondary);
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s ease;
}

.wwu-desk-chip-btn:hover {
  border-color: var(--border-accent);
  color: var(--accent);
}

.wwu-desk-chip-btn.is-active-chip {
  background: var(--accent);
  color: #FFFFFF !important;
  border-color: var(--accent);
  box-shadow: 0 4px 14px var(--accent-glow);
}

/* ==========================================================================
   7. CONSULTATION MODAL (Retained & Synchronized)
   ========================================================================== */
.wwu-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(10, 22, 14, 0.82);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  z-index: 999999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transition: opacity 0.35s ease, visibility 0.35s ease;
}

.wwu-modal-backdrop.is-open {
  opacity: 1;
  visibility: visible;
  pointer-events: auto;
}

.wwu-modal-dialog {
  position: relative;
  width: 100%;
  max-width: 720px;
  max-height: 90vh;
  overflow-y: auto;
  background: var(--bg-card);
  border: 1.5px solid var(--border-accent);
  border-radius: 28px;
  padding: 44px 44px 40px;
  box-shadow: var(--shadow-lg), 0 0 50px var(--accent-glow);
  transform: scale(0.94) translateY(20px);
  transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.wwu-modal-backdrop.is-open .wwu-modal-dialog {
  transform: scale(1) translateY(0);
}

.wwu-modal-close {
  position: absolute;
  top: 20px;
  right: 20px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  color: var(--text-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.25s ease;
}

.wwu-modal-close:hover {
  background: var(--accent);
  color: #FFFFFF;
}

.wwu-form-card .wwu-tag {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--accent);
  display: block;
  margin-bottom: 8px;
}

.wwu-form-card h2 {
  font-size: 30px;
  margin-bottom: 8px;
  color: var(--text-heading);
}

.wwu-form-subtitle {
  font-size: 14.5px;
  color: var(--text-secondary);
  line-height: 1.6;
  margin-bottom: 28px;
}

.form-grid {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 18px;
}

.form-grid label {
  display: block;
  font-size: 12.5px;
  font-weight: 600;
  letter-spacing: 0.4px;
  color: var(--text-heading);
  margin-bottom: 6px;
}

.form-grid input,
.form-grid select,
.form-grid textarea {
  width: 100%;
  padding: 13px 16px;
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: 12px;
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  color: var(--text-primary);
  transition: all 0.3s ease;
  outline: none;
}

.form-grid input:focus,
.form-grid select:focus,
.form-grid textarea:focus {
  border-color: var(--accent);
  background: var(--bg-card);
  box-shadow: 0 0 0 3px var(--accent-glow);
}

.form-submit-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 15px 38px;
  background: linear-gradient(135deg, var(--accent), var(--accent-dark));
  color: #FFFFFF !important;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 1.4px;
  text-transform: uppercase;
  border-radius: 50px;
  border: none;
  cursor: pointer;
  box-shadow: 0 6px 22px var(--accent-glow);
  transition: all 0.3s ease;
  min-width: 240px;
}

.form-submit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px var(--accent-glow);
  filter: brightness(1.1);
}

.alert-ok {
  padding: 14px 18px;
  background: rgba(46, 204, 113, 0.12);
  border: 1px solid rgba(46, 204, 113, 0.35);
  color: #1e824c;
  border-radius: 12px;
  font-size: 13.5px;
  font-weight: 500;
  margin-bottom: 20px;
}

.alert-err {
  padding: 14px 18px;
  background: rgba(231, 76, 60, 0.12);
  border: 1px solid rgba(231, 76, 60, 0.35);
  color: #c0392b;
  border-radius: 12px;
  font-size: 13.5px;
  font-weight: 500;
  margin-bottom: 20px;
}

/* ==========================================================================
   RESPONSIVE GEOMETRY BREAKPOINTS
   ========================================================================== */
@media (max-width: 1200px) {
  .wwu-hero-split-grid {
    grid-template-columns: 1fr;
    gap: 40px;
  }
  .wwu-hero-deck {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
  }
  .wwu-studio-track-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .wwu-studio-canvas {
    grid-template-columns: 1fr;
    gap: 32px;
  }
  .card-solve { right: -10px; }
  .card-learn { left: -10px; }
}

@media (max-width: 960px) {
  .wwu-hero-deck {
    grid-template-columns: 1fr;
  }
  .wwu-process-steps {
    grid-template-columns: repeat(3, 1fr);
    gap: 16px 10px;
  }
  .wwu-journey-line {
    display: none;
  }
  .wwu-dossier-content-grid {
    grid-template-columns: 1fr;
    gap: 18px;
  }
  .wwu-eco-drawer-grid {
    grid-template-columns: 1fr;
    gap: 18px;
  }
  .wwu-areas-inner {
    grid-template-columns: 1fr;
    gap: 40px;
  }
  .wwu-diagram-stage {
    display: none;
  }
  .wwu-mobile-areas-flow {
    display: block;
  }
  .wwu-mob-center-badge {
    text-align: center;
    padding: 24px;
    background: var(--bg-card);
    border: 1.5px solid var(--border-accent);
    border-radius: 20px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
  }
  .wwu-mob-center-badge .mob-badge-tag {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 2px;
    color: var(--accent);
    text-transform: uppercase;
    display: block;
    margin-bottom: 6px;
  }
  .wwu-mob-center-badge h3 {
    font-size: 22px;
    margin-bottom: 4px;
  }
  .wwu-mob-center-badge span {
    font-size: 13px;
    color: var(--text-muted);
  }
  .wwu-mob-cards-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  .wwu-mob-card {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 18px;
    padding: 20px;
    cursor: pointer;
    box-shadow: var(--shadow-sm);
  }
  .wwu-mob-card:hover {
    border-color: var(--border-accent);
  }
  .wwu-mob-card .mob-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: var(--accent-glow);
    color: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .wwu-mob-card .mob-icon svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    stroke-width: 2;
    fill: none;
  }
  .wwu-mob-card .mob-tag {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.5px;
    color: var(--accent);
    text-transform: uppercase;
    display: block;
    margin-bottom: 4px;
  }
  .wwu-mob-card h4 {
    font-size: 16px;
    margin-bottom: 6px;
  }
  .wwu-mob-card p {
    font-size: 13px;
    line-height: 1.5;
    color: var(--text-secondary);
    margin: 0 0 8px 0;
  }
  .wwu-mob-card .mob-btn {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--accent);
  }
  .wwu-eco-tier-top {
    grid-template-columns: repeat(2, 1fr);
  }
  .wwu-eco-tier-bottom {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  #page-wwu {
    padding-top: 70px;
  }
  .wwu-hero {
    padding: 50px 0 50px;
  }
  .wwu-hero-text h1 {
    font-size: 34px;
  }
  .wwu-hero-sublead {
    font-size: 15px;
  }
  .wwu-hero-actions {
    flex-direction: column;
    align-items: stretch;
  }
  .wwu-hero-btn-gold,
  .wwu-hero-btn-outline {
    width: 100%;
    justify-content: center;
  }
  .wwu-process-steps {
    grid-template-columns: repeat(2, 1fr);
  }
  .wwu-studio-track-grid {
    grid-template-columns: 1fr;
  }
  .wwu-params-grid {
    grid-template-columns: 1fr;
  }
  .wwu-studio-canvas {
    padding: 28px 20px;
  }
  .wwu-milestone-dossier {
    padding: 22px 18px;
  }
  .wwu-eco-tier-top,
  .wwu-eco-tier-bottom {
    grid-template-columns: 1fr;
  }
  .wwu-desk-card {
    padding: 34px 20px;
  }
  .form-row {
    grid-template-columns: 1fr;
  }
  .wwu-modal-dialog {
    padding: 34px 20px;
  }
}
</style>

<!-- ================================================================
     WORK WITH US — PAGE CONTENT
     ================================================================ -->
<div id="page-wwu" class="page active">

  <!-- ====== 1. HERO ====== -->
  <section class="wwu-hero">
    <!-- Full-bleed background video -->
    <video class="wwu-hero-bg-video" id="wwuHeroVideo" autoplay loop muted playsinline preload="auto" poster="assets/workwithusvid_thumb.jpg">
      <source src="assets/workwithusvid.mp4" type="video/mp4">
      Your browser does not support HTML5 video.
    </video>

    <!-- Luxury Dark Gradient Vignette for perfect text contrast -->
    <div class="wwu-hero-video-overlay" aria-hidden="true"></div>

    <div class="wwu-wrap wwu-hero-inner">
      <div class="wwu-hero-split-grid">
        
        <!-- Left: Editorial Narrative -->
        <div class="wwu-hero-text">
          <div class="wwu-tag-badge" style="background:rgba(255,255,255,0.08); color:#F7F2E8; border-color:rgba(255,255,255,0.18);">
            <span class="dot-beacon" style="background:#2ecc71; box-shadow:0 0 10px #2ecc71;"></span>
            <span>Work With RT Chocos</span>
          </div>

          <h1>Bring us<br><span class="hl-hero-gold">the question.</span></h1>

          <p class="wwu-hero-sublead">From formulation challenges to new product concepts, we turn ambitious chocolate ideas into commercially viable products.</p>

          <div class="wwu-hero-actions-wrap">
            <div class="wwu-hero-actions">
              <button type="button" onclick="focusOnPageDesk('General Inquiry')" class="wwu-hero-btn-gold">
                <span>DISCUSS YOUR IDEA</span>
                <svg viewBox="0 0 24 24" fill="none"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
              </button>
              <a href="#interactive-studio" class="wwu-hero-btn-outline">
                <span>EXPLORE WHAT WE SOLVE</span>
                <svg viewBox="0 0 24 24" fill="none"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
              </a>
            </div>
            <div class="wwu-hero-trust-strip">
              <span>IDEAS</span>
              <span class="strip-sep">&bull;</span>
              <span>INGREDIENTS</span>
              <span class="strip-sep">&bull;</span>
              <span>SCIENCE</span>
              <span class="strip-sep">&bull;</span>
              <span>REAL-WORLD SOLUTIONS</span>
            </div>
          </div>
        </div>

        <!-- Right: Interactive 4 Question Pillars Deck -->
        <div class="wwu-hero-deck">
          <!-- Card 1: PRODUCT ARCHITECTURE -->
          <div class="hero-pillar-card" onclick="exploreStudioTrack('idea')">
            <div class="hero-card-icon-badge badge-cacao">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2C7.5 5 4.5 9 4.5 14c0 4.5 3 7.5 7.5 8 4.5-.5 7.5-3.5 7.5-8 0-5-3-9-7.5-12z"/>
                <path d="M12 2v20"/>
                <path d="M8.5 6.5C7 9.5 7 14 8.5 17.5"/>
                <path d="M15.5 6.5C17 9.5 17 14 15.5 17.5"/>
              </svg>
            </div>
            <div class="hero-card-body">
              <div class="hero-card-header">
                <span class="card-icon-tag">PRODUCT ARCHITECTURE</span>
                <span class="card-arrow">&rarr;</span>
              </div>
              <strong>A product to rethink.</strong>
              <p>Formulation, sensory profiling &amp; market repositioning.</p>
            </div>
          </div>

          <!-- Card 2: INGREDIENTS -->
          <div class="hero-pillar-card" onclick="exploreStudioTrack('ingredient')">
            <div class="hero-card-icon-badge badge-ingredients">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/>
                <line x1="8.5" y1="2" x2="15.5" y2="2"/>
                <line x1="7" y1="15" x2="17" y2="15" stroke-dasharray="2 2"/>
              </svg>
            </div>
            <div class="hero-card-body">
              <div class="hero-card-header">
                <span class="card-icon-tag">INGREDIENTS</span>
                <span class="card-arrow">&rarr;</span>
              </div>
              <strong>An ingredient to explore.</strong>
              <p>Novel fats, clean-label sweeteners &amp; botanicals.</p>
            </div>
          </div>

          <!-- Card 3: STABILITY -->
          <div class="hero-pillar-card" onclick="exploreStudioTrack('defect')">
            <div class="hero-card-icon-badge badge-stability">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2.5l8 4.6v9.2l-8 4.6-8-4.6V7.1z"/>
                <path d="M12 2.5v18.4"/>
                <path d="M12 11.7l8-4.6"/>
                <path d="M12 11.7l-8-4.6"/>
                <circle cx="12" cy="11.7" r="1.5" fill="currentColor"/>
              </svg>
            </div>
            <div class="hero-card-body">
              <div class="hero-card-header">
                <span class="card-icon-tag">STABILITY</span>
                <span class="card-arrow">&rarr;</span>
              </div>
              <strong>A problem to solve.</strong>
              <p>Fat bloom, viscosity drift &amp; shelf-life stabilization.</p>
            </div>
          </div>

          <!-- Card 4: INNOVATION -->
          <div class="hero-pillar-card" onclick="exploreStudioTrack('idea')">
            <div class="hero-card-icon-badge badge-innovation">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 18h6"/>
                <path d="M10 22h4"/>
                <path d="M12 2a7 7 0 0 0-7 7c0 2.5-1.3 4.7-3.5 6l1 3h6l1-3c1.8-1.3 3-3.5 3-6a7 7 0 0 0-7-7z"/>
                <path d="M12 6v3"/>
              </svg>
            </div>
            <div class="hero-card-body">
              <div class="hero-card-header">
                <span class="card-icon-tag">INNOVATION</span>
                <span class="card-arrow">&rarr;</span>
              </div>
              <strong>An idea not tried yet.</strong>
              <p>Translating blue-sky concepts into pilot lab prototypes.</p>
            </div>
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


  <!-- ====== 2. HOW WE COLLABORATE ====== -->
  <section class="wwu-collab-section" id="how-we-collaborate">
    <div class="wwu-wrap">
      
      <!-- Section Header -->
      <div class="wwu-collab-intro-box">
        <span class="wwu-collab-tag">HOW WE COLLABORATE</span>
        <h2 class="wwu-collab-heading">A thoughtful journey<br>from question to impact.</h2>
        <div class="wwu-collab-gold-dash"></div>
        <p class="wwu-collab-subtext">Every collaboration is unique, but our approach is always rooted in curiosity, chocolate science, and commercial viability.</p>
      </div>

      <!-- 6-Step Connected Milestone Stepper -->
      <div class="wwu-journey-track">
        <div class="wwu-journey-line">
          <div class="wwu-journey-line-fill" id="journeyLineFill"></div>
        </div>
        <div class="wwu-process-steps">
          
          <!-- Step 01 -->
          <div class="wwu-step-node is-active" id="stepNode1" onclick="activateJourneyStep(1)" onmouseenter="activateJourneyStep(1)">
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
          <div class="wwu-step-node" id="stepNode2" onclick="activateJourneyStep(2)" onmouseenter="activateJourneyStep(2)">
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
          <div class="wwu-step-node" id="stepNode3" onclick="activateJourneyStep(3)" onmouseenter="activateJourneyStep(3)">
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
          <div class="wwu-step-node" id="stepNode4" onclick="activateJourneyStep(4)" onmouseenter="activateJourneyStep(4)">
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
          <div class="wwu-step-node" id="stepNode5" onclick="activateJourneyStep(5)" onmouseenter="activateJourneyStep(5)">
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
          <div class="wwu-step-node" id="stepNode6" onclick="activateJourneyStep(6)" onmouseenter="activateJourneyStep(6)">
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

      <!-- Dynamic Milestone Spotlight Dossier (Non-linear Interactive Deep-Dive) -->
      <div class="wwu-milestone-dossier-wrap">
        <div class="wwu-milestone-dossier" id="journeyMilestoneDossier">
          <div class="wwu-dossier-top">
            <span class="wwu-dossier-phase-pill" id="dossierPhasePill">PHASE 01 • INITIAL ENGAGEMENT</span>
            <span class="wwu-dossier-client-role" id="dossierClientRole">Client Input: Challenge Brief &amp; Target Constraints</span>
          </div>
          <div class="wwu-dossier-content-grid">
            <div class="wwu-dossier-scope">
              <h4 id="dossierHeading">CONNECT — Understanding Your Challenge, Intent &amp; Line Conditions</h4>
              <p id="dossierScopeText">We begin with an exploratory technical dialogue and NDA execution. We map your current formulation, cocoa sourcing, production equipment, tempering limits, and commercial targets to define clear technical success criteria.</p>
            </div>
            <div class="wwu-dossier-deliverable-card">
              <span class="wwu-dossier-del-label">Phase Milestone Output</span>
              <strong class="wwu-dossier-del-title" id="dossierDeliverableTitle">Project Charter &amp; Formulation Feasibility Hypothesis</strong>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>


  <!-- ====== 3. INTERACTIVE COLLABORATION STUDIO & SOLUTION EXPLORER ====== -->
  <section class="wwu-studio-section" id="interactive-studio">
    <div class="wwu-wrap">
      
      <!-- Studio Header -->
      <div class="wwu-studio-header">
        <div class="wwu-tag-badge">
          <span class="dot-beacon"></span>
          <span>COLLABORATION STUDIO</span>
        </div>
        <h2 class="wwu-sec-title-h2">Where Ambition Meets Chocolate Science.</h2>
        <p class="wwu-sec-desc-lead">Select your starting question or engagement preference to reveal our laboratory methodology, parameters tested, and tangible project deliverables.</p>
        
        <!-- Segmented Dual-Perspective Mode Switcher -->
        <div class="wwu-studio-mode-switcher">
          <button type="button" class="wwu-studio-mode-btn is-active-mode" id="modeBtnScenario" onclick="switchStudioMode('scenario')">
            🔍 By Starting Question
          </button>
          <button type="button" class="wwu-studio-mode-btn" id="modeBtnModel" onclick="switchStudioMode('model')">
            ⚡ By Engagement Model
          </button>
        </div>
      </div>

      <!-- 4 Selectable Master Track Cards -->
      <div class="wwu-studio-track-grid">
        
        <!-- Track 1: Defect Resolution -->
        <div class="wwu-studio-track-card is-active-track" id="studioTrackCard-defect" onclick="exploreStudioTrack('defect')">
          <div class="wwu-track-icon-box">
            <svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          </div>
          <span class="wwu-track-badge" id="trackBadge-defect">DEFECT FORENSICS</span>
          <h4 id="trackTitle-defect">I Have a Problem</h4>
          <p class="wwu-track-synopsis" id="trackSynopsis-defect">Fat bloom, viscosity drift, tempering instability or shelf-life collapse.</p>
          <div class="wwu-track-tag-list" id="trackTags-defect">
            <span>Fat Bloom</span><span>Viscosity</span><span>Tempering</span><span>Shelf-Life</span><span>Texture</span>
          </div>
        </div>

        <!-- Track 2: Product Innovation -->
        <div class="wwu-studio-track-card" id="studioTrackCard-idea" onclick="exploreStudioTrack('idea')">
          <div class="wwu-track-icon-box">
            <svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.5 6-.3.2-.5.5-.5.9V17H9v-1.1c0-.4-.2-.7-.5-.9C6.3 13.7 5 11.5 5 9a7 7 0 0 1 7-7z"/><line x1="9" y1="21" x2="15" y2="21"/></svg>
          </div>
          <span class="wwu-track-badge" id="trackBadge-idea">PRODUCT ARCHITECTURE</span>
          <h4 id="trackTitle-idea">I Have an Idea</h4>
          <p class="wwu-track-synopsis" id="trackSynopsis-idea">Developing a distinctive, commercial-ready chocolate or confectionery.</p>
          <div class="wwu-track-tag-list" id="trackTags-idea">
            <span>New Formats</span><span>Clean-Label</span><span>Single-Origin</span><span>Plant-Based</span><span>Sugar Reduction</span>
          </div>
        </div>

        <!-- Track 3: Ingredients Lab -->
        <div class="wwu-studio-track-card" id="studioTrackCard-ingredient" onclick="exploreStudioTrack('ingredient')">
          <div class="wwu-track-icon-box">
            <svg viewBox="0 0 24 24"><path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/><line x1="8.5" y1="2" x2="15.5" y2="2"/></svg>
          </div>
          <span class="wwu-track-badge" id="trackBadge-ingredient">APPLICATION LAB</span>
          <h4 id="trackTitle-ingredient">I Have an Ingredient</h4>
          <p class="wwu-track-synopsis" id="trackSynopsis-ingredient">Evaluating novel fats, inclusions, alternative sweeteners, or cacao components.</p>
          <div class="wwu-track-tag-list" id="trackTags-ingredient">
            <span>Novel Fats / CBE</span><span>Low-GI Sweeteners</span><span>Botanicals</span><span>Alternative Milks</span>
          </div>
        </div>

        <!-- Track 4: Research & Academy -->
        <div class="wwu-studio-track-card" id="studioTrackCard-research" onclick="exploreStudioTrack('research')">
          <div class="wwu-track-icon-box">
            <svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
          </div>
          <span class="wwu-track-badge" id="trackBadge-research">COLLABORATIVE SCIENCE</span>
          <h4 id="trackTitle-research">I Have a Question</h4>
          <p class="wwu-track-synopsis" id="trackSynopsis-research">Chocolate science education, sensory panels and industry research.</p>
          <div class="wwu-track-tag-list" id="trackTags-research">
            <span>Rheology</span><span>Form V Crystals</span><span>Team Training</span><span>Scientific Study</span>
          </div>
        </div>

      </div>

      <!-- Live Asymmetric Technical Canvas (Dynamic Deep-Dive Panel) -->
      <div class="wwu-studio-canvas" id="studioCanvas">
        
        <!-- Left: Technical Scope & Lab Approach -->
        <div class="wwu-canvas-left">
          <div>
            <div class="wwu-canvas-focus-tag" id="canvasFocusTag">
              <span class="dot-beacon"></span>
              <span>DEFECT RESOLUTION &bull; CHOCOLATE PROBLEM SOLVING</span>
            </div>
            <h3 class="wwu-canvas-title" id="canvasTitle">Forensic Investigation of Chocolate Instabilities &amp; Defects</h3>
            <p class="wwu-canvas-narrative" id="canvasNarrative">When chocolate loses snap, blooms prematurely, or refuses to temper consistently, the root cause lies in lipid polymorphism or rheological shear limits. We perform rigorous bench diagnostics to determine exact root causes and engineer permanent formulation fixes.</p>
          </div>

          <div>
            <span class="wwu-params-title">Parameters Investigated in Our Pilot Lab:</span>
            <div class="wwu-params-grid" id="canvasParamsGrid">
              <div class="wwu-param-item">
                <strong>Polymorphic Form V Crystal Kinetics</strong>
                <p>Distinguishing fat bloom (Form IV/V transition or fat migration) from sugar bloom (ambient condensation).</p>
              </div>
              <div class="wwu-param-item">
                <strong>Casson Yield Stress &amp; Plastic Viscosity</strong>
                <p>Analyzing shear rate curve to prevent enrober clogging, uneven bar thickness, or moulding voids.</p>
              </div>
              <div class="wwu-param-item">
                <strong>Water Activity ($a_w$) &amp; Moisture Barriers</strong>
                <p>Eliminating shell softening and microbial risk across filled bonbons, ganaches, and inclusions.</p>
              </div>
              <div class="wwu-param-item">
                <strong>Cooling Tunnel &amp; Temper Index Curves</strong>
                <p>Calibrating latent heat removal and crystal nucleation speed for clean mould release and high gloss.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Tangible Deliverables, Timeline & CTA -->
        <div class="wwu-canvas-right">
          <div>
            <span class="wwu-deliverables-header">Tangible Project Deliverables</span>
            <div class="wwu-deliverables-list" id="canvasDeliverablesList">
              <div class="wwu-deliverable-item">
                <span class="wwu-del-check">&#10003;</span>
                <div class="wwu-del-text">
                  <strong>Defect Root Cause Diagnostic Dossier</strong>
                  <span>Comprehensive analysis of fat crystal collapse or rheological breakdown.</span>
                </div>
              </div>
              <div class="wwu-deliverable-item">
                <span class="wwu-del-check">&#10003;</span>
                <div class="wwu-del-text">
                  <strong>Corrective Pilot Batch Formulations</strong>
                  <span>Optimized cocoa butter and fat phase ratios engineered for your production line.</span>
                </div>
              </div>
              <div class="wwu-deliverable-item">
                <span class="wwu-del-check">&#10003;</span>
                <div class="wwu-del-text">
                  <strong>Tempering &amp; Cooling Parameter Guide</strong>
                  <span>Precise machine temperature, temper meter target, and tunnel fan profiles.</span>
                </div>
              </div>
              <div class="wwu-deliverable-item">
                <span class="wwu-del-check">&#10003;</span>
                <div class="wwu-del-text">
                  <strong>Accelerated 60-Day Bloom Stability Data</strong>
                  <span>Thermal stress testing to verify shelf-life longevity in warm climates.</span>
                </div>
              </div>
            </div>
          </div>

          <div>
            <div class="wwu-timeline-strip">
              <span class="time-label">Typical Engagement Pace</span>
              <span class="time-val" id="canvasTimelineVal">1 – 3 Weeks Rapid Diagnostic Sprint</span>
            </div>
            <button type="button" class="wwu-canvas-cta-btn" id="canvasCtaBtn" onclick="focusOnPageDesk('Chocolate Problem Solving')">
              <span>CONSULT ON THIS TRACK</span>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
          </div>
        </div>

      </div>

    </div>
  </section>


  <!-- ====== 4. THE QUESTION — COLLABORATION FRAMEWORK ====== -->
  <section class="wwu-areas" id="collaboration-areas">
    <div class="wwu-wrap">
      
      <div class="wwu-areas-inner">
        <!-- Left Column: Vision & Action -->
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

          <button type="button" onclick="focusOnPageDesk('R&D & Product Innovation')" class="wwu-btn-areas-cta">
            <span>DISCUSS YOUR COLLABORATION</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </button>
        </div>

        <!-- Desktop Radial Orbital Hub -->
        <div class="wwu-diagram-stage">
          
          <svg class="wwu-orbital-svg" viewBox="0 0 680 460" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient id="spokeGradGold" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#d4af37" stop-opacity="0.9"/>
                <stop offset="100%" stop-color="var(--accent)" stop-opacity="0.35"/>
              </linearGradient>
              <radialGradient id="dockGlow" cx="50%" cy="50%" r="50%">
                <stop offset="0%" stop-color="#d4af37" stop-opacity="0.8"/>
                <stop offset="100%" stop-color="#d4af37" stop-opacity="0"/>
              </radialGradient>
            </defs>

            <!-- Outer guide ring -->
            <circle cx="340" cy="230" r="205" stroke="#d4af37" stroke-width="0.8" stroke-opacity="0.18" stroke-dasharray="4 6"/>
            
            <!-- Main Orbital Track -->
            <circle cx="340" cy="230" r="148" stroke="var(--accent)" stroke-width="1.2" stroke-opacity="0.25" stroke-dasharray="5 5"/>

            <!-- Inner pulse halo ring -->
            <circle cx="340" cy="230" r="62" stroke="#d4af37" stroke-width="1" stroke-opacity="0.35" stroke-dasharray="2 4"/>

            <!-- 5 Radial Connector Spokes -->
            <line id="spoke-1" x1="340" y1="172" x2="340" y2="92" stroke="url(#spokeGradGold)" stroke-width="1.5" stroke-dasharray="3 3"/>
            <circle cx="340" cy="92" r="6" fill="url(#dockGlow)"/>
            <circle cx="340" cy="92" r="2.5" fill="#d4af37"/>

            <line id="spoke-2" x1="384" y1="198" x2="480" y2="128" stroke="url(#spokeGradGold)" stroke-width="1.5" stroke-dasharray="3 3"/>
            <circle cx="480" cy="128" r="6" fill="url(#dockGlow)"/>
            <circle cx="480" cy="128" r="2.5" fill="#d4af37"/>

            <line id="spoke-3" x1="384" y1="262" x2="470" y2="350" stroke="url(#spokeGradGold)" stroke-width="1.5" stroke-dasharray="3 3"/>
            <circle cx="470" cy="350" r="6" fill="url(#dockGlow)"/>
            <circle cx="470" cy="350" r="2.5" fill="#d4af37"/>

            <line id="spoke-4" x1="296" y1="262" x2="210" y2="350" stroke="url(#spokeGradGold)" stroke-width="1.5" stroke-dasharray="3 3"/>
            <circle cx="210" cy="350" r="6" fill="url(#dockGlow)"/>
            <circle cx="210" cy="350" r="2.5" fill="#d4af37"/>

            <line id="spoke-5" x1="296" y1="198" x2="200" y2="128" stroke="url(#spokeGradGold)" stroke-width="1.5" stroke-dasharray="3 3"/>
            <circle cx="200" cy="128" r="6" fill="url(#dockGlow)"/>
            <circle cx="200" cy="128" r="2.5" fill="#d4af37"/>
          </svg>

          <!-- Center Core: THE QUESTION -->
          <div class="wwu-diagram-center" id="orbital-nucleus">
            <div class="dc-pulse-ring"></div>
            <span class="dc-title" id="nucleus-title">THE<br>QUESTION</span>
            <span class="dc-sub" id="nucleus-sub">Curiosity starts here</span>
          </div>

          <!-- Node 1: CREATE -->
          <div class="wwu-area-card card-create" 
               onmouseenter="highlightHubNode(1, '01 • CREATE', 'R&amp;D LAB', 'Custom prototypes & trials')"
               onmouseleave="resetHubNode()"
               onclick="exploreStudioTrack('idea')">
            <div class="card-head">
              <div class="area-icon">
                <svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.5 6V17H9v-2c-2.2-1.3-3.5-3.5-3.5-6a7 7 0 0 1 7-7z"/><line x1="9" y1="21" x2="15" y2="21"/></svg>
              </div>
              <span class="area-label">01 • CREATE</span>
            </div>
            <h4>R&amp;D &amp; Product Innovation</h4>
            <p>Concept to prototypes, formulation, fat matrices, flavour design &amp; sensory trials.</p>
            <span class="card-action">Select Area &rarr;</span>
          </div>

          <!-- Node 2: SOLVE -->
          <div class="wwu-area-card card-solve" 
               onmouseenter="highlightHubNode(2, '02 • SOLVE', 'DEFECT LAB', 'Root cause diagnostics')"
               onmouseleave="resetHubNode()"
               onclick="exploreStudioTrack('defect')">
            <div class="card-head">
              <div class="area-icon">
                <svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
              </div>
              <span class="area-label">02 • SOLVE</span>
            </div>
            <h4>Chocolate Problem Solving</h4>
            <p>Root cause investigation for bloom, viscosity, tempering &amp; shelf-life defects.</p>
            <span class="card-action">Select Area &rarr;</span>
          </div>

          <!-- Node 3: EXPLORE -->
          <div class="wwu-area-card card-explore" 
               onmouseenter="highlightHubNode(3, '03 • EXPLORE', 'NOVEL LAB', 'Functional ingredients')"
               onmouseleave="resetHubNode()"
               onclick="exploreStudioTrack('ingredient')">
            <div class="card-head">
              <div class="area-icon">
                <svg viewBox="0 0 24 24"><path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/><line x1="8.5" y1="2" x2="15.5" y2="2"/></svg>
              </div>
              <span class="area-label">03 • EXPLORE</span>
            </div>
            <h4>Ingredients &amp; Application Lab</h4>
            <p>Testing novel inclusions, fats, alternative sugars &amp; functional cacao botanicals.</p>
            <span class="card-action">Select Area &rarr;</span>
          </div>

          <!-- Node 4: CONNECT -->
          <div class="wwu-area-card card-connect" 
               onmouseenter="highlightHubNode(4, '04 • CONNECT', 'COLLAB HUB', 'Strategic partnerships')"
               onmouseleave="resetHubNode()"
               onclick="exploreStudioTrack('research')">
            <div class="card-head">
              <div class="area-icon">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
              <span class="area-label">04 • CONNECT</span>
            </div>
            <h4>Knowledge &amp; Industry Collabs</h4>
            <p>Joint research, roundtables, technical publishing &amp; strategic brand partnerships.</p>
            <span class="card-action">Select Area &rarr;</span>
          </div>

          <!-- Node 5: LEARN -->
          <div class="wwu-area-card card-learn" 
               onmouseenter="highlightHubNode(5, '05 • LEARN', 'ACADEMY', 'Formulation & rheology')"
               onmouseleave="resetHubNode()"
               onclick="exploreStudioTrack('research')">
            <div class="card-head">
              <div class="area-icon">
                <svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
              </div>
              <span class="area-label">05 • LEARN</span>
            </div>
            <h4>Education &amp; Masterclasses</h4>
            <p>Deep-dive masterclasses on chocolate rheology, crystallization &amp; formulation.</p>
            <span class="card-action">Select Area &rarr;</span>
          </div>

        </div>

        <!-- Mobile Stepper Architecture (< 960px) -->
        <div class="wwu-mobile-areas-flow">
          <div class="wwu-mob-center-badge">
            <span class="mob-badge-tag">THE NUCLEUS</span>
            <h3>THE QUESTION</h3>
            <span>Curiosity is the starting point.</span>
          </div>

          <div class="wwu-mob-cards-list">
            <div class="wwu-mob-card" onclick="exploreStudioTrack('idea')">
              <div class="mob-icon"><svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.5 6V17H9v-2c-2.2-1.3-3.5-3.5-3.5-6a7 7 0 0 1 7-7z"/><line x1="9" y1="21" x2="15" y2="21"/></svg></div>
              <div>
                <span class="mob-tag">01 • CREATE</span>
                <h4>R&amp;D &amp; Product Innovation</h4>
                <p>Concept to working prototypes, formulation, fat matrices, flavour design &amp; sensory trials.</p>
                <span class="mob-btn">Explore &amp; Inquire &rarr;</span>
              </div>
            </div>

            <div class="wwu-mob-card" onclick="exploreStudioTrack('defect')">
              <div class="mob-icon"><svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></div>
              <div>
                <span class="mob-tag">02 • SOLVE</span>
                <h4>Chocolate Problem Solving</h4>
                <p>Root cause investigation for bloom, viscosity, tempering &amp; shelf-life defects.</p>
                <span class="mob-btn">Explore &amp; Inquire &rarr;</span>
              </div>
            </div>

            <div class="wwu-mob-card" onclick="exploreStudioTrack('ingredient')">
              <div class="mob-icon"><svg viewBox="0 0 24 24"><path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/><line x1="8.5" y1="2" x2="15.5" y2="2"/></svg></div>
              <div>
                <span class="mob-tag">03 • EXPLORE</span>
                <h4>Ingredients &amp; Application Lab</h4>
                <p>Testing novel inclusions, fats, alternative sugars &amp; functional cacao botanicals.</p>
                <span class="mob-btn">Explore &amp; Inquire &rarr;</span>
              </div>
            </div>

            <div class="wwu-mob-card" onclick="exploreStudioTrack('research')">
              <div class="mob-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
              <div>
                <span class="mob-tag">04 • CONNECT</span>
                <h4>Knowledge &amp; Industry Collabs</h4>
                <p>Joint research, roundtables, technical publishing &amp; strategic brand partnerships.</p>
                <span class="mob-btn">Explore &amp; Inquire &rarr;</span>
              </div>
            </div>

            <div class="wwu-mob-card" onclick="exploreStudioTrack('research')">
              <div class="mob-icon"><svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
              <div>
                <span class="mob-tag">05 • LEARN</span>
                <h4>Education &amp; Masterclasses</h4>
                <p>Deep-dive masterclasses on chocolate rheology, crystallization &amp; formulation.</p>
                <span class="mob-btn">Explore &amp; Inquire &rarr;</span>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </section>


  <!-- ====== 5. WE COLLABORATE ACROSS THE CHOCOLATE ECOSYSTEM ====== -->
  <section class="wwu-ecosystem-section" id="who-we-work-with">
    <img src="assets/cacao_botanical_sketch.jpg" alt="" class="wwu-eco-sketch-bg" />

    <div class="wwu-wrap wwu-ecosystem-inner">
      <h2 class="wwu-ecosystem-heading">WE COLLABORATE ACROSS THE CHOCOLATE ECOSYSTEM</h2>

      <!-- Curated Tiered Matrix (4 on Top, 3 Below) -->
      <div class="wwu-ecosystem-grid-tiered">
        
        <!-- Row 1 (4 items) -->
        <div class="wwu-eco-tier-row wwu-eco-tier-top">
          <!-- 1. Brands & Manufacturers -->
          <div class="wwu-eco-card is-active-eco" id="ecoCard-brands" onclick="selectEcosystemPartner('brands', this)">
            <div class="wwu-eco-icon">
              <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 26V13l6 4V11l7 5V7l8 6v13H5z"/>
                <path d="M9 26v-4M15 26v-4M21 26v-4"/>
              </svg>
            </div>
            <span class="wwu-eco-label">BRANDS &amp;<br>MANUFACTURERS</span>
          </div>

          <!-- 2. Ingredient Companies -->
          <div class="wwu-eco-card" id="ecoCard-ingredients" onclick="selectEcosystemPartner('ingredients', this)">
            <div class="wwu-eco-icon">
              <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 26C6 26 8 13 18 7c7-4 8-1 8-1s3 1 0 8c-6 10-20 12-20 12z"/>
                <path d="M6 26c4-6 10-12 16-15"/>
              </svg>
            </div>
            <span class="wwu-eco-label">INGREDIENT<br>COMPANIES</span>
          </div>

          <!-- 3. R&D & Product Developers -->
          <div class="wwu-eco-card" id="ecoCard-rd" onclick="selectEcosystemPartner('rd', this)">
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
          <div class="wwu-eco-card" id="ecoCard-chocolatiers" onclick="selectEcosystemPartner('chocolatiers', this)">
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
        </div>

        <!-- Row 2 (3 items) -->
        <div class="wwu-eco-tier-row wwu-eco-tier-bottom">
          <!-- 5. Startups & Founders -->
          <div class="wwu-eco-card" id="ecoCard-startups" onclick="selectEcosystemPartner('startups', this)">
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
          <div class="wwu-eco-card" id="ecoCard-academia" onclick="selectEcosystemPartner('academia', this)">
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
          <div class="wwu-eco-card" id="ecoCard-hotels" onclick="selectEcosystemPartner('hotels', this)">
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

      </div>

      <!-- Interactive Ecosystem Spotlight Drawer (Reveals Tailored Value) -->
      <div class="wwu-eco-spotlight-drawer" id="ecosystemSpotlightDrawer">
        <div class="wwu-eco-drawer-top">
          <span class="wwu-eco-drawer-badge" id="ecoDrawerBadge">PARTNER COLLABORATION &bull; BRANDS &amp; MANUFACTURERS</span>
          <span style="font-size:12px; color:var(--text-muted);">Tailored Commercial Blueprint</span>
        </div>
        <div class="wwu-eco-drawer-grid">
          <div class="wwu-eco-drawer-desc">
            <h4 id="ecoDrawerHeading">Reformulation, Defect Forensics &amp; Scale-Up Calibration</h4>
            <p id="ecoDrawerText">We assist established brands and industrial manufacturers with clean-label recipe modernizations, fat phase re-engineering, eliminating palm oil or artificial emulsifiers, and tuning continuous tempering lines to eliminate seasonal bloom losses.</p>
          </div>
          <div class="wwu-eco-drawer-action">
            <span id="ecoDrawerDeliverable"><strong>Key Deliverable:</strong> Commercial Recipe Card, Pilot Verification Samples &amp; Scale-Up SOP</span>
            <button type="button" class="wwu-eco-drawer-link" onclick="focusOnPageDesk('R&D & Product Innovation')">
              <span>Discuss Partnership Options &rarr;</span>
            </button>
          </div>
        </div>
      </div>

      <p class="wwu-ecosystem-footer-note">If your world touches chocolate, we can create value together.</p>
    </div>
  </section>


  <!-- ====== 6. ON-PAGE DIRECT CONSULTATION DESK (Seamless UX) ====== -->
  <section class="wwu-desk-section" id="consultation-section">
    <div class="wwu-wrap">
      
      <div class="wwu-desk-card">
        <div class="wwu-desk-header">
          <span class="wwu-tag-badge">
            <span class="dot-beacon"></span>
            <span>GET STARTED</span>
          </span>
          <h2 style="font-size: clamp(28px, 3.4vw, 38px); margin-bottom: 10px;">Start a Conversation</h2>
          <p class="wwu-sec-desc-lead">Tell us about your challenge, idea, or question. We'll review it and get back to you with how we can help.</p>
          <div class="wwu-confidential-badge">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span>100% Confidential &bull; Mutual Non-Disclosure Agreement (NDA) Available Upon Request</span>
          </div>
        </div>

        <?php if ($submissionSuccess): ?>
          <div class="alert-ok">&#10003; Thank you! Your inquiry has been received. We will get in touch with you within 24 hours.</div>
        <?php elseif (!empty($submissionError)): ?>
          <div class="alert-err"><?php echo htmlspecialchars($submissionError); ?></div>
        <?php endif; ?>

        <!-- Quick Interactive Chips for Topic Picking -->
        <div class="wwu-desk-chips-wrap">
          <span class="wwu-desk-chips-label">Select Your Inquiry Focus:</span>
          <div class="wwu-desk-chips">
            <button type="button" class="wwu-desk-chip-btn is-active-chip" onclick="setInquiryTopic('R&D & Product Innovation', this)">🔬 R&amp;D &amp; Product Innovation</button>
            <button type="button" class="wwu-desk-chip-btn" onclick="setInquiryTopic('Chocolate Problem Solving', this)">🧩 Problem Solving (Bloom/Viscosity)</button>
            <button type="button" class="wwu-desk-chip-btn" onclick="setInquiryTopic('Ingredients & Application Lab', this)">⚗️ Ingredients &amp; Application Lab</button>
            <button type="button" class="wwu-desk-chip-btn" onclick="setInquiryTopic('Education & Masterclasses', this)">🎓 Education &amp; Masterclasses</button>
            <button type="button" class="wwu-desk-chip-btn" onclick="setInquiryTopic('Knowledge & Industry Collaboration', this)">🤝 Knowledge &amp; Industry Collabs</button>
            <button type="button" class="wwu-desk-chip-btn" onclick="setInquiryTopic('General Inquiry', this)">✨ General Inquiry</button>
          </div>
        </div>

        <form method="POST" action="work-with-us.php#consultation-section" id="onpage-inquiry-form">
          <div class="form-grid">
            <div class="form-row">
              <div>
                <label for="desk-name">Your Full Name *</label>
                <input type="text" id="desk-name" name="name" required placeholder="e.g. Aarti Sahni" />
              </div>
              <div>
                <label for="desk-email">Work Email Address *</label>
                <input type="email" id="desk-email" name="email" required placeholder="name@company.com" />
              </div>
            </div>

            <div class="form-row">
              <div>
                <label for="desk-phone">Phone / WhatsApp</label>
                <input type="text" id="desk-phone" name="phone" placeholder="+91 98765 43210" />
              </div>
              <div>
                <label for="desk-company">Company / Brand Name</label>
                <input type="text" id="desk-company" name="company" placeholder="e.g. Artisan Cacao Ltd." />
              </div>
            </div>

            <div class="form-row">
              <div>
                <label for="desk-service-select">Primary Area of Interest</label>
                <select name="service" id="desk-service-select">
                  <option value="R&D & Product Innovation">🔬 R&amp;D &amp; Product Innovation</option>
                  <option value="Chocolate Problem Solving">🧩 Chocolate Problem Solving</option>
                  <option value="Ingredients & Application Lab">⚗️ Ingredients &amp; Application Lab</option>
                  <option value="Education & Masterclasses">🎓 Education &amp; Masterclasses</option>
                  <option value="Knowledge & Industry Collaboration">🤝 Knowledge &amp; Industry Collaboration</option>
                  <option value="General Inquiry">✨ General Inquiry</option>
                </select>
              </div>
              <div>
                <label for="desk-budget">Budget Range</label>
                <select name="budget" id="desk-budget">
                  <option value="Flexible / Undecided">Flexible / Undecided</option>
                  <option value="₹50,000 - ₹1,50,000">&#8377;50,000 – &#8377;1,50,000</option>
                  <option value="₹1,50,000 - ₹3,50,000">&#8377;1,50,000 – &#8377;3,50,000</option>
                  <option value="₹3,50,000+">&#8377;3,50,000+ (Enterprise)</option>
                </select>
              </div>
            </div>

            <div>
              <label for="desk-message">Describe Your Challenge or Question *</label>
              <textarea id="desk-message" name="message" rows="4" required placeholder="What's the question you're trying to answer? Tell us about the product, formulation, or idea you're working on..."></textarea>
            </div>

            <div style="text-align:center; margin-top:10px;">
              <button type="submit" class="form-submit-btn">Submit Inquiry &rarr;</button>
            </div>
          </div>
        </form>

      </div>

    </div>
  </section>


  <!-- ====== 7. CONSULTATION MODAL ====== -->
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

        <form method="POST" action="work-with-us.php" id="modal-inquiry-form">
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
                  <option value="R&D & Product Innovation">🔬 R&amp;D &amp; Product Innovation</option>
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
                  <option value="Flexible / Undecided">Flexible / Undecided</option>
                  <option value="₹50,000 - ₹1,50,000">&#8377;50,000 – &#8377;1,50,000</option>
                  <option value="₹1,50,000 - ₹3,50,000">&#8377;1,50,000 – &#8377;3,50,000</option>
                  <option value="₹3,50,000+">&#8377;3,50,000+ (Enterprise)</option>
                </select>
              </div>
            </div>
            <div>
              <label>Describe Your Challenge or Question *</label>
              <textarea name="message" rows="4" required placeholder="What's the question you're trying to answer? Tell us about the product, formulation, or idea you're working on..."></textarea>
            </div>
            <div style="text-align:center; margin-top:8px;">
              <button type="submit" class="form-submit-btn">Submit Inquiry &rarr;</button>
            </div>
          </div>
        </form>
      </div>

    </div>
  </div>

</div>

<!-- ================================================================
     INTERACTIVE SCRIPTS (Non-Linear UI/UX Platform Engine)
     ================================================================ -->
<script>
/**
 * 1. DYNAMIC JOURNEY PROGRESS TRACKER & MILESTONE DOSSIER
 */
const journeyMilestones = {
  1: {
    phase: "PHASE 01 • INITIAL ENGAGEMENT",
    clientRole: "Client Input: Challenge Brief & Target Constraints",
    heading: "CONNECT — Understanding Your Challenge, Intent & Line Conditions",
    scope: "We begin with an exploratory technical dialogue and mutual NDA execution. We map your current formulation, cocoa sourcing, production equipment, tempering limits, and commercial targets to define clear technical success criteria.",
    deliverable: "Project Charter & Formulation Feasibility Hypothesis"
  },
  2: {
    phase: "PHASE 02 • TECHNICAL DIAGNOSTICS",
    clientRole: "Client Input: Raw Material Specs & Line Constraints",
    heading: "EXPLORE — Auditing Ingredients, Rheology & Formulation Boundaries",
    scope: "We evaluate cocoa bean origin terroir, fat phase ratios (cocoa butter vs alternatives), melting enthalpies, particle size distribution (D90 < 20μm), and thermal tolerance boundaries under production conditions.",
    deliverable: "Ingredient Compatibility Dossier & Boundary Mapping"
  },
  3: {
    phase: "PHASE 03 • BENCHTOP PROTOTYPING",
    clientRole: "Client Input: Prototype Sensory Review & Direction Selection",
    heading: "EXPERIMENT — Micro-Refining, Conching & Form V Polymorph Seeding",
    scope: "Small-batch benchtop refining and conching trials. We test seeding kinetics, crystallization polymorphs (Form V), and inclusion compatibility under controlled humidity and temperature.",
    deliverable: "Physical Benchtop Prototypes & Sensory Variance Matrix"
  },
  4: {
    phase: "PHASE 04 • SENSORY & STRESS TESTING",
    clientRole: "Client Input: Blind Sensory Evaluation & Shelf-Life Targets",
    heading: "REFINE — Sensory Profiling, Bloom Acceleration & Texture Calibration",
    scope: "Sensory analysis and thermal cycling in accelerated bloom chambers (18°C to 28°C cycling). We measure Casson yield stress and plastic viscosity to ensure crisp snap, gloss, and clean melting.",
    deliverable: "Refined Batch Iterations & Accelerated Stability Log"
  },
  5: {
    phase: "PHASE 05 • COMMERCIAL SCALE-UP",
    clientRole: "Client Input: Factory Line Specs & Machinery Parameters",
    heading: "APPLY — Technical Transfer, Recipe Card & Commercial Line Protocol",
    scope: "Translating benchtop successes into scalable factory parameters. We establish precise tempering curves, cooling tunnel fan speeds, deposition temperatures, and quality assurance checkpoints.",
    deliverable: "Complete Technical Recipe Card & Industrial Transfer Protocol"
  },
  6: {
    phase: "PHASE 06 • LONG-TERM PARTNERSHIP",
    clientRole: "Client Input: Post-Launch Line Feedback & Seasonal Audits",
    heading: "EVOLVE — Continuous Optimization, Shelf-Life Monitoring & Line Advisory",
    scope: "Ambient shelf-life tracking across seasons, packaging gas barrier audits, ongoing formulation advisory, and next-generation flavor or ingredient line extensions.",
    deliverable: "Long-term Stability Log & Ongoing R&D Advisory Access"
  }
};

function activateJourneyStep(stepNumber) {
  const steps = document.querySelectorAll('.wwu-step-node');
  const lineFill = document.getElementById('journeyLineFill');
  
  steps.forEach((node, idx) => {
    if (idx < stepNumber) {
      node.classList.add('is-active');
    } else {
      node.classList.remove('is-active');
    }
  });

  if (lineFill) {
    const percentages = [16.6, 33.3, 50, 66.6, 83.3, 100];
    lineFill.style.width = percentages[stepNumber - 1] + '%';
  }

  // Update Dynamic Dossier
  const data = journeyMilestones[stepNumber];
  if (data) {
    const pill = document.getElementById('dossierPhasePill');
    const role = document.getElementById('dossierClientRole');
    const heading = document.getElementById('dossierHeading');
    const scopeText = document.getElementById('dossierScopeText');
    const delTitle = document.getElementById('dossierDeliverableTitle');

    if (pill) pill.textContent = data.phase;
    if (role) role.textContent = data.clientRole;
    if (heading) heading.textContent = data.heading;
    if (scopeText) scopeText.textContent = data.scope;
    if (delTitle) delTitle.textContent = data.deliverable;
  }
}

/**
 * 2. INTERACTIVE COLLABORATION STUDIO & SOLUTION EXPLORER
 */
let currentStudioMode = 'scenario'; // 'scenario' | 'model'

const studioTracks = {
  defect: {
    badgeScenario: "DEFECT FORENSICS",
    badgeModel: "RAPID DIAGNOSTIC SPRINT",
    titleScenario: "I Have a Problem",
    titleModel: "Defect Forensics & Rheology Sprint",
    synopsisScenario: "Fat bloom, viscosity drift, tempering instability or shelf-life collapse.",
    synopsisModel: "Targeted 1–3 week sprint to investigate and fix critical production defects.",
    tagsScenario: ["Fat Bloom", "Viscosity", "Tempering", "Shelf-Life", "Texture"],
    tagsModel: ["1–3 Weeks", "Root Cause Analysis", "Lab Trials", "Recipe Fix", "Line Protocol"],
    focusTag: "DEFECT RESOLUTION &bull; CHOCOLATE PROBLEM SOLVING",
    heading: "Forensic Investigation of Chocolate Instabilities & Defects",
    narrative: "When chocolate loses snap, blooms prematurely, or refuses to temper consistently, the root cause lies in lipid polymorphism or rheological shear limits. We perform rigorous bench diagnostics to determine exact root causes and engineer permanent formulation fixes.",
    serviceName: "Chocolate Problem Solving",
    timelineVal: "1 – 3 Weeks Rapid Diagnostic Sprint",
    params: [
      { title: "Polymorphic Form V Crystal Kinetics", desc: "Distinguishing fat bloom (Form IV/V transition or fat migration) from sugar bloom (ambient condensation)." },
      { title: "Casson Yield Stress & Plastic Viscosity", desc: "Analyzing shear rate curves to prevent enrober clogging, uneven bar thickness, or moulding voids." },
      { title: "Water Activity (aw) & Moisture Barriers", desc: "Eliminating shell softening and microbial risk across filled bonbons, ganaches, and inclusions." },
      { title: "Cooling Tunnel & Temper Index Curves", desc: "Calibrating latent heat removal and crystal nucleation speed for clean mould release and high gloss." }
    ],
    deliverables: [
      { title: "Defect Root Cause Diagnostic Dossier", desc: "Comprehensive analysis of fat crystal collapse or rheological breakdown." },
      { title: "Corrective Pilot Batch Formulations", desc: "Optimized cocoa butter and fat phase ratios engineered for your production line." },
      { title: "Tempering & Cooling Parameter Guide", desc: "Precise machine temperature, temper meter target, and tunnel fan profiles." },
      { title: "Accelerated 60-Day Bloom Stability Data", desc: "Thermal stress testing to verify shelf-life longevity in warm climates." }
    ]
  },
  idea: {
    badgeScenario: "PRODUCT ARCHITECTURE",
    badgeModel: "CUSTOM R&D LAB PROJECT",
    titleScenario: "I Have an Idea",
    titleModel: "Formulation & Product Innovation Lab",
    synopsisScenario: "Developing a distinctive, commercial-ready chocolate or confectionery.",
    synopsisModel: "End-to-end R&D from initial concept to commercial pilot batch formulations.",
    tagsScenario: ["New Formats", "Clean-Label", "Single-Origin", "Plant-Based", "Sugar Reduction"],
    tagsModel: ["4–8 Weeks", "Pilot Batches", "Sensory Balancing", "BOM Costing", "Scale-Up SOP"],
    focusTag: "PRODUCT ARCHITECTURE &bull; R&amp;D &amp; PRODUCT INNOVATION",
    heading: "Engineering Next-Generation Chocolate & Confectionery Architecture",
    narrative: "Turning ambitious flavor and concept ideas into commercially viable recipes. We balance cocoa origin profiles, fat matrices, melt curves, and clean-label sweeteners to create signature chocolates that stand out in the premium market.",
    serviceName: "R&D & Product Innovation",
    timelineVal: "4 – 8 Weeks Custom R&D Lab Project",
    params: [
      { title: "Cocoa Phase & Solid Ratio Balancing", desc: "Formulating precise ratios of cocoa liquor, deodorized butter, and solids for optimum snap and melt." },
      { title: "Clean-Label & Sugar Reduction Science", desc: "Integrating allulose, monk fruit, inulin, or dates without grittiness or undesirable cooling sensations." },
      { title: "Plant-Based Dairy Alternative Matrices", desc: "Developing creamy vegan milk chocolate formulations using oat, almond, or tiger nut matrices." },
      { title: "Terroir & Roasting Flavor Retention", desc: "Fine-tuning conching duration and shear profiles to capture delicate floral, fruity, or nutty cacao notes." }
    ],
    deliverables: [
      { title: "3 to 5 Handcrafted Benchtop Pilot Prototypes", desc: "Physical pilot samples delivered for sensory evaluation and stakeholder approval." },
      { title: "Commercial Bill of Materials (BOM) & Formulation Sheet", desc: "Full percentage breakdown with commercial supplier specifications." },
      { title: "Sensory & Nutritional Fact Sheet", desc: "Complete macro profile, sugar-fat ratios, and sensory flavor wheel mapping." },
      { title: "Pilot-to-Production Scale-Up SOP", desc: "Clear industrial refining, conching, and deposition instructions for your team or co-packer." }
    ]
  },
  ingredient: {
    badgeScenario: "APPLICATION LAB",
    badgeModel: "INGREDIENT VALIDATION SPRINT",
    titleScenario: "I Have an Ingredient",
    titleModel: "Ingredient Trials & Application Validation",
    synopsisScenario: "Evaluating novel fats, inclusions, alternative sweeteners, or cacao components.",
    synopsisModel: "Proving your ingredient's functionality, mouthfeel, and stability in real chocolate systems.",
    tagsScenario: ["Novel Fats / CBE", "Low-GI Sweeteners", "Botanicals", "Alternative Milks"],
    tagsModel: ["2–5 Weeks", "Fat Compatibility", "Mouthfeel Trials", "B2B Dossier", "Sample Showcase"],
    focusTag: "APPLICATION LAB &bull; INGREDIENTS &amp; APPLICATION LAB",
    heading: "Rigorous Functional & Sensory Validation for Novel Ingredients",
    narrative: "Ingredients behave differently inside a dense fat crystal suspension. We test your cocoa butter alternatives, novel plant extracts, natural sweeteners, or inclusion crunchies in active chocolate matrices to verify shelf stability, temper compatibility, and mouthfeel.",
    serviceName: "Ingredients & Application Lab",
    timelineVal: "2 – 5 Weeks Application Sprint",
    params: [
      { title: "Eutectic Fat Compatibility & Bloom Risk", desc: "Evaluating cocoa butter equivalents (CBE/CBR) and nut oils for fat migration and softening." },
      { title: "Particle Size & Palate Grittiness (D90 < 20μm)", desc: "Milling fibrous plant powders and protein isolates below the human tongue detection threshold." },
      { title: "Rheology & Emulsification Response", desc: "Measuring how novel inclusions alter yield stress and Casson plastic viscosity, optimizing surfactant levels." },
      { title: "Moisture Sorption & Particulate Crunch", desc: "Testing hydrophobic coating techniques to keep freeze-dried fruits or nuts crunchy in chocolate." }
    ],
    deliverables: [
      { title: "Ingredient Application Compatibility Dossier", desc: "Technical data report on inclusion thresholds, temper impact, and shelf-life." },
      { title: "Finished Showcase Bench Samples", desc: "Finished prototype chocolate bars or dragées incorporating your ingredient for client presentations." },
      { title: "Commercial Application Whitepaper Sheet", desc: "Authoritative technical one-pager for your B2B enterprise sales team." },
      { title: "Accelerated Oxidation & Stability Log", desc: "Verification of flavor stability, fat rancidity prevention, and color retention." }
    ]
  },
  research: {
    badgeScenario: "COLLABORATIVE SCIENCE",
    badgeModel: "ACADEMY & ADVISORY TRACK",
    titleScenario: "I Have a Question",
    titleModel: "Knowledge Partnerships & Masterclasses",
    synopsisScenario: "Chocolate science education, sensory panels and industry research.",
    synopsisModel: "Technical masterclasses, factory staff training, or ongoing technical advisory retainers.",
    tagsScenario: ["Rheology", "Form V Crystals", "Team Training", "Scientific Study"],
    tagsModel: ["Flexible Scope", "Masterclasses", "Staff Upskilling", "Research Papers", "Retainer Advisory"],
    focusTag: "COLLABORATIVE SCIENCE &bull; KNOWLEDGE &amp; ACADEMY",
    heading: "Technical Masterclasses, Team Immersion & Scientific Partnerships",
    narrative: "Elevating chocolate making from guesswork to systematic science. We conduct hands-on masterclasses for chefs and production teams, joint academic research on cocoa fermentation and crystallization, and retained technical advisory for growing brands.",
    serviceName: "Education & Masterclasses",
    timelineVal: "Flexible (1-Day Masterclass to Annual Retainer)",
    params: [
      { title: "Rheology & Physics of Chocolate Flow", desc: "Understanding shear stress, plastic viscosity, and Bingham curves to optimize line efficiency." },
      { title: "Cacao Fermentation Biochemistry & Terroir", desc: "Evaluating cut tests, fermentation indices, and roasting profiles to unlock origin character." },
      { title: "Factory Tempering & Line Trouble Diagnostics", desc: "Mastering temper meters, slope curves, and continuous cooling tunnel air velocities." },
      { title: "Sensory Profiling & Defect Recognition", desc: "Training professional palates to identify over-roasting, smoke defects, rancidity, and blooming." }
    ],
    deliverables: [
      { title: "Technical Curriculum & Formulation Guidebook", desc: "Comprehensive engineering reference notes and formulation calculator spreadsheets." },
      { title: "Interactive Lab Training Sessions", desc: "Hands-on micro-batch sessions, tempering drills, and blind sensory calibration." },
      { title: "Standard Operating Procedure (SOP) Library", desc: "Custom documentation for your factory, kitchen, or product line." },
      { title: "Direct Advisory Access on Retainer", desc: "Ongoing phone, WhatsApp, and lab advisory support for technical escalations." }
    ]
  }
};

function switchStudioMode(mode) {
  currentStudioMode = mode;
  const btnScenario = document.getElementById('modeBtnScenario');
  const btnModel = document.getElementById('modeBtnModel');

  if (mode === 'scenario') {
    btnScenario.classList.add('is-active-mode');
    btnModel.classList.remove('is-active-mode');
  } else {
    btnModel.classList.add('is-active-mode');
    btnScenario.classList.remove('is-active-mode');
  }

  // Update track cards content
  ['defect', 'idea', 'ingredient', 'research'].forEach(key => {
    const data = studioTracks[key];
    const badge = document.getElementById('trackBadge-' + key);
    const title = document.getElementById('trackTitle-' + key);
    const synopsis = document.getElementById('trackSynopsis-' + key);
    const tagsContainer = document.getElementById('trackTags-' + key);

    if (badge) badge.textContent = mode === 'scenario' ? data.badgeScenario : data.badgeModel;
    if (title) title.textContent = mode === 'scenario' ? data.titleScenario : data.titleModel;
    if (synopsis) synopsis.textContent = mode === 'scenario' ? data.synopsisScenario : data.synopsisModel;

    if (tagsContainer) {
      const tags = mode === 'scenario' ? data.tagsScenario : data.tagsModel;
      tagsContainer.innerHTML = tags.map(t => `<span>${t}</span>`).join('');
    }
  });
}

function exploreStudioTrack(trackKey) {
  const data = studioTracks[trackKey];
  if (!data) return;

  // Update card active class
  document.querySelectorAll('.wwu-studio-track-card').forEach(c => c.classList.remove('is-active-track'));
  const activeCard = document.getElementById('studioTrackCard-' + trackKey);
  if (activeCard) activeCard.classList.add('is-active-track');

  // Update Canvas content
  const focusTag = document.getElementById('canvasFocusTag');
  const title = document.getElementById('canvasTitle');
  const narrative = document.getElementById('canvasNarrative');
  const paramsGrid = document.getElementById('canvasParamsGrid');
  const delList = document.getElementById('canvasDeliverablesList');
  const timeVal = document.getElementById('canvasTimelineVal');
  const ctaBtn = document.getElementById('canvasCtaBtn');

  if (focusTag) focusTag.innerHTML = `<span class="dot-beacon"></span><span>${data.focusTag}</span>`;
  if (title) title.textContent = data.heading;
  if (narrative) narrative.textContent = data.narrative;
  if (timeVal) timeVal.textContent = data.timelineVal;

  if (ctaBtn) {
    ctaBtn.setAttribute('onclick', `focusOnPageDesk('${data.serviceName}')`);
    ctaBtn.querySelector('span').textContent = `CONSULT ON THIS TRACK (${data.serviceName.toUpperCase()})`;
  }

  // Update Parameters
  if (paramsGrid && data.params) {
    paramsGrid.innerHTML = data.params.map(p => `
      <div class="wwu-param-item">
        <strong>${p.title}</strong>
        <p>${p.desc}</p>
      </div>
    `).join('');
  }

  // Update Deliverables
  if (delList && data.deliverables) {
    delList.innerHTML = data.deliverables.map(d => `
      <div class="wwu-deliverable-item">
        <span class="wwu-del-check">&#10003;</span>
        <div class="wwu-del-text">
          <strong>${d.title}</strong>
          <span>${d.desc}</span>
        </div>
      </div>
    `).join('');
  }

  // Smooth scroll to studio if user came from hero
  const studioSec = document.getElementById('interactive-studio');
  if (studioSec) {
    const rect = studioSec.getBoundingClientRect();
    if (rect.top < -50 || rect.top > window.innerHeight) {
      studioSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }
}

/**
 * 3. INTERACTIVE ECOSYSTEM MATCHER & SPOTLIGHT DRAWER
 */
const ecosystemData = {
  brands: {
    badge: "PARTNER COLLABORATION &bull; BRANDS &amp; MANUFACTURERS",
    heading: "Reformulation, Defect Forensics &amp; Scale-Up Calibration",
    text: "We assist established brands and industrial manufacturers with clean-label recipe modernizations, fat phase re-engineering, eliminating palm oil or artificial emulsifiers, and tuning continuous tempering lines to eliminate seasonal bloom losses.",
    deliverable: "Commercial Recipe Card, Pilot Verification Samples &amp; Scale-Up SOP",
    service: "R&D & Product Innovation"
  },
  ingredients: {
    badge: "PARTNER COLLABORATION &bull; INGREDIENT COMPANIES",
    heading: "Independent Matrix Testing &amp; Application Verification",
    text: "Proving your novel fat, alternative sweetener, plant protein, or natural flavoring inside real chocolate systems. We deliver rigorous application testing, crystallization curves, and show-stopping prototypes for your B2B sales force.",
    deliverable: "Application Dossier, Technical Whitepaper &amp; Trade Show Prototypes",
    service: "Ingredients & Application Lab"
  },
  rd: {
    badge: "PARTNER COLLABORATION &bull; R&amp;D &amp; PRODUCT DEVELOPERS",
    heading: "De-Risking New Formulations &amp; Overcoming Roadblocks",
    text: "Partnering with corporate innovation teams to solve difficult formulation hurdles: reducing sugar without cooling aftertaste, balancing high protein chocolate viscosities, and solving tempering drift under tight project deadlines.",
    deliverable: "Defect Diagnostics Report &amp; Corrective Formulation Series",
    service: "R&D & Product Innovation"
  },
  chocolatiers: {
    badge: "PARTNER COLLABORATION &bull; CHOCOLATIERS &amp; CHEFS",
    heading: "Mastering Ganache Water Activity &amp; Signature Couvertures",
    text: "Working alongside artisan chocolate makers and pastry chefs to achieve 90+ day shelf-life stability without artificial preservatives, calculating precise water activity (aw), and formulating custom house couvertures from single-origin beans.",
    deliverable: "Shelf-Life Formulations, Ganache Water Activity Maps &amp; Masterclass Modules",
    service: "Education & Masterclasses"
  },
  startups: {
    badge: "PARTNER COLLABORATION &bull; STARTUPS &amp; FOUNDERS",
    heading: "Turning Blue-Sky Concepts Into Scalable Market Launches",
    text: "Empowering food entrepreneurs to transition from kitchen prototypes to co-packer ready specifications. We build commercial formulations, source ingredients, and establish production SOPs so you launch with confidence.",
    deliverable: "Market-Ready Benchtop Prototypes, BOM Costing &amp; Co-Packer Handover SOP",
    service: "R&D & Product Innovation"
  },
  academia: {
    badge: "PARTNER COLLABORATION &bull; RESEARCHERS &amp; ACADEMIA",
    heading: "Joint Scientific Exploration, Polyphenols &amp; Lipid Kinetics",
    text: "Collaborating with universities and food science institutes on cocoa fermentation biochemistry, lipid crystallization polymorphism, sensory evaluation, and peer-reviewed technical publications.",
    deliverable: "Standardized Trial Batches, Analytical Data Sets &amp; Co-Authored Papers",
    service: "Knowledge & Industry Collaboration"
  },
  hotels: {
    badge: "PARTNER COLLABORATION &bull; LUXURY HOTELS &amp; HORECA",
    heading: "Signature House Chocolate &amp; Banqueting Thermal Stability",
    text: "Formulating exclusive signature chocolate collections for luxury hotels, optimizing heat stability for banquet display, and training pastry brigade teams on crystallization and decorative showpieces.",
    deliverable: "Exclusive House Chocolate Formula &amp; Kitchen Tempering Manual",
    service: "Education & Masterclasses"
  }
};

function selectEcosystemPartner(partnerKey, cardEl) {
  const data = ecosystemData[partnerKey];
  if (!data) return;

  // Update card active class
  document.querySelectorAll('.wwu-eco-card').forEach(c => c.classList.remove('is-active-eco'));
  if (cardEl) cardEl.classList.add('is-active-eco');

  // Update Drawer
  const badge = document.getElementById('ecoDrawerBadge');
  const heading = document.getElementById('ecoDrawerHeading');
  const text = document.getElementById('ecoDrawerText');
  const del = document.getElementById('ecoDrawerDeliverable');
  const drawer = document.getElementById('ecosystemSpotlightDrawer');

  if (badge) badge.innerHTML = data.badge;
  if (heading) heading.innerHTML = data.heading;
  if (text) text.innerHTML = data.text;
  if (del) del.innerHTML = `<strong>Key Deliverable:</strong> ${data.deliverable}`;

  const linkBtn = drawer ? drawer.querySelector('.wwu-eco-drawer-link') : null;
  if (linkBtn) {
    linkBtn.setAttribute('onclick', `focusOnPageDesk('${data.service}')`);
  }
}

/**
 * 4. INTERACTIVE HUB NUCLEUS DYNAMIC FOCUS (Orbital Constellation)
 */
function highlightHubNode(nodeId, tag, title, sub) {
  const nucleus = document.getElementById('orbital-nucleus');
  const titleEl = document.getElementById('nucleus-title');
  const subEl = document.getElementById('nucleus-sub');
  const spoke = document.getElementById('spoke-' + nodeId);
  
  if (nucleus && titleEl && subEl) {
    nucleus.style.borderColor = '#d4af37';
    nucleus.style.boxShadow = '0 0 0 7px rgba(212, 175, 55, 0.2), 0 0 35px rgba(212, 175, 55, 0.45)';
    nucleus.style.transform = 'translate(-50%, -50%) scale(1.08)';
    titleEl.innerHTML = title;
    subEl.innerText = sub;
    subEl.style.color = '#d4af37';
  }
  if (spoke) {
    spoke.style.stroke = '#d4af37';
    spoke.style.strokeWidth = '2.2';
    spoke.style.strokeDasharray = 'none';
  }
}

function resetHubNode() {
  const nucleus = document.getElementById('orbital-nucleus');
  const titleEl = document.getElementById('nucleus-title');
  const subEl = document.getElementById('nucleus-sub');
  
  if (nucleus && titleEl && subEl) {
    nucleus.style.borderColor = '';
    nucleus.style.boxShadow = '';
    nucleus.style.transform = '';
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
 * 5. ON-PAGE CONSULTATION DESK & MODAL SYNC
 */
function setInquiryTopic(serviceName, chipBtn) {
  document.querySelectorAll('.wwu-desk-chip-btn').forEach(btn => btn.classList.remove('is-active-chip'));
  if (chipBtn) chipBtn.classList.add('is-active-chip');

  const deskSelect = document.getElementById('desk-service-select');
  if (deskSelect) {
    for (let i = 0; i < deskSelect.options.length; i++) {
      if (deskSelect.options[i].value === serviceName) {
        deskSelect.selectedIndex = i;
        break;
      }
    }
  }

  const modalSelect = document.getElementById('modal-service-select');
  if (modalSelect) {
    for (let i = 0; i < modalSelect.options.length; i++) {
      if (modalSelect.options[i].value === serviceName) {
        modalSelect.selectedIndex = i;
        break;
      }
    }
  }
}

function focusOnPageDesk(serviceName) {
  if (serviceName) {
    const chips = document.querySelectorAll('.wwu-desk-chip-btn');
    chips.forEach(chip => {
      if (chip.textContent.toLowerCase().includes(serviceName.toLowerCase())) {
        setInquiryTopic(serviceName, chip);
      }
    });
  }

  const desk = document.getElementById('consultation-section');
  if (desk) {
    desk.scrollIntoView({ behavior: 'smooth', block: 'start' });
    setTimeout(() => {
      const nameInput = document.getElementById('desk-name');
      if (nameInput) nameInput.focus();
    }, 600);
  }
}

function selectCollabArea(areaName, cardElement) {
  focusOnPageDesk(areaName);
}

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

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeConsultationModal();
  }
});

<?php if ($submissionSuccess || !empty($submissionError)): ?>
window.addEventListener('DOMContentLoaded', function() {
  const desk = document.getElementById('consultation-section');
  if (desk) desk.scrollIntoView();
});
<?php endif; ?>
</script>

<!-- ================================================================
     HERO LAB VIDEO CONTROLS
     =============================================================== -->
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
