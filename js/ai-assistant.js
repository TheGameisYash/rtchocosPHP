// --- AI CHAT DRAWER STATE & FUNCTIONS ---
let aiChatHistory = [];

function toggleAiDrawer() {
  const drawer = document.getElementById('ai-chat-drawer');
  const overlay = document.getElementById('ai-drawer-overlay');
  if (drawer && overlay) {
    const isOpen = drawer.classList.toggle('open');
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

// --- AI CHOCOLAB FORMULATION ---
async function generateCustomBarFormula() {
  const base = document.getElementById('chocolab-base').value;
  const percent = document.getElementById('chocolab-percent').value;
  const checkboxes = document.querySelectorAll('input[name="inclusions"]:checked');

  const inclusions = Array.from(checkboxes).map(cb => cb.value);
  if (inclusions.length > 3) {
    alert("Please select a maximum of 3 inclusions for your chocolate bar formulation.");
    return;
  }

  const placeholder = document.getElementById('chocolab-placeholder');
  const loader = document.getElementById('chocolab-loader');
  const results = document.getElementById('chocolab-results');

  if (!placeholder || !loader || !results) return;

  placeholder.style.display = 'none';
  results.style.display = 'none';
  loader.style.display = 'block';

  const inclusionsText = inclusions.length > 0 ? inclusions.join(', ') : 'no extra inclusions';
  const prompt = `Formulate a detailed professional recipe profile for a custom chocolate bar:
Base: ${base}
Cacao percentage: ${percent}%
Inclusions: ${inclusionsText}

You must return the response in exactly this format, using pipe characters '|' to separate the sections. Do not include any markdown bold stars in the section separators. Use exactly 4 sections:
Bar Name | Short mouthwatering description under 40 words | Professional tasting notes (describing acidity, sweetness, bitterness, texture in detail) | Step-by-step professional tempering temperatures and conching notes for this specific bar.

Format example:
The Spice Route | A rich dark chocolate bar with cardamom and sea salt... | Tasting Notes detailed text... | Tempering Guide detailed text...`;

  try {
    const isBlogSubfolder = window.location.pathname.includes('/blog/');
    const prefix = isBlogSubfolder ? '../' : '';

    const response = await fetch(prefix + 'api_ai.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        message: prompt
      })
    });

    loader.style.display = 'none';

    if (response.ok) {
      const data = await response.json();
      if (data.reply) {
        const parts = data.reply.trim().split('|');
        if (parts.length >= 4) {
          document.getElementById('chocolab-result-base').textContent = `${percent}% ${base}`;
          document.getElementById('chocolab-result-name').textContent = parts[0].trim();
          document.getElementById('chocolab-result-desc').textContent = parts[1].trim();
          document.getElementById('chocolab-result-tasting').innerHTML = parts[2].trim().replace(/\n/g, '<br>');
          document.getElementById('chocolab-result-tempering').innerHTML = parts[3].trim().replace(/\n/g, '<br>');

          results.style.display = 'block';
        } else {
          document.getElementById('chocolab-result-base').textContent = `${percent}% ${base}`;
          document.getElementById('chocolab-result-name').textContent = "Custom Formulation";
          document.getElementById('chocolab-result-desc').textContent = "Formulation completed successfully.";
          document.getElementById('chocolab-result-tasting').innerHTML = "Tasting Notes:<br>" + data.reply.replace(/\n/g, '<br>');
          document.getElementById('chocolab-result-tempering').innerHTML = "Refer to the CocoaGenius assistant for complete guide.";
          results.style.display = 'block';
        }
      } else {
        placeholder.style.display = 'block';
        alert("Failed to parse AI formulation response.");
      }
    } else {
      placeholder.style.display = 'block';
      alert("Error contacting the AI Alchemist.");
    }
  } catch (err) {
    loader.style.display = 'none';
    placeholder.style.display = 'block';
    console.error("AI Chocolab formulation error:", err);
    alert("Connection error formulating recipe.");
  }
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