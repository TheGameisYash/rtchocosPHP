<?php
// includes/components/workshop_notify_modal.php
// Dedicated Luxury Workshop Waitlist & Priority Notification Modal for RT Chocos
?>
<!-- Workshop Notify Modal Backdrop & Card -->
<div id="workshop-notify-modal" class="wnm-backdrop" role="dialog" aria-modal="true" aria-labelledby="wnm-heading" aria-hidden="true">
  <div class="wnm-card">
    <button type="button" class="wnm-close-btn" onclick="closeWorkshopNotifyModal()" aria-label="Close dialog">&times;</button>
    
    <div class="wnm-header">
      <div class="wnm-badge">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <span>Academy Priority Waitlist</span>
      </div>
      <h3 id="wnm-heading" class="wnm-title">Be First in Line</h3>
      <p class="wnm-subtitle">Get private early-access booking privileges, curriculum outlines, and launch notifications the moment this masterclass opens.</p>
    </div>

    <!-- Active Workshop Focus Pill -->
    <div class="wnm-workshop-box">
      <div class="wnm-workshop-icon">🍫</div>
      <div class="wnm-workshop-info">
        <h4 id="wnm-title" class="wnm-workshop-title">Artisan Chocolate Truffles &amp; Ganache</h4>
        <div class="wnm-meta-tags">
          <span id="wnm-duration" class="wnm-tag">⏱ 1 Day</span>
          <span id="wnm-level" class="wnm-tag">Beginner to Intermediate</span>
          <span class="wnm-tag wnm-tag-status">Coming Soon</span>
        </div>
      </div>
    </div>

    <!-- Submission Form -->
    <form id="workshop-notify-form" class="wnm-form" novalidate>
      <input type="text" name="nickname" style="display:none !important;" tabindex="-1" autocomplete="off" aria-hidden="true" />
      <input type="hidden" id="wnm-hidden-title" name="workshop_title" value="Artisan Chocolate Truffles & Ganache" />
      
      <div class="wnm-input-group">
        <label for="wnm-email" class="wnm-label">Your Email Address</label>
        <div class="wnm-input-wrapper">
          <svg class="wnm-input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
            <polyline points="22,6 12,13 2,6"></polyline>
          </svg>
          <input type="email" id="wnm-email" name="email" class="wnm-input" placeholder="name@domain.com" required autocomplete="email" />
        </div>
      </div>

      <div id="wnm-feedback" class="wnm-feedback" style="display:none;" role="alert"></div>

      <button type="submit" id="wnm-submit-btn" class="wnm-submit-btn">
        <span class="wnm-btn-text">Notify Me When Open</span>
        <svg class="wnm-btn-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
          <line x1="5" y1="12" x2="19" y2="12"></line>
          <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
      </button>

      <div class="wnm-trust-note">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
        <span>Zero spam. You will only receive an alert when this workshop opens.</span>
      </div>
    </form>

    <!-- Success View (Hidden by default) -->
    <div id="wnm-success-state" class="wnm-success-state" style="display:none;">
      <div class="wnm-success-icon-wrap">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
          <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
      </div>
      <h4 class="wnm-success-title">You're on the Priority List!</h4>
      <p id="wnm-success-message" class="wnm-success-desc">We've saved your spot. You will be the very first to receive registration links and early-bird seats as soon as dates are confirmed.</p>
      <button type="button" class="wnm-done-btn" onclick="closeWorkshopNotifyModal()">Close Window</button>
    </div>
  </div>
</div>

<style>
/* Workshop Notification Modal Luxury Styles */
.wnm-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(10, 18, 13, 0.82);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  z-index: 100050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px 16px;
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.3s ease;
}

.wnm-backdrop.open {
  opacity: 1;
  visibility: visible;
  pointer-events: auto;
}

body.wnm-modal-open {
  overflow: hidden !important;
}

.wnm-card {
  position: relative;
  background: linear-gradient(165deg, #13271D 0%, #0C1A13 100%);
  border: 1px solid rgba(229, 179, 88, 0.35);
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.65), 0 0 50px rgba(229, 179, 88, 0.12);
  border-radius: 20px;
  width: 100%;
  max-width: 480px;
  padding: 32px 28px;
  color: #FAF6EF;
  transform: translateY(20px) scale(0.97);
  transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
  box-sizing: border-box;
}

.wnm-backdrop.open .wnm-card {
  transform: translateY(0) scale(1);
}

.wnm-close-btn {
  position: absolute;
  top: 18px;
  right: 18px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 1px solid rgba(250, 246, 239, 0.15);
  background: rgba(255, 255, 255, 0.04);
  color: #FAF6EF;
  font-size: 22px;
  line-height: 1;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.wnm-close-btn:hover {
  background: rgba(229, 179, 88, 0.15);
  border-color: rgba(229, 179, 88, 0.5);
  color: #E5B358;
  transform: rotate(90deg);
}

.wnm-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(229, 179, 88, 0.12);
  border: 1px solid rgba(229, 179, 88, 0.35);
  color: #E5B358;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 12px;
}

.wnm-title {
  font-family: 'DM Serif Display', Georgia, serif;
  font-size: 24px;
  font-weight: 400;
  color: #FAF6EF;
  margin: 0 0 8px 0;
  line-height: 1.25;
}

.wnm-subtitle {
  font-size: 13px;
  line-height: 1.6;
  color: rgba(250, 246, 239, 0.75);
  margin: 0 0 20px 0;
}

/* Workshop pill box inside modal */
.wnm-workshop-box {
  display: flex;
  align-items: center;
  gap: 14px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(229, 179, 88, 0.2);
  border-radius: 12px;
  padding: 14px 16px;
  margin-bottom: 22px;
}

.wnm-workshop-icon {
  font-size: 24px;
  flex-shrink: 0;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(229, 179, 88, 0.1);
  border-radius: 10px;
  border: 1px solid rgba(229, 179, 88, 0.2);
}

.wnm-workshop-info {
  flex: 1;
  min-width: 0;
}

.wnm-workshop-title {
  font-family: 'DM Serif Display', Georgia, serif;
  font-size: 15.5px;
  color: #FAF6EF;
  margin: 0 0 6px 0;
  line-height: 1.35;
  white-space: normal;
}

.wnm-meta-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.wnm-tag {
  font-size: 11px;
  color: rgba(250, 246, 239, 0.8);
  background: rgba(255, 255, 255, 0.06);
  padding: 2px 8px;
  border-radius: 4px;
}

.wnm-tag-status {
  color: #E5B358;
  background: rgba(229, 179, 88, 0.15);
  font-weight: 600;
}

/* Form inputs & actions */
.wnm-label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #E5B358;
  margin-bottom: 8px;
}

.wnm-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.wnm-input-icon {
  position: absolute;
  left: 14px;
  color: rgba(250, 246, 239, 0.4);
  pointer-events: none;
}

.wnm-input {
  width: 100%;
  box-sizing: border-box;
  background: rgba(8, 17, 12, 0.85);
  border: 1.2px solid rgba(229, 179, 88, 0.3);
  color: #FAF6EF;
  padding: 13px 14px 13px 42px;
  border-radius: 10px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.wnm-input:focus {
  border-color: #E5B358;
  box-shadow: 0 0 0 3px rgba(229, 179, 88, 0.2);
}

.wnm-input::placeholder {
  color: rgba(250, 246, 239, 0.35);
}

.wnm-feedback {
  margin-top: 10px;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 12.5px;
  line-height: 1.4;
  text-align: left;
}

.wnm-feedback.error {
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.4);
  color: #FCA5A5;
}

.wnm-feedback.success {
  background: rgba(37, 211, 102, 0.12);
  border: 1px solid rgba(37, 211, 102, 0.4);
  color: #86EFAC;
}

.wnm-submit-btn {
  width: 100%;
  margin-top: 16px;
  background: #E5B358;
  color: #0F2016;
  border: none;
  border-radius: 10px;
  padding: 14px 20px;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.25s ease;
  box-shadow: 0 4px 16px rgba(229, 179, 88, 0.3);
}

.wnm-submit-btn:hover {
  background: #F0C475;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(229, 179, 88, 0.45);
}

.wnm-submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.wnm-trust-note {
  margin-top: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-size: 11.5px;
  color: rgba(250, 246, 239, 0.55);
}

/* Success State View */
.wnm-success-state {
  text-align: center;
  padding: 16px 8px;
}

.wnm-success-icon-wrap {
  width: 58px;
  height: 58px;
  margin: 0 auto 16px auto;
  border-radius: 50%;
  background: rgba(37, 211, 102, 0.12);
  border: 1px solid rgba(37, 211, 102, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
}

.wnm-success-title {
  font-family: 'DM Serif Display', Georgia, serif;
  font-size: 22px;
  color: #FAF6EF;
  margin: 0 0 10px 0;
}

.wnm-success-desc {
  font-size: 13.5px;
  line-height: 1.6;
  color: rgba(250, 246, 239, 0.8);
  margin: 0 0 24px 0;
}

.wnm-done-btn {
  background: transparent;
  border: 1.5px solid rgba(229, 179, 88, 0.5);
  color: #E5B358;
  padding: 11px 24px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.wnm-done-btn:hover {
  background: rgba(229, 179, 88, 0.15);
  border-color: #E5B358;
}

@media (max-width: 480px) {
  .wnm-card {
    padding: 26px 20px;
    border-radius: 16px;
  }
  .wnm-title {
    font-size: 21px;
  }
}
</style>

<script>
// Workshop Notification Modal Controller
(function() {
  window.openWorkshopNotifyModal = function(title, duration, level) {
    var modal = document.getElementById('workshop-notify-modal');
    if (!modal) {
      // Graceful fallback to footer form
      var footerForm = document.getElementById('newsletter-footer-form');
      if (footerForm) {
        footerForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        var inp = footerForm.querySelector('input[type="email"]');
        if (inp) setTimeout(function() { inp.focus(); }, 400);
      }
      return;
    }

    var titleEl = document.getElementById('wnm-title');
    var durationEl = document.getElementById('wnm-duration');
    var levelEl = document.getElementById('wnm-level');
    var hiddenTitleEl = document.getElementById('wnm-hidden-title');
    var formEl = document.getElementById('workshop-notify-form');
    var successEl = document.getElementById('wnm-success-state');
    var emailInput = document.getElementById('wnm-email');
    var feedbackEl = document.getElementById('wnm-feedback');

    var cleanTitle = title || 'Artisan Chocolate Masterclass';
    if (titleEl) titleEl.textContent = cleanTitle;
    if (durationEl) durationEl.textContent = duration ? ('⏱ ' + duration.replace('⏱', '').trim()) : '⏱ 1 Day';
    if (levelEl) levelEl.textContent = level || 'All Levels';
    if (hiddenTitleEl) hiddenTitleEl.value = cleanTitle;

    // Reset view
    if (formEl) {
      formEl.style.display = 'block';
      formEl.reset();
    }
    if (successEl) successEl.style.display = 'none';
    if (feedbackEl) {
      feedbackEl.style.display = 'none';
      feedbackEl.textContent = '';
      feedbackEl.className = 'wnm-feedback';
    }

    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('wnm-modal-open');

    if (emailInput) {
      setTimeout(function() {
        emailInput.focus();
      }, 350);
    }
  };

  window.closeWorkshopNotifyModal = function() {
    var modal = document.getElementById('workshop-notify-modal');
    if (modal) {
      modal.classList.remove('open');
      modal.setAttribute('aria-hidden', 'true');
    }
    document.body.classList.remove('wnm-modal-open');
  };

  // Backwards compatible triggerNewsletterAlert
  window.triggerNewsletterAlert = function(title) {
    window.openWorkshopNotifyModal(title || 'Artisan Chocolate Truffles & Ganache');
  };

  document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('workshop-notify-modal');
    if (!modal) return;

    // Close on backdrop click
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeWorkshopNotifyModal();
      }
    });

    // Close on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && modal.classList.contains('open')) {
        closeWorkshopNotifyModal();
      }
    });

    // Form submit AJAX
    var form = document.getElementById('workshop-notify-form');
    if (form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        var emailInput = document.getElementById('wnm-email');
        var feedback = document.getElementById('wnm-feedback');
        var submitBtn = document.getElementById('wnm-submit-btn');
        var hiddenTitle = document.getElementById('wnm-hidden-title');
        var honeypot = form.querySelector('input[name="nickname"]');

        var email = emailInput ? emailInput.value.trim() : '';
        var workshopTitle = (hiddenTitle && hiddenTitle.value) ? hiddenTitle.value : 'Artisan Workshop';

        if (!email || email.indexOf('@') === -1 || email.indexOf('.') === -1) {
          if (feedback) {
            feedback.style.display = 'block';
            feedback.className = 'wnm-feedback error';
            feedback.textContent = 'Please enter a valid email address.';
          }
          return;
        }

        var origBtnHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span>Saving...</span>';
        }
        if (feedback) feedback.style.display = 'none';

        var prefix = (typeof pathPrefix !== 'undefined' && pathPrefix) ? pathPrefix : '';

        fetch(prefix + 'subscribe.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            email: email,
            nickname: honeypot ? honeypot.value : '',
            source: 'Workshop Waitlist: ' + workshopTitle,
            workshop_title: workshopTitle
          })
        })
        .then(function(res) {
          return res.json();
        })
        .then(function(data) {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origBtnHtml;
          }

          if (data && data.status === 'success') {
            form.style.display = 'none';
            var successState = document.getElementById('wnm-success-state');
            var successDesc = document.getElementById('wnm-success-message');
            if (successState) successState.style.display = 'block';
            if (successDesc) {
              successDesc.textContent = "You're on the priority waitlist! We'll notify you as soon as registrations open for " + workshopTitle + ".";
            }
          } else {
            if (feedback) {
              feedback.style.display = 'block';
              feedback.className = 'wnm-feedback error';
              feedback.textContent = (data && data.message) ? data.message : 'Subscription could not be processed. Please try again.';
            }
          }
        })
        .catch(function(err) {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origBtnHtml;
          }
          // Graceful fallback to success message
          form.style.display = 'none';
          var successState = document.getElementById('wnm-success-state');
          var successDesc = document.getElementById('wnm-success-message');
          if (successState) successState.style.display = 'block';
          if (successDesc) {
            successDesc.textContent = "You're on the priority waitlist! We'll notify you as soon as registrations open for " + workshopTitle + ".";
          }
        });
      });
    }
  });
})();
</script>
