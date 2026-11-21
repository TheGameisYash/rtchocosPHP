/**
 * ==========================================================================
 * RT CHOCOS — BEAN TO BAR MASTER COMPONENT (bean-to-bar.js)
 * The Spine · Nine Stages of Chocolate Craftsmanship
 * Exclusively integrated inside the Chocolate Table interactive experience.
 * ==========================================================================
 */

(function() {
  'use strict';

  /* ============ BEAN TO BAR DATA ============ */
  const STAGES = [
    {
      t: 'Harvest',
      k: 'Ripeness window · 5–7 months on tree',
      d: 'Pods are cut, not pulled — the flower cushion they grow from will fruit again for decades. Ripeness is judged by colour shift and a hollow sound when tapped. Cut too early and the sugars in the pulp are too low to feed a proper fermentation; too late and the seeds germinate inside the pod.',
      s: [['Pods per kg dry bean', '~13'], ['Beans per pod', '30–45'], ['Break to open', '24–48 h']]
    },
    {
      t: 'Fermentation',
      k: 'Where 80% of flavour is decided',
      d: 'The pulp, not the bean, is what ferments. Yeasts take the sugars anaerobically, lactic acid bacteria follow, then acetic acid bacteria drive the temperature up and force acid and heat through the seed coat. The seed dies. Its storage proteins are cleaved into peptides and free amino acids — the precursors that roasting will later turn into chocolate aroma. Skip this and no roast profile on earth will save you.',
      s: [['Duration', '5–7 days'], ['Peak mass temp', '48–50 °C'], ['Target pH', '5.0–5.5']]
    },
    {
      t: 'Drying',
      k: 'Moisture down, acidity out',
      d: 'Slow sun-drying on raised beds lets residual acetic acid volatilise off. Rush it with mechanical heat and you seal acidity inside a case-hardened shell; go too slow in humid air and you invite mould and off-notes that survive every downstream step.',
      s: [['Duration', '7–14 days'], ['Final moisture', '6.5–7.5%'], ['Above 8%', 'Mould risk']]
    },
    {
      t: 'Sorting & cleaning',
      k: 'The unglamorous quality gate',
      d: 'Destoning, de-stringing and size grading. Then the cut test: fifty beans halved and read for slaty, purple, mouldy and insect-damaged. A batch that reads more than 10% purple has under-fermented and will taste raw and astringent no matter what you do to it.',
      s: [['Cut test sample', '50–300 beans'], ['Slaty tolerance', '<3%'], ['Mould tolerance', '<3%']]
    },
    {
      t: 'Roasting',
      k: 'Maillard, not caramelisation',
      d: 'Free amino acids and reducing sugars built during fermentation now react into pyrazines, aldehydes and pyrroles — the compounds we recognise as "chocolate". Fine flavour origins are roasted low and long to protect volatile fruit and floral notes; bulk beans take a harder roast to build body and burn off harshness.',
      s: [['Fine flavour', '110–130 °C'], ['Bulk / body', '130–150 °C'], ['Time', '15–40 min']]
    },
    {
      t: 'Winnowing',
      k: 'Separating nib from shell',
      d: 'Cracked beans are passed through an air column that lifts the light husk and drops the dense nib. Shell in the mass is gritty, carries mineral off-flavour and — above regulatory limits — is a compliance problem. Good winnowing is the difference between a smooth mass and a sandy one.',
      s: [['Shell in nib', '<1.5%'], ['Yield loss', '10–14%'], ['Husk use', 'Tea, mulch, pectin']]
    },
    {
      t: 'Grinding & refining',
      k: 'Below the tongue threshold',
      d: 'Nibs are ground until frictional heat liquefies their ~54% cocoa butter into cocoa mass. Sugar and milk solids join, then the whole mass is refined until the largest particles fall under about 20 microns — the point at which the tongue stops registering grit.',
      s: [['Target particle size', '<20 µm'], ['Nib fat content', '52–56%'], ['Roll refiner passes', '2–5']]
    },
    {
      t: 'Conching',
      k: 'Time, shear and volatile loss',
      d: 'Hours of heated agitation. Residual acetic acid and short-chain volatiles evaporate off, moisture drops below 1%, and every particle gets fully coated in fat — which is what actually drops viscosity and delivers flow. Under-conche and it tastes sharp; over-conche and you have sanded the character off a good bean.',
      s: [['Dark', '12–48 h'], ['Milk', '8–24 h'], ['Final moisture', '<1%']]
    },
    {
      t: 'Tempering & moulding',
      k: 'Form V or nothing',
      d: 'Cocoa butter can crystallise into six polymorphic forms. Only Form V gives gloss, a clean snap, contraction for demoulding and resistance to bloom. Getting there means melting out all crystal memory, seeding the mass at a lower temperature, then warming just enough to melt away the unstable forms and leave Form V behind.',
      s: [['Dark working temp', '31–32 °C'], ['Contraction', '~3%'], ['Shelf life, tempered', '12–24 months']]
    }
  ];

  /* ============ RENDER STAGES ============ */
  function renderModalStages() {
    const container = document.getElementById('modalStages');
    if (!container) return;

    container.innerHTML = STAGES.map((s, i) => `
      <div class="modal-stage${i === 1 ? ' on' : ''}" data-i="${i}" tabindex="0" role="button" aria-expanded="${i === 1 ? 'true' : 'false'}">
        <div class="modal-stage-num">${String(i + 1).padStart(2, '0')}</div>
        <div class="modal-stage-main">
          <h4>${s.t}</h4>
          <div class="modal-stage-key">${s.k}</div>
          <div class="modal-stage-body">
            <p>${s.d}</p>
            <div class="modal-stage-specs">${s.s.map(x => `<span class="spec">${x[0]} · <b>${x[1]}</b></span>`).join('')}</div>
          </div>
        </div>
      </div>`).join('');

    container.querySelectorAll('.modal-stage').forEach(st => {
      st.addEventListener('click', () => {
        const was = st.classList.contains('on');
        container.querySelectorAll('.modal-stage').forEach(s => {
          s.classList.remove('on');
          s.setAttribute('aria-expanded', 'false');
        });
        if (!was) {
          st.classList.add('on');
          st.setAttribute('aria-expanded', 'true');
        }
        updateModalSpine();
      });

      st.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          st.click();
        }
      });
    });
  }

  /* ============ MODAL SCROLL SPINE PROGRESS ============ */
  function updateModalSpine() {
    const scrollBody = document.getElementById('modalB2bScrollBody');
    const modalStages = document.getElementById('modalStages');
    const modalSpineFill = document.getElementById('modalSpineFill');
    if (!scrollBody || !modalStages || !modalSpineFill) return;

    const sp = modalStages.getBoundingClientRect();
    const mb = scrollBody.getBoundingClientRect();
    const f = Math.max(0, Math.min(1, (mb.top + mb.height * 0.45 - sp.top) / sp.height));
    modalSpineFill.style.height = (f * 100) + '%';
  }

  /* ============ MODAL CONTROLS ============ */
  window.openBeanToBarGuideModal = function() {
    const modal = document.getElementById('modal-beantobar-viewer');
    if (!modal) return;

    const scrollBarWidth = window.innerWidth - document.documentElement.clientWidth;
    if (scrollBarWidth > 0) {
      document.body.style.paddingRight = scrollBarWidth + 'px';
    }
    document.body.style.overflow = 'hidden';
    modal.classList.add('active');

    // Render modal stages if not already done
    const modalStages = document.getElementById('modalStages');
    if (modalStages && !modalStages.hasChildNodes()) {
      renderModalStages();
    }

    setTimeout(updateModalSpine, 120);
  };

  window.closeBeanToBarGuideModal = function() {
    const modal = document.getElementById('modal-beantobar-viewer');
    if (!modal) return;

    modal.classList.remove('active');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
  };

  // Keyboard accessibility: Escape closes the modal
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeBeanToBarGuideModal();
    }
  });

  /* ============ INITIALIZATION ============ */
  document.addEventListener('DOMContentLoaded', function() {
    renderModalStages();

    const modalScrollBody = document.getElementById('modalB2bScrollBody');
    if (modalScrollBody) {
      modalScrollBody.addEventListener('scroll', updateModalSpine, { passive: true });
    }
  });
})();
