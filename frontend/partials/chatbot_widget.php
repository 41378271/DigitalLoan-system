<!-- ✅ Floating Chat Button -->
<div id="chatFab" class="fixed bottom-6 right-6 w-14 h-14 bg-brand-600 hover:bg-brand-700 text-white rounded-full flex items-center justify-center cursor-pointer shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 z-50">
  <i data-lucide="message-circle" class="w-6 h-6"></i>
  <span id="chatBadge" class="absolute -top-1 -right-1 min-w-[20px] h-[20px] px-1.5 rounded-full bg-red-500 text-white text-[10px] font-bold hidden items-center justify-center border-2 border-white shadow-sm ring-2 ring-red-500/20 animate-pulse">0</span>
</div>

<!-- ✅ Chat Window -->
<div id="chatbot" class="fixed bottom-24 right-6 w-[360px] max-w-[calc(100vw-48px)] bg-white rounded-2xl shadow-2xl overflow-hidden z-50 transform translate-y-4 opacity-0 pointer-events-none transition-all duration-300 flex flex-col border border-gray-100" style="height: 500px;">
  
  <!-- Header (draggable) -->
  <div id="chatHeader" class="bg-brand-600 text-white p-4 flex items-center justify-between cursor-grab active:cursor-grabbing border-b border-brand-700 shrink-0">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center shrink-0 border border-white/30 backdrop-blur-sm">
        <i data-lucide="bot" class="w-5 h-5 text-white"></i>
      </div>
      <div>
        <div class="font-bold text-sm leading-tight text-white mb-0.5">KashFlow Assistant</div>
        <div id="typingMini" class="text-xs text-brand-100 hidden animate-pulse font-medium">Typing...</div>
        <div id="statusMini" class="text-xs text-brand-100 font-medium flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Online</div>
      </div>
    </div>

    <div class="flex gap-2 items-center">
      <button type="button" id="chatClose" title="Close chat" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors text-white">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>
  </div>

  <!-- Body -->
  <div id="chatBody" class="flex-1 overflow-y-auto bg-gray-50 flex flex-col p-4 space-y-4 relative scroll-smooth">
      <!-- Default Greeting gets injected here -->
      <div id="chatBox" class="flex flex-col space-y-4 pb-2"></div>
      
      <!-- Typing Indicator Row -->
      <div id="typingRow" class="hidden my-2 justify-start">
          <div class="bg-white border border-gray-100 rounded-2xl rounded-bl-sm py-3 px-4 shadow-sm inline-flex items-center gap-1">
              <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:-0.3s"></span>
              <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:-0.15s"></span>
              <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"></span>
          </div>
      </div>
  </div>

  <!-- Suggestions -->
  <div id="suggestWrap" class="px-4 py-3 bg-gray-50 border-t border-gray-100 shrink-0 flex flex-wrap gap-2 overflow-x-auto whitespace-nowrap scrollbar-hide empty:hidden">
      <!-- Suggestions injected here -->
  </div>

  <!-- Input Area -->
  <div class="bg-white p-3 pt-0 shrink-0 border-t border-gray-100 mt-auto">
      <form id="chatForm" class="flex items-center gap-2 mt-3">
        <div class="relative flex-1">
            <input id="chatInput" type="text" placeholder="Type your message..." class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors placeholder-gray-400">
        </div>
        <button type="submit" class="w-10 h-10 rounded-xl bg-brand-600 hover:bg-brand-700 text-white flex items-center justify-center shrink-0 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
            <i data-lucide="send" class="w-4 h-4 ml-0.5"></i>
        </button>
      </form>
  </div>
</div>

<script>
(function(){
  // Only inject if not already injected (prevent duplicate IDs if included multiple times)
  if(window._chatbotInitialized) return;
  window._chatbotInitialized = true;

  const fab = document.getElementById("chatFab");
  const badge = document.getElementById("chatBadge");
  const win = document.getElementById("chatbot");
  const closeBtn = document.getElementById("chatClose");
  const header = document.getElementById("chatHeader");

  const chatBody = document.getElementById("chatBody");
  const chatBox = document.getElementById("chatBox");
  const chatForm = document.getElementById("chatForm");
  const chatInput = document.getElementById("chatInput");
  const typingMini = document.getElementById("typingMini");
  const statusMini = document.getElementById("statusMini");
  const typingRow = document.getElementById("typingRow");
  const suggestWrap = document.getElementById("suggestWrap");
  const submitBtn = chatForm.querySelector('button[type="submit"]');

  // API Route - Keep existing setup since widget can be included from anywhere
  const API_BASE = "<?= $basePath ?? '' ?>/backend/api/chatbot";
  let isOpen = false;
  let isDragging = false;
  let lastMessageId = 0;

  // Make sure Lucide icons in the widget are loaded
  if(typeof lucide !== 'undefined') {
      lucide.createIcons();
  }

  // ---------- Helpers ----------
  function escapeHtml(str){
    return String(str || "")
      .replaceAll("&","&amp;")
      .replaceAll("<","&lt;")
      .replaceAll(">","&gt;")
      .replaceAll('"',"&quot;")
      .replaceAll("'","&#039;");
  }

  function renderLinks(text){
    // Convert [LINK:Label|/url] into <a>
    return String(text).replace(/\[LINK:([^\|\]]+)\|([^\]]+)\]/g, (m, label, url) => {
      const safeLabel = escapeHtml(label);
      const safeUrl = escapeHtml(url);
      return `<a href="${safeUrl}" class="font-medium text-brand-600 hover:text-brand-700 underline underline-offset-2 transition-colors inline-flex items-center gap-0.5">${safeLabel} <i data-lucide="external-link" class="w-3 h-3"></i></a>`;
    });
  }

  function scrollToBottom() {
      chatBody.scrollTop = chatBody.scrollHeight;
  }

  function addBubble(role, text){
    const row = document.createElement("div");
    row.className = role === "You" ? "flex justify-end w-full animate-in fade-in slide-in-from-bottom-2 duration-300" : "flex justify-start w-full animate-in fade-in slide-in-from-bottom-2 duration-300";
    
    const bubble = document.createElement("div");
    bubble.className = "max-w-[75%] px-4 py-2.5 text-[13.5px] leading-relaxed relative group";
    
    if(role === "You"){
      bubble.classList.add("bg-brand-600", "text-white", "rounded-2xl", "rounded-tr-sm", "shadow-sm");
    } else {
      bubble.classList.add("bg-white", "border", "border-gray-100", "text-gray-800", "rounded-2xl", "rounded-tl-sm", "shadow-sm");
    }

    const safe = escapeHtml(text).replaceAll("\n","<br>");
    bubble.innerHTML = renderLinks(safe);

    row.appendChild(bubble);
    
    // Insert before typing indicator if it exists
    if(typingRow.parentNode === chatBox) {
        chatBox.insertBefore(row, typingRow);
    } else {
        chatBox.appendChild(row);
    }
    
    if(typeof lucide !== 'undefined') lucide.createIcons({root: bubble});
    scrollToBottom();
  }

  function showTyping(){
    typingMini.classList.remove("hidden");
    statusMini.classList.add("hidden");
    typingRow.classList.remove("hidden");
    typingRow.classList.add("flex");
    chatBox.appendChild(typingRow); // Move to bottom
    scrollToBottom();
    submitBtn.disabled = true;
  }

  function hideTyping(){
    typingMini.classList.add("hidden");
    statusMini.classList.remove("hidden");
    typingRow.classList.remove("flex");
    typingRow.classList.add("hidden");
    submitBtn.disabled = false;
  }

  function setSuggestions(items){
    suggestWrap.innerHTML = "";
    if(!items || !items.length) {
        suggestWrap.classList.add("hidden");
        return;
    }
    
    suggestWrap.classList.remove("hidden");
    items.slice(0,4).forEach(s => {
      const b = document.createElement("button");
      b.type = "button";
      b.textContent = s;
      b.className = "px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-[13px] font-medium rounded-full shadow-sm hover:bg-gray-50 hover:border-gray-300 transition-all flex-none";
      b.onclick = () => {
        chatInput.value = s;
        chatForm.requestSubmit();
      };
      suggestWrap.appendChild(b);
    });
  }

  async function refreshUnreadBadge(){
    try{
      const res = await fetch(API_BASE + "/unread_count.php");
      const data = await res.json();
      if(data.success && data.count > 0){
        badge.classList.remove("hidden");
        badge.classList.add("flex");
        badge.textContent = data.count > 9 ? "9+" : data.count;
      } else {
        badge.classList.add("hidden");
        badge.classList.remove("flex");
      }
    }catch(e){}
  }

  async function markRead(){
    try{ await fetch(API_BASE + "/mark_read.php"); }catch(e){}
    await refreshUnreadBadge();
  }

  async function loadHistory(){
    chatBox.innerHTML = ""; // Clear existing
    chatBox.appendChild(typingRow); // Keep typing row in tree
    
    try{
      const res = await fetch(API_BASE + "/history.php");
      const data = await res.json();
      
      if(data.success && data.messages && data.messages.length > 0){
        data.messages.forEach(m => {
          addBubble(m.role === "user" ? "You" : "Bot", m.message);
        });
      } else {
        addBubble("Bot", "Hi there! 👋 I'm your KashFlow AI assistant. I can help you with:\n\n• Checking KYC requirements\n• Estimating loan terms\n• Application status updates\n\nHow can I help you today?");
      }
    }catch(e){
      addBubble("Bot", "Hi! Ask me about KYC, loans, status, or notifications.");
    }
    setSuggestions(["Calculate loan for 50k", "What is KYC?", "Check my loan status", "Recent notifications"]);
  }

  // ---------- Open/Close ----------
  function openChat(){
    if(isDragging) return;
    
    isOpen = true;
    win.classList.remove("opacity-0", "translate-y-4", "pointer-events-none");
    fab.classList.add("scale-0"); // Hide FAB beautifully
    
    loadHistory().then(markRead);
    setTimeout(() => chatInput.focus(), 300);
  }

  function closeChat(){
    isOpen = false;
    win.classList.add("opacity-0", "translate-y-4", "pointer-events-none");
    fab.classList.remove("scale-0"); // Show FAB
  }

  fab.addEventListener("click", () => {
    if(isOpen) closeChat();
    else openChat();
  });

  closeBtn.addEventListener("click", closeChat);

  // ---------- Send message ----------
  chatForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const msg = chatInput.value.trim();
    if(!msg) return;

    addBubble("You", msg);
    chatInput.value = "";
    setSuggestions([]);

    showTyping();
    try{
      const res = await fetch(API_BASE + "/respond.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: "message=" + encodeURIComponent(msg)
      });

      const data = await res.json();
      hideTyping();
      addBubble("Bot", data.reply || "I didn't quite catch that.");
      setSuggestions(data.suggestions || ["Help me"]);
      if(!isOpen) refreshUnreadBadge();
      else markRead();
    }catch(err){
      hideTyping();
      addBubble("Bot", "Sorry, my AI connection is currently interrupted. Please try again later.");
    }
  });

  // ---------- Draggable ----------
  let drag = {on:false, x:0, y:0, startX:0, startY:0, moved: false};
  
  header.addEventListener("pointerdown", (e) => {
    // Don't drag if clicking buttons
    if(e.target.closest('button')) return;
    
    drag.on = true;
    drag.moved = false;
    header.classList.add("cursor-grabbing");
    drag.startX = e.clientX;
    drag.startY = e.clientY;
    
    const rect = win.getBoundingClientRect();
    drag.x = rect.left;
    drag.y = rect.top;
    
    header.setPointerCapture(e.pointerId);
  });

  header.addEventListener("pointermove", (e) => {
    if(!drag.on) return;
    
    const dx = e.clientX - drag.startX;
    const dy = e.clientY - drag.startY;
    
    if(Math.abs(dx) > 5 || Math.abs(dy) > 5) {
        drag.moved = true;
        isDragging = true;
    }
    
    if(!drag.moved) return;

    let nx = drag.x + dx;
    let ny = drag.y + dy;

    // keep inside screen bounds
    nx = Math.max(16, Math.min(nx, window.innerWidth - win.offsetWidth - 16));
    ny = Math.max(16, Math.min(ny, window.innerHeight - win.offsetHeight - 16));

    win.style.left = nx + "px";
    win.style.top = ny + "px";
    win.style.right = "auto";
    win.style.bottom = "auto";
    win.style.margin = "0"; // Reset any margins
    win.style.transform = "none"; // Reset transform to avoid layout jump
  });

  header.addEventListener("pointerup", (e) => {
    drag.on = false;
    header.classList.remove("cursor-grabbing");
    header.releasePointerCapture(e.pointerId);
    
    // reset dragging state slightly delayed so click handlers can see it
    setTimeout(() => { isDragging = false; }, 50);
  });

  // Initial poll
  refreshUnreadBadge();
  setInterval(refreshUnreadBadge, 15000);
})();
</script>