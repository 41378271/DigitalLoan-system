<!-- ✅ Floating Chat Button -->
<div id="chatFab" style="
  position:fixed; bottom:20px; right:20px; width:54px; height:54px;
  border-radius:50%; background:#111; color:#fff; display:flex;
  align-items:center; justify-content:center; cursor:pointer;
  box-shadow:0 10px 25px rgba(0,0,0,.25);
  z-index:9999; user-select:none;
">
  💬
  <span id="chatBadge" style="
    position:absolute; top:-6px; right:-6px;
    min-width:18px; height:18px; padding:0 5px;
    border-radius:999px; background:#e11d48; color:#fff;
    font-size:12px; display:none; align-items:center; justify-content:center;
  ">0</span>
</div>

<!-- ✅ Chat Window -->
<div id="chatbot" style="
  position:fixed; bottom:84px; right:20px; width:340px; max-width:92vw;
  border-radius:16px; background:#fff; overflow:hidden;
  box-shadow:0 20px 40px rgba(0,0,0,.18);
  font-family:Arial; z-index:9999;
  transform: translateY(12px); opacity:0; pointer-events:none;
  transition: all .22s ease;
">
  <!-- Header (draggable) -->
  <div id="chatHeader" style="
    padding:10px 12px; background:#111; color:#fff;
    display:flex; align-items:center; justify-content:space-between;
    cursor:grab;
  ">
    <div style="display:flex; align-items:center; gap:10px;">
      <div style="width:28px;height:28px;border-radius:50%;background:#fff;color:#111;display:flex;align-items:center;justify-content:center;font-weight:bold;">
        🤖
      </div>
      <div>
        <div style="font-weight:bold; line-height:1;">Loan Assistant</div>
        <div id="typingMini" style="font-size:12px; opacity:.85; display:none;">typing…</div>
      </div>
    </div>

    <div style="display:flex; gap:8px; align-items:center;">
      <button type="button" id="themeBtn" title="Toggle theme" style="border:none;background:rgba(255,255,255,.12);color:#fff;padding:6px 10px;border-radius:10px;cursor:pointer;">🌓</button>
      <button type="button" id="chatClose" title="Close" style="border:none;background:rgba(255,255,255,.12);color:#fff;padding:6px 10px;border-radius:10px;cursor:pointer;">✕</button>
    </div>
  </div>

  <!-- Body -->
  <div id="chatBody" style="background:#f5f5f5;">
    <div id="chatBox" style="height:320px; padding:12px; overflow:auto; font-size:14px;"></div>

    <div id="suggestWrap" style="padding:0 12px 10px; display:flex; flex-wrap:wrap; gap:8px;"></div>

    <form id="chatForm" style="display:flex; border-top:1px solid #ddd; background:#fff;">
      <input id="chatInput" type="text" placeholder="Type a message..." style="flex:1; padding:12px; border:none; outline:none;">
      <button type="submit" style="padding:12px 14px; border:none; background:#111; color:#fff; cursor:pointer;">Send</button>
    </form>
  </div>
</div>

<script>
(function(){
  const fab = document.getElementById("chatFab");
  const badge = document.getElementById("chatBadge");
  const win = document.getElementById("chatbot");
  const closeBtn = document.getElementById("chatClose");
  const header = document.getElementById("chatHeader");
  const themeBtn = document.getElementById("themeBtn");

  const chatBox = document.getElementById("chatBox");
  const chatForm = document.getElementById("chatForm");
  const chatInput = document.getElementById("chatInput");
  const typingMini = document.getElementById("typingMini");
  const suggestWrap = document.getElementById("suggestWrap");

  const API_BASE = "/digital-loan-system/backend/api/chatbot";
  let isOpen = false;

  // ---------- Theme ----------
  const THEME_KEY = "chatTheme";
  function applyTheme(theme){
    if(theme === "dark"){
      win.style.background = "#0b0b0b";
      document.getElementById("chatBody").style.background = "#0f0f0f";
      chatBox.style.background = "#0f0f0f";
      chatBox.style.color = "#eaeaea";
      win.dataset.theme = "dark";
    } else {
      win.style.background = "#fff";
      document.getElementById("chatBody").style.background = "#f5f5f5";
      chatBox.style.background = "transparent";
      chatBox.style.color = "#111";
      win.dataset.theme = "light";
    }
  }
  const savedTheme = localStorage.getItem(THEME_KEY) || "light";
  applyTheme(savedTheme);

  themeBtn.addEventListener("click", () => {
    const t = (win.dataset.theme === "dark") ? "light" : "dark";
    localStorage.setItem(THEME_KEY, t);
    applyTheme(t);
  });

  // ---------- Helpers ----------
  function escapeHtml(str){
    return String(str)
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
      return `<a href="${safeUrl}" style="color:#2563eb; text-decoration:underline;">${safeLabel}</a>`;
    });
  }

  function addBubble(role, text){
    const row = document.createElement("div");
    row.style.display = "flex";
    row.style.margin = "8px 0";
    row.style.justifyContent = (role === "You") ? "flex-end" : "flex-start";

    const bubble = document.createElement("div");
    bubble.style.maxWidth = "78%";
    bubble.style.padding = "10px 12px";
    bubble.style.borderRadius = "14px";
    bubble.style.lineHeight = "1.35";
    bubble.style.boxShadow = "0 6px 14px rgba(0,0,0,.06)";
    bubble.style.whiteSpace = "normal";

    if(role === "You"){
      bubble.style.background = "#111";
      bubble.style.color = "#fff";
      bubble.style.borderBottomRightRadius = "6px";
    }else{
      bubble.style.background = (win.dataset.theme === "dark") ? "#1b1b1b" : "#fff";
      bubble.style.color = (win.dataset.theme === "dark") ? "#eaeaea" : "#111";
      bubble.style.borderBottomLeftRadius = "6px";
    }

    const safe = escapeHtml(text).replaceAll("\n","<br>");
    bubble.innerHTML = renderLinks(safe);

    row.appendChild(bubble);
    chatBox.appendChild(row);
    chatBox.scrollTop = chatBox.scrollHeight;
  }

  function showTyping(){
    typingMini.style.display = "block";
    // typing bubble
    const row = document.createElement("div");
    row.id = "typingRow";
    row.style.display = "flex";
    row.style.margin = "8px 0";
    row.style.justifyContent = "flex-start";

    const bubble = document.createElement("div");
    bubble.style.background = (win.dataset.theme === "dark") ? "#1b1b1b" : "#fff";
    bubble.style.color = (win.dataset.theme === "dark") ? "#eaeaea" : "#111";
    bubble.style.padding = "10px 12px";
    bubble.style.borderRadius = "14px";
    bubble.style.borderBottomLeftRadius = "6px";
    bubble.style.boxShadow = "0 6px 14px rgba(0,0,0,.06)";
    bubble.innerHTML = `typing <span class="dots">...</span>`;
    row.appendChild(bubble);

    chatBox.appendChild(row);
    chatBox.scrollTop = chatBox.scrollHeight;
  }

  function hideTyping(){
    typingMini.style.display = "none";
    const row = document.getElementById("typingRow");
    if(row) row.remove();
  }

  function setSuggestions(items){
    suggestWrap.innerHTML = "";
    if(!items || !items.length) return;
    items.slice(0,4).forEach(s => {
      const b = document.createElement("button");
      b.type = "button";
      b.textContent = s;
      b.style.padding = "8px 10px";
      b.style.borderRadius = "999px";
      b.style.border = "1px solid #ddd";
      b.style.background = (win.dataset.theme === "dark") ? "#151515" : "#fff";
      b.style.color = (win.dataset.theme === "dark") ? "#eaeaea" : "#111";
      b.style.cursor = "pointer";
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
        badge.style.display = "flex";
        badge.textContent = data.count;
      } else {
        badge.style.display = "none";
      }
    }catch(e){}
  }

  async function markRead(){
    try{ await fetch(API_BASE + "/mark_read.php"); }catch(e){}
    await refreshUnreadBadge();
  }

  async function loadHistory(){
    chatBox.innerHTML = "";
    try{
      const res = await fetch(API_BASE + "/history.php");
      const data = await res.json();
      if(data.success){
        data.messages.forEach(m => {
          addBubble(m.role === "user" ? "You" : "Bot", m.message);
        });
      } else {
        addBubble("Bot", "Hi! Ask me about KYC, loans, status, or notifications.");
      }
    }catch(e){
      addBubble("Bot", "Hi! Ask me about KYC, loans, status, or notifications.");
    }
    setSuggestions(["Upload KYC","Loan status","Notifications","Calculate loan 50000 12 15%"]);
  }

  // ---------- Open/Close ----------
  function openChat(){
    isOpen = true;
    win.style.opacity = "1";
    win.style.transform = "translateY(0)";
    win.style.pointerEvents = "auto";
    loadHistory().then(markRead);
  }

  function closeChat(){
    isOpen = false;
    win.style.opacity = "0";
    win.style.transform = "translateY(12px)";
    win.style.pointerEvents = "none";
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
      addBubble("Bot", data.reply || "No reply.");
      setSuggestions(data.suggestions || []);
      if(!isOpen) refreshUnreadBadge();
      else markRead();
    }catch(err){
      hideTyping();
      addBubble("Bot", "Sorry, I’m having trouble right now.");
    }
  });

  // ---------- Draggable ----------
  let drag = {on:false, x:0, y:0, startX:0, startY:0};
  header.addEventListener("pointerdown", (e) => {
    drag.on = true;
    header.style.cursor = "grabbing";
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
    let nx = drag.x + dx;
    let ny = drag.y + dy;

    // keep inside screen
    nx = Math.max(8, Math.min(nx, window.innerWidth - win.offsetWidth - 8));
    ny = Math.max(8, Math.min(ny, window.innerHeight - win.offsetHeight - 8));

    win.style.left = nx + "px";
    win.style.top = ny + "px";
    win.style.right = "auto";
    win.style.bottom = "auto";
    win.style.position = "fixed";
  });

  header.addEventListener("pointerup", (e) => {
    drag.on = false;
    header.style.cursor = "grab";
  });

  // initial unread badge
  refreshUnreadBadge();
  // poll occasionally (optional)
  setInterval(refreshUnreadBadge, 5000);
})();
</script>