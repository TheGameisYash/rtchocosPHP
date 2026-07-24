<?php
// AI Chat Drawer Component
?>
<!-- AI Chat Drawer -->
<div id="ai-chat-drawer" class="ai-drawer-container">
  <div class="ai-drawer-header">
    <div class="ai-drawer-title">
      <span class="ai-glowing-dot"></span>
      <h3>CocoaGenius AI</h3>
    </div>
    <button class="ai-drawer-close" onclick="toggleAiDrawer()">&times;</button>
  </div>
  <div class="ai-drawer-body" id="ai-chat-messages">
    <div class="ai-message system">
      <div class="ai-msg-bubble">
        Hello! I am <strong>CocoaGenius AI</strong>, your expert guide to the science, craft, and chemistry of chocolate making. How can I help you today?
      </div>
    </div>
  </div>
  
  <div class="ai-prompt-chips">
    <button class="ai-chip" onclick="sendQuickPrompt('Explain chocolate tempering science')">🔬 Tempering Science</button>
    <button class="ai-chip" onclick="sendQuickPrompt('Why is my chocolate blooming?')">🫘 Bloom Diagnosis</button>
    <button class="ai-chip" onclick="sendQuickPrompt('What is the difference between Criollo and Forastero?')">🍫 Cacao Varieties</button>
    <button class="ai-chip" onclick="sendQuickPrompt('Who runs RT Chocos and who developed this website?')">💻 Founder & Developer</button>
  </div>

  <div class="ai-drawer-footer">
    <form id="ai-chat-form" onsubmit="handleAiChatSubmit(event)">
      <input type="text" id="ai-chat-input" placeholder="Ask about tempering, roasting, recipes..." required autocomplete="off">
      <button type="submit" aria-label="Send message">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="22" y1="2" x2="11" y2="13"></line>
          <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
        </svg>
      </button>
    </form>
  </div>
</div>
<div id="ai-drawer-overlay" onclick="toggleAiDrawer()"></div>
