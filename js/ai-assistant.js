// --- AI CHAT DRAWER STATE & FUNCTIONS ---
let aiChatHistory = [];

function toggleAiDrawer() {
  const drawer = document.getElementById('ai-chat-drawer');
  const overlay = document.getElementById('ai-drawer-overlay');
  if (drawer && overlay) {
    const isOpen = drawer.classList.toggle('open');
    drawer.setAttribute('aria-hidden', !isOpen);
    document.querySelectorAll('.ai-drawer-trigger').forEach(btn => btn.setAttribute('aria-expanded', isOpen));
    if (isOpen) {
      overlay.classList.add('visible');
      document.body.style.overflow = 'hidden';
      const input = document.getElementById('ai-chat-input');
      if (input) setTimeout(() => input.focus(), 150);
    } else {
      overlay.classList.remove('visible');
      document.body.style.overflow = '';
    }
  }
}

function appendAiMessage(role, text) {
  const container = document.getElementById('ai-chat-messages');
  if (!container) return;

  const msgDiv = document.createElement('div');
  msgDiv.className = `ai-message ${role}`;

  let formattedText = text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");

  formattedText = formattedText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
  formattedText = formattedText.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank" rel="noopener" style="color: var(--accent); text-decoration: underline; font-weight: 600;">$1</a>');
  formattedText = formattedText.replace(/\n/g, '<br>');

  msgDiv.innerHTML = `<div class="ai-msg-bubble">${formattedText}</div>`;
  container.appendChild(msgDiv);
  container.scrollTop = container.scrollHeight;
}

function showAiTypingIndicator() {
  const container = document.getElementById('ai-chat-messages');
  if (!container) return;

  const indicator = document.createElement('div');
  indicator.id = 'ai-typing-indicator';
  indicator.className = 'ai-typing-indicator';
  indicator.innerHTML = `
    <span class="ai-typing-dot"></span>
    <span class="ai-typing-dot"></span>
    <span class="ai-typing-dot"></span>
  `;
  container.appendChild(indicator);
  container.scrollTop = container.scrollHeight;
}

function removeAiTypingIndicator() {
  const indicator = document.getElementById('ai-typing-indicator');
  if (indicator) {
    indicator.remove();
  }
}

async function callAiApi(message) {
  showAiTypingIndicator();

  try {
    const isBlogSubfolder = window.location.pathname.includes('/blog/');
    const prefix = isBlogSubfolder ? '../' : '';

    const response = await fetch(prefix + 'api_ai.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        message: message,
        history: aiChatHistory
      })
    });

    removeAiTypingIndicator();

    if (response.ok) {
      const data = await response.json();
      if (data.reply) {
        appendAiMessage('ai', data.reply);
        aiChatHistory.push({ role: 'user', text: message });
        aiChatHistory.push({ role: 'model', text: data.reply });
        if (aiChatHistory.length > 20) {
          aiChatHistory = aiChatHistory.slice(-20);
        }
      } else if (data.error) {
        appendAiMessage('ai', `Sorry, I encountered an error: ${data.error}`);
      }
    } else {
      appendAiMessage('ai', "Sorry, I am unable to connect to the backend AI proxy right now.");
    }
  } catch (error) {
    removeAiTypingIndicator();
    appendAiMessage('ai', "Error sending message. Please check your network connection.");
    console.error("AI chat error:", error);
  }
}

function handleAiChatSubmit(e) {
  e.preventDefault();
  const input = document.getElementById('ai-chat-input');
  if (!input) return;

  const text = input.value.trim();
  if (!text) return;

  input.value = '';
  appendAiMessage('user', text);
  callAiApi(text);
}

function sendQuickPrompt(promptText) {
  appendAiMessage('user', promptText);
  callAiApi(promptText);
}

function sendTroubleshootQuery(promptText) {
  const container = document.getElementById('ai-chat-messages');
  if (container) {
    container.innerHTML = `
      <div class="ai-message system">
        <div class="ai-msg-bubble">
          Hello! I am <strong>CocoaGenius AI</strong>, your expert guide to the science, craft, and chemistry of chocolate making. How can I help you today?
        </div>
      </div>
    `;
    aiChatHistory = [];
  }

  toggleAiDrawer();
  appendAiMessage('user', promptText);
  callAiApi(promptText);
}

async function loadDynamicAiInsight() {
  try {
    const isBlogSubfolder = window.location.pathname.includes('/blog/');
    const prefix = isBlogSubfolder ? '../' : '';

    // Trigger background generation for the next visit
    await fetch(prefix + 'api_generate_insight.php');
  } catch (err) {
    console.error("Failed to trigger background AI insight generation:", err);
  }
}

async function loadDynamicAiRecipe() {
  const titleEl = document.getElementById('ai-dynamic-recipe-title');
  const descEl = document.getElementById('ai-dynamic-recipe-desc');
  if (!titleEl || !descEl) return;

  try {
    const isBlogSubfolder = window.location.pathname.includes('/blog/');
    const prefix = isBlogSubfolder ? '../' : '';

    const response = await fetch(prefix + 'api_ai.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        message: "Create a unique, creative, high-end chocolate flavor formulation pairing (e.g. Cardamom, Sea Salt, and Rosemary Dark Chocolate). Provide the name of the recipe in line 1, and a brief mouthwatering description in line 2 (under 40 words). Return ONLY the name on line 1, and description on line 2, separated by a pipe character '|' (e.g., Recipe Name|Description)."
      })
    });
    if (response.ok) {
      const data = await response.json();
      if (data.reply) {
        const parts = data.reply.trim().split('|');
        if (parts.length >= 2) {
          titleEl.textContent = parts[0].trim();
          descEl.textContent = parts[1].trim();
        } else {
          titleEl.textContent = "Lavender & Sea Salt Ganache";
          descEl.textContent = data.reply.trim();
        }
      }
    }
  } catch (err) {
    console.error("Failed to load AI recipe:", err);
    titleEl.textContent = "Chilli & Lime Dark Truffles";
    descEl.textContent = "A fiery kick of bird's eye chilli paired with fresh lime zest in an organic 70% Malabar dark chocolate shell.";
  }
}

async function loadDynamicAiClassInsight() {
  const el = document.getElementById('ai-dynamic-class-insight');
  if (!el) return;

  try {
    const isBlogSubfolder = window.location.pathname.includes('/blog/');
    const prefix = isBlogSubfolder ? '../' : '';

    const response = await fetch(prefix + 'api_ai.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        message: "Provide a single, professional tip about tempering curves, crystal polymorphs, or roasting parameters in chocolate making (under 30 words). Return ONLY the tip text."
      })
    });
    if (response.ok) {
      const data = await response.json();
      if (data.reply) {
        el.textContent = data.reply.trim();
      }
    }
  } catch (err) {
    console.error("Failed to load AI class insight:", err);
    el.textContent = "Stable Form V crystallization occurs best when dark chocolate is held between 31°C and 32°C.";
  }
}

// --- AI CHOCOLAB FORMULATION ENGINE ---

// Helper: Update percentage slider label & descriptor
function updatePercentRangeHint() {
  const percentEl = document.getElementById('chocolab-percent');
  const valTag = document.getElementById('chocolab-percent-val');
  if (!percentEl || !valTag) return;

  const val = parseInt(percentEl.value, 10);
  let hint = 'Sweet Confection';
  if (val >= 85) hint = 'Intense Bittersweet';
  else if (val >= 70) hint = 'Bittersweet Balance';
  else if (val >= 55) hint = 'Semi-Sweet Classic';
  else if (val >= 40) hint = 'Creamy Mild';
  else hint = 'Sweet Confection';

  valTag.textContent = `${val}% (${hint})`;
}

// Helper: Inclusion Check Limit (Max 3) & Counter
function handleInclusionCheck(changedEl) {
  const checkboxes = document.querySelectorAll('input[name="inclusions"]:checked');
  const counterEl = document.getElementById('chocolab-inc-counter');
  
  if (checkboxes.length > 3) {
    changedEl.checked = false;
    alert("You can select a maximum of 3 inclusions for optimum flavor balance.");
    return;
  }
  
  if (counterEl) {
    counterEl.textContent = `${checkboxes.length} / 3 Selected`;
  }
}

// Helper: Apply Preset Inspirations
function applyChocolabPreset(baseVal, percentVal, inclusionsArr) {
  const baseEl = document.getElementById('chocolab-base');
  const percentEl = document.getElementById('chocolab-percent');
  const checkboxes = document.querySelectorAll('input[name="inclusions"]');
  
  if (baseEl) baseEl.value = baseVal;
  if (percentEl) {
    percentEl.value = percentVal;
    updatePercentRangeHint();
  }
  
  checkboxes.forEach(cb => {
    cb.checked = inclusionsArr.includes(cb.value);
  });

  const counterEl = document.getElementById('chocolab-inc-counter');
  if (counterEl) {
    const activeCount = document.querySelectorAll('input[name="inclusions"]:checked').length;
    counterEl.textContent = `${activeCount} / 3 Selected`;
  }
  
  generateCustomBarFormula();
}

// Store current formulation globally for copy/print actions
let currentChocolabRecipe = null;

// Core AI Formulation Execution
async function generateCustomBarFormula() {
  const base = document.getElementById('chocolab-base')?.value || 'Dark Chocolate';
  const percent = parseInt(document.getElementById('chocolab-percent')?.value || '72', 10);
  const batchGrams = parseInt(document.getElementById('chocolab-batch')?.value || '500', 10);
  const checkboxes = document.querySelectorAll('input[name="inclusions"]:checked');

  const inclusions = Array.from(checkboxes).map(cb => cb.value);
  if (inclusions.length > 3) {
    alert("Please select a maximum of 3 inclusions for your chocolate bar formulation.");
    return;
  }

  const placeholder = document.getElementById('chocolab-placeholder');
  const loader = document.getElementById('chocolab-loader');
  const results = document.getElementById('chocolab-results');
  const loaderStatus = document.getElementById('chocolab-loader-status');

  if (!placeholder || !loader || !results) return;

  placeholder.style.display = 'none';
  results.style.display = 'none';
  loader.style.display = 'block';

  if (loaderStatus) {
    loaderStatus.textContent = "Calculating fat-to-sugar ratios & Form V tempering points...";
  }

  const inclusionsText = inclusions.length > 0 ? inclusions.join(', ') : 'No inclusions (Pure Bar)';
  
  const prompt = `Formulate a precise, professional chocolate bar recipe profile in strict JSON format.
Input Specifications:
- Base: ${base}
- Cacao Percentage: ${percent}%
- Target Batch Weight: ${batchGrams}g
- Gourmet Inclusions: ${inclusionsText}

Return ONLY valid JSON with no markdown block markers, matching this exact structure:
{
  "name": "Creative Signature Bar Name",
  "description": "Evocative, mouthwatering description under 35 words.",
  "batch_grams": ${batchGrams},
  "ratios": {
    "cacao_mass": "XXXg (XX%)",
    "cacao_butter": "XXXg (XX%)",
    "sugar": "XXXg (XX%)",
    "inclusions": "XXXg (XX%)"
  },
  "sensory": {
    "aroma": "Aroma profile",
    "flavor": "Taste breakdown",
    "texture": "Texture & snap",
    "finish": "Aftertaste & finish"
  },
  "steps": [
    {"num": 1, "title": "Bean Roasting & Nib Melting", "temp": "45°C–50°C (113°F–122°F)", "detail": "Exact short action step."},
    {"num": 2, "title": "Micro-Conching & Refining", "temp": "45°C (113°F)", "detail": "Conch time and micrometer target."},
    {"num": 3, "title": "Form V Precision Tempering Curve", "temp": "Melt 45°C ➔ Cool 27°C ➔ Work 31.5°C", "detail": "Exact temperature curve."},
    {"num": 4, "title": "Inclusion Integration & Moulding", "temp": "31.5°C (88.7°F)", "detail": "Adding inclusions and vibrating moulds."},
    {"num": 5, "title": "Crystallization & Demoulding", "temp": "14°C–16°C (57°F–60°F)", "detail": "Cooling duration and glossy snap finish."}
  ],
  "pro_tip": "Pro advice from Master Chocolatier Aarti Saluja Sahni."
}`;

  try {
    const isBlogSubfolder = window.location.pathname.includes('/blog/');
    const prefix = isBlogSubfolder ? '../' : '';

    const response = await fetch(prefix + 'api_ai.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: prompt })
    });

    if (response.ok) {
      const data = await response.json();
      if (data.reply) {
        let recipe = parseAiRecipeResponse(data.reply, base, percent, inclusions, batchGrams);
        currentChocolabRecipe = recipe;
        renderChocolabRecipe(recipe);
        loader.style.display = 'none';
        results.style.display = 'block';
        return;
      }
    }

    // Fallback if API response is invalid or unavailable
    const fallbackRecipe = buildScientificFallbackRecipe(base, percent, inclusions, batchGrams);
    currentChocolabRecipe = fallbackRecipe;
    renderChocolabRecipe(fallbackRecipe);
    loader.style.display = 'none';
    results.style.display = 'block';

  } catch (err) {
    console.warn("AI API unreachable, rendering scientific offline formulation:", err);
    const fallbackRecipe = buildScientificFallbackRecipe(base, percent, inclusions, batchGrams);
    currentChocolabRecipe = fallbackRecipe;
    renderChocolabRecipe(fallbackRecipe);
    loader.style.display = 'none';
    results.style.display = 'block';
  }
}

// Parse raw AI text or JSON safely
function parseAiRecipeResponse(replyText, base, percent, inclusions, batchGrams) {
  try {
    // Strip markdown code fences if present
    let cleanStr = replyText.replace(/```json/gi, '').replace(/```/g, '').trim();
    
    // Find first { and last }
    const firstBrace = cleanStr.indexOf('{');
    const lastBrace = cleanStr.lastIndexOf('}');
    if (firstBrace !== -1 && lastBrace !== -1) {
      cleanStr = cleanStr.substring(firstBrace, lastBrace + 1);
      const parsed = JSON.parse(cleanStr);
      if (parsed.name && parsed.steps && Array.isArray(parsed.steps)) {
        parsed.base = base;
        parsed.percent = percent;
        return parsed;
      }
    }
  } catch (e) {
    console.warn("Failed JSON parse of AI output, attempting pipe format fallback:", e);
  }

  // Legacy pipe parser fallback if AI returned old format
  const parts = replyText.trim().split('|');
  if (parts.length >= 4) {
    return {
      name: parts[0].trim(),
      description: parts[1].trim(),
      base: base,
      percent: percent,
      batch_grams: batchGrams,
      ratios: calculateScientificRatios(base, percent, inclusions, batchGrams),
      sensory: {
        aroma: "Complex cacao roast with subtle floral & spice notes.",
        flavor: parts[2].trim(),
        texture: "Smooth velvet melt with clean snap.",
        finish: "Lingering cocoa intensity."
      },
      steps: buildStandardSteps(base, percent, inclusions, parts[3].trim()),
      pro_tip: "Ensure mold temperature is 20°C–22°C before pouring to prevent fat bloom."
    };
  }

  return buildScientificFallbackRecipe(base, percent, inclusions, batchGrams);
}

// Render structured recipe into DOM elements
function renderChocolabRecipe(recipe) {
  document.getElementById('chocolab-result-base').textContent = `${recipe.percent}% ${recipe.base} (${recipe.batch_grams || 500}g Batch)`;
  document.getElementById('chocolab-result-name').textContent = recipe.name;
  document.getElementById('chocolab-result-desc').textContent = recipe.description;
  
  const batchDisplay = document.getElementById('chocolab-batch-display');
  if (batchDisplay) batchDisplay.textContent = `${recipe.batch_grams || 500}g`;

  // Render Ratios Cards
  const ratiosContainer = document.getElementById('chocolab-result-ratios');
  if (ratiosContainer && recipe.ratios) {
    ratiosContainer.innerHTML = `
      <div class="ratio-card">
        <span class="ratio-icon">🟤</span>
        <span class="ratio-label">Cacao Mass / Nibs</span>
        <span class="ratio-val">${recipe.ratios.cacao_mass || '270g (54%)'}</span>
      </div>
      <div class="ratio-card">
        <span class="ratio-icon">🧈</span>
        <span class="ratio-label">Cocoa Butter</span>
        <span class="ratio-val">${recipe.ratios.cacao_butter || '90g (18%)'}</span>
      </div>
      <div class="ratio-card">
        <span class="ratio-icon">🍬</span>
        <span class="ratio-label">Organic Sugar / Dairy</span>
        <span class="ratio-val">${recipe.ratios.sugar || '125g (25%)'}</span>
      </div>
      <div class="ratio-card">
        <span class="ratio-icon">✨</span>
        <span class="ratio-label">Gourmet Inclusions</span>
        <span class="ratio-val">${recipe.ratios.inclusions || '15g (3%)'}</span>
      </div>
    `;
  }

  // Render Sensory Cards
  const sensoryContainer = document.getElementById('chocolab-result-sensory');
  if (sensoryContainer && recipe.sensory) {
    sensoryContainer.innerHTML = `
      <div class="sensory-card">
        <div class="sensory-card-title">👃 Aroma</div>
        <div class="sensory-card-text">${recipe.sensory.aroma || 'Warm cocoa with toasted nut notes'}</div>
      </div>
      <div class="sensory-card">
        <div class="sensory-card-title">👅 Flavor Notes</div>
        <div class="sensory-card-text">${recipe.sensory.flavor || 'Rich bittersweet chocolate balance'}</div>
      </div>
      <div class="sensory-card">
        <div class="sensory-card-title">✨ Texture &amp; Snap</div>
        <div class="sensory-card-text">${recipe.sensory.texture || 'Crisp acoustic snap, silky tongue melt'}</div>
      </div>
      <div class="sensory-card">
        <div class="sensory-card-title">🏁 Finish</div>
        <div class="sensory-card-text">${recipe.sensory.finish || 'Clean long-lasting cocoa aftertaste'}</div>
      </div>
    `;
  }

  // Render 5-Step Process Timeline
  const stepsContainer = document.getElementById('chocolab-result-steps');
  if (stepsContainer && recipe.steps) {
    stepsContainer.innerHTML = recipe.steps.map((st, idx) => `
      <div class="timeline-step">
        <div class="step-badge">0${st.num || idx + 1}</div>
        <div class="step-content">
          <div class="step-header">
            <h6 class="step-title">${st.title}</h6>
            <span class="step-temp-tag">${st.temp}</span>
          </div>
          <p class="step-desc">${st.detail}</p>
        </div>
      </div>
    `).join('');
  }

  // Render Pro Tip
  const proTipEl = document.getElementById('chocolab-result-protip');
  if (proTipEl) {
    proTipEl.textContent = recipe.pro_tip || "Always maintain ambient room temperature around 18°C–20°C with humidity below 50% for optimal Form V crystal formation.";
  }
}

// Generate scientific fallback recipe
function buildScientificFallbackRecipe(base, percent, inclusions, batchGrams) {
  const incText = inclusions.length > 0 ? inclusions.join(' & ') : 'Pure Origin';
  const ratios = calculateScientificRatios(base, percent, inclusions, batchGrams);
  
  let workingTemp = '31.5°C (88.7°F)';
  let meltTemp = '45°C–48°C (113°F–118°F)';
  let coolTemp = '27°C (80.6°F)';

  if (base.includes('Milk')) {
    workingTemp = '29.5°C–30°C (85.1°F–86°F)';
    coolTemp = '26°C (78.8°F)';
  } else if (base.includes('White')) {
    workingTemp = '28.5°C–29°C (83.3°F–84.2°F)';
    coolTemp = '25.5°C (77.9°F)';
  }

  return {
    name: `${percent}% ${base} with ${incText}`,
    description: `A masterfully balanced ${percent}% ${base.toLowerCase()} formulation infused with ${incText}. Engineered for a brilliant acoustic snap and silky melt.`,
    base: base,
    percent: percent,
    batch_grams: batchGrams,
    ratios: ratios,
    sensory: {
      aroma: `Deep roast cacao bouquet highlighted by natural notes of ${incText}.`,
      flavor: `Harmonious interplay of ${percent}% cacao richness balanced by the delicate warmth of ${incText}.`,
      texture: `Extremely smooth, refined particle size (<18 microns) with an acoustic glossy snap.`,
      finish: `Clean, lingering cacao finish with zero fat coating on the palate.`
    },
    steps: [
      {
        num: 1,
        title: "Bean Roasting & Nib Melting",
        temp: meltTemp,
        detail: `Liquefy ${ratios.cacao_mass} of cacao nibs/liquor and ${ratios.cacao_butter} of pure cocoa butter in a double boiler until smooth.`
      },
      {
        num: 2,
        title: "Micro-Conching & Refining",
        temp: "45°C (113°F)",
        detail: `Conch in stone melanger for 16-24 hours. Gradually add ${ratios.sugar} organic sugar until particle size drops below 18 microns.`
      },
      {
        num: 3,
        title: "Form V Precision Tempering Curve",
        temp: `Melt ${meltTemp} ➔ Cool ${coolTemp} ➔ Work ${workingTemp}`,
        detail: `Heat to ${meltTemp}, tabling 2/3 of batch on marble to ${coolTemp} to seed Beta V crystals, then agitate back to working temp ${workingTemp}.`
      },
      {
        num: 4,
        title: "Inclusion Integration & Moulding",
        temp: workingTemp,
        detail: `Gently fold in ${ratios.inclusions} of ${incText}. Pour into pre-warmed 21°C polycarbonate moulds and vibrate vigorously to dislodge micro air bubbles.`
      },
      {
        num: 5,
        title: "Crystallization & Demoulding",
        temp: "14°C–16°C (57°F–60°F)",
        detail: `Chill for 18–22 minutes. Cacao contraction will release bars effortlessly from moulds with a glass-like sheen.`
      }
    ],
    pro_tip: `For ${base}, never exceed working temperature during moulding. A 1°C overheat destroys Form V crystals, causing bloom.`
  };
}

// Calculate exact gram ratios based on percentage and batch weight
function calculateScientificRatios(base, percent, inclusions, batchGrams) {
  const incPercent = inclusions.length > 0 ? 3 : 0;
  const cacaoPercent = percent;
  const sugarPercent = Math.max(0, 100 - cacaoPercent - incPercent);

  // Split cacao into mass (75%) and butter (25% added butter)
  const cacaoGrams = Math.round(batchGrams * (cacaoPercent / 100));
  const cacaoMassGrams = Math.round(cacaoGrams * 0.75);
  const cacaoButterGrams = Math.round(cacaoGrams * 0.25);
  const sugarGrams = Math.round(batchGrams * (sugarPercent / 100));
  const incGrams = Math.round(batchGrams * (incPercent / 100));

  return {
    cacao_mass: `${cacaoMassGrams}g (${Math.round((cacaoMassGrams / batchGrams) * 100)}%)`,
    cacao_butter: `${cacaoButterGrams}g (${Math.round((cacaoButterGrams / batchGrams) * 100)}%)`,
    sugar: `${sugarGrams}g (${sugarPercent}%)`,
    inclusions: incGrams > 0 ? `${incGrams}g (${incPercent}%)` : `0g (0%)`
  };
}

// Build standard steps from text input
function buildStandardSteps(base, percent, inclusions, textGuide) {
  return [
    { num: 1, title: "Roasting & Melting", temp: "45°C–50°C", detail: "Melt cacao liquor and cocoa butter smoothly." },
    { num: 2, title: "Conching & Refining", temp: "45°C", detail: "Refine ingredients for smooth mouthfeel under 20 microns." },
    { num: 3, title: "Tempering Curve", temp: "Melt 45°C ➔ Cool 27°C ➔ Work 31°C", detail: textGuide },
    { num: 4, title: "Inclusion Integration", temp: "31°C", detail: "Fold inclusions and mould into bar frames." },
    { num: 5, title: "Demoulding & Storage", temp: "15°C", detail: "Cool for 20 mins until glossy contraction occurs." }
  ];
}

// Copy Recipe text to clipboard
function copyChocolabRecipe() {
  if (!currentChocolabRecipe) return;
  const r = currentChocolabRecipe;
  let text = `✨ RT CHOCOS FORMULATION SHEET ✨\n`;
  text += `Bar: ${r.name}\nBase: ${r.percent}% ${r.base} (${r.batch_grams || 500}g Batch)\n`;
  text += `Description: ${r.description}\n\n`;
  text += `⚖️ INGREDIENT RATIOS:\n`;
  if (r.ratios) {
    text += `- Cacao Mass: ${r.ratios.cacao_mass}\n`;
    text += `- Cocoa Butter: ${r.ratios.cacao_butter}\n`;
    text += `- Sugar/Dairy: ${r.ratios.sugar}\n`;
    text += `- Inclusions: ${r.ratios.inclusions}\n\n`;
  }
  text += `⏱️ 5-STEP PROCESS TIMELINE:\n`;
  if (r.steps) {
    r.steps.forEach(st => {
      text += `Step ${st.num} [${st.title}] (${st.temp}): ${st.detail}\n`;
    });
  }
  text += `\n🎓 PRO TIP: ${r.pro_tip}\n`;

  navigator.clipboard.writeText(text).then(() => {
    alert("Master recipe copied to clipboard!");
  }).catch(err => {
    console.error("Clipboard copy error:", err);
  });
}

// Print Recipe Sheet formatted window
function printChocolabRecipe() {
  if (!currentChocolabRecipe) return;
  const r = currentChocolabRecipe;
  const printWindow = window.open('', '_blank');
  if (!printWindow) return;

  const html = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>${r.name} - RT Chocos Formulation Sheet</title>
      <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; padding: 40px; color: #1a1a1a; line-height: 1.6; }
        h1 { color: #1a5f35; border-bottom: 2px solid #c9956b; padding-bottom: 10px; margin-bottom: 5px; }
        .meta { color: #666; font-size: 14px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .section-title { font-size: 16px; font-weight: bold; color: #1a5f35; text-transform: uppercase; margin-top: 25px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 10px; }
        .card { background: #f9f8f6; padding: 12px 16px; border-radius: 8px; border: 1px solid #e8e4df; }
        .card-label { font-size: 11px; text-transform: uppercase; color: #888; font-weight: bold; }
        .card-val { font-size: 15px; font-weight: bold; color: #1a5f35; }
        .step { margin-top: 15px; padding-left: 15px; border-left: 3px solid #c9956b; }
        .step-title { font-weight: bold; font-size: 15px; color: #1a1a1a; }
        .step-temp { display: inline-block; background: #1a5f35; color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 4px; margin-left: 8px; }
        .protip { background: #f4efe9; border-left: 4px solid #1a5f35; padding: 15px; margin-top: 30px; font-style: italic; }
      </style>
    </head>
    <body>
      <h1>${r.name}</h1>
      <div class="meta">${r.percent}% ${r.base} • ${r.batch_grams || 500}g Batch • Formulated by CocoaGenius AI</div>
      <p><em>${r.description}</em></p>

      <div class="section-title">⚖️ Precision Ratios</div>
      <div class="grid">
        <div class="card"><div class="card-label">Cacao Mass / Nibs</div><div class="card-val">${r.ratios?.cacao_mass || ''}</div></div>
        <div class="card"><div class="card-label">Cocoa Butter</div><div class="card-val">${r.ratios?.cacao_butter || ''}</div></div>
        <div class="card"><div class="card-label">Organic Sugar / Dairy</div><div class="card-val">${r.ratios?.sugar || ''}</div></div>
        <div class="card"><div class="card-label">Inclusions</div><div class="card-val">${r.ratios?.inclusions || ''}</div></div>
      </div>

      <div class="section-title">⏱️ 5-Step Master Crafting Process</div>
      ${(r.steps || []).map(s => `
        <div class="step">
          <div class="step-title">Step 0${s.num}: ${s.title} <span class="step-temp">${s.temp}</span></div>
          <div>${s.detail}</div>
        </div>
      `).join('')}

      <div class="protip">
        <strong>🎓 Master Chocolatier Pro Tip (Aarti Saluja Sahni):</strong><br>
        ${r.pro_tip}
      </div>

      <script>
        window.onload = function() { window.print(); }
      </script>
    </body>
    </html>
  `;

  printWindow.document.write(html);
  printWindow.document.close();
}

// Newsletter popup after 8 seconds, only if not previously closed
setTimeout(() => {
  if (localStorage.getItem('rtchocos-newsletter-closed') !== 'true') {
    const popup = document.getElementById('newsletter-popup');
    if (popup) {
      popup.classList.add('open');
    }
  }
}, 8000);

// === INTERACTIVE ABOUT PAGE HANDLERS ===

function toggleTimelineMilestone(node) {
  const isOpen = node.classList.contains('active');
  document.querySelectorAll('.timeline-node').forEach(n => n.classList.remove('active'));
  if (!isOpen) {
    node.classList.add('active');
  }
}

async function askAboutAiCompanion(question) {
  const output = document.getElementById('about-ai-chat-output');
  const loader = document.getElementById('about-ai-loader');
  if (!output || !loader) return;

  // Append user message bubble
  const userBubble = document.createElement('div');
  userBubble.className = 'ai-message user about-bubble-user';
  userBubble.textContent = question;
  output.appendChild(userBubble);
  output.scrollTop = output.scrollHeight;

  loader.style.display = 'flex';

  try {
    const isBlogSubfolder = window.location.pathname.includes('/blog/');
    const prefix = isBlogSubfolder ? '../' : '';

    const promptText = `System Instructions: You are the AI companion of Aarti Saluja Sahni, the professional chocolate maker and recipe consultant. Respond in Aarti's systems-thinking, science-first educator voice (strictly under 45 words). If asked about recipes or techniques, focus on crystallisation chemistry, conching, or farm fermentation.
Question: ${question}`;

    const response = await fetch(prefix + 'api_ai.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: promptText })
    });

    loader.style.display = 'none';

    if (response.ok) {
      const data = await response.json();
      if (data.reply) {
        const replyBubble = document.createElement('div');
        replyBubble.className = 'ai-message system about-bubble-bot';
        let cleaned = data.reply.trim().replace(/^Companion:\s*/i, '');
        let formatted = cleaned
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
          .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank" rel="noopener" style="color: var(--accent); text-decoration: underline; font-weight: 600;">$1</a>')
          .replace(/\n/g, '<br>');
        replyBubble.innerHTML = formatted;
        output.appendChild(replyBubble);
        output.scrollTop = output.scrollHeight;
      }
    }
  } catch (err) {
    loader.style.display = 'none';
    console.error("About page AI error:", err);
  }
}

function sendAboutAiPrompt(question) {
  askAboutAiCompanion(question);
}

function handleAboutAiSubmit() {
  const input = document.getElementById('about-ai-input');
  if (!input || !input.value.trim()) return;
  const query = input.value.trim();
  input.value = '';
  askAboutAiCompanion(query);
}

// Add enter key support for the about chat input
document.addEventListener("DOMContentLoaded", () => {
  const aboutInput = document.getElementById('about-ai-input');
  if (aboutInput) {
    aboutInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        handleAboutAiSubmit();
      }
    });
  }
});

function selectPhilosophy(type, element) {
  document.querySelectorAll('.matcher-card').forEach(card => card.classList.remove('active'));
  element.classList.add('active');

  const resultBox = document.getElementById('matcher-result-box');
  const resultTitle = document.getElementById('matcher-result-title');
  const resultDesc = document.getElementById('matcher-result-desc');

  if (!resultBox || !resultTitle || !resultDesc) return;

  let title = "";
  let desc = "";

  switch(type) {
    case 'science':
      title = "🔬 The Cacao Chemist (Tempering & Crystal Science)";
      desc = "You believe that exceptional chocolate is built on precise molecular control. Your ideal learning path starts with the 'The Science of Tempering & Cocoa Crystallization' Masterclass to master polymorph Form V structures, temper curves, and water activity chemistry.";
      break;
    case 'practical':
      title = "👩‍🍳 The Artisan Confectioner (Ganache & Bonbons)";
      desc = "You love the aesthetic and sensory delight of finished truffles and shells. Your perfect match is 'Artisan Chocolate Truffles & Ganache' or the 'Mastering Artisan Bonbons' series, focusing on flavor infusions, stable emulsions, and cocoa painting.";
      break;
    case 'farms':
      title = "🌱 The Cacao Sommelier (Origin & Post-Harvest Sourcing)";
      desc = "You trace chocolate quality back to its roots in the soil. You should focus on 'Bean-to-Bar Foundations', exploring farm-level fermentation boxes, solar drying profiles, and micro-lot roasting parameters.";
      break;
    case 'scaling':
      title = "📈 The Product Innovator (Recipe Scaling & NPD)";
      desc = "You want to bridge kitchen creativity with industry-level commercial production. Your focus should be 'Recipe & Product Development', learning ingredient math, batch conch logs, and production optimization.";
      break;
  }

  resultTitle.textContent = title;
  resultDesc.textContent = desc;
  resultBox.style.display = 'block';
  resultBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}