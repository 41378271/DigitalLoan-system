<?php 
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['borrower','user'])) {
  header("Location: login_page.php");
  exit;
}

require_once "../../backend/config/db.php";

$user_id = (int)$_SESSION['user_id'];

// Latest KYC record
$stmt = $conn->prepare("
  SELECT status, doc_type, uploaded_at, admin_comment
  FROM kyc_documents
  WHERE user_id = ?
  ORDER BY uploaded_at DESC
  LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$kyc = $stmt->get_result()->fetch_assoc();

$kyc_status = $kyc['status'] ?? 'not_uploaded';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Borrower Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body{
      background:#f5f7fb;
      font-family: Arial, sans-serif;
    }

    #splashScreen{
      position: fixed;
      inset: 0;
      background: linear-gradient(135deg, #2415ca, #120a7b);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      color: #fff;
      z-index: 99999;
    }

    .splash-logo{
      width: 90px;
      height: 90px;
      border-radius: 50%;
      background: #fff;
      color: #2415ca;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 34px;
      font-weight: bold;
      margin-bottom: 18px;
      box-shadow: 0 10px 24px rgba(0,0,0,0.2);
      animation: pulse 1.2s infinite;
    }

    .loader{
      width: 42px;
      height: 42px;
      border: 4px solid rgba(255,255,255,0.3);
      border-top: 4px solid #fff;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-top: 18px;
    }

    @keyframes spin { 100% { transform: rotate(360deg); } }
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.08); }
      100% { transform: scale(1); }
    }

    #pageContent{ display:none; }

    .dashboard-card,
    .wallet-card,
    .kyc-card,
    .quick-card{
      border: none;
      border-radius: 18px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    }

    #chatToggleBtn{
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 56px;
      height: 56px;
      border-radius: 50%;
      border: none;
      background: #111;
      color: #fff;
      font-size: 22px;
      cursor: pointer;
      box-shadow: 0 6px 18px rgba(0,0,0,0.25);
      z-index: 9999;
    }

    #chatbotPanel{
      position: fixed;
      bottom: 90px;
      right: 20px;
      width: 340px;
      max-width: calc(100vw - 40px);
      border: 1px solid #ccc;
      border-radius: 12px;
      background: #fff;
      box-shadow: 0 10px 28px rgba(0,0,0,0.2);
      overflow: hidden;
      display: none;
      z-index: 9999;
    }

    #chatHeader{
      padding: 10px 12px;
      background: #2415ca;
      font-weight: bold;
      display:flex;
      align-items:center;
      justify-content: space-between;
      color:#fff;
    }

    #chatCloseBtn{
      border:none;
      background: transparent;
      font-size: 18px;
      cursor:pointer;
      color:#fff;
    }

    #chatBox{
      height: 240px;
      padding: 10px;
      overflow: auto;
      font-size: 14px;
    }

    #chatForm{
      display:flex;
      border-top:1px solid #ddd;
    }

    #chatInput{
      flex:1;
      padding: 10px;
      border:none;
      outline:none;
    }

    #chatSendBtn{
      padding: 10px 14px;
      border:none;
      background:#333;
      color:#fff;
      cursor:pointer;
    }

    .muted{ color:#666; font-size: 13px; }
    .ok{ color: #0a7a3a; font-weight: bold; }
    .bad{ color: #9b1c1c; font-weight: bold; }
  </style>
</head>
<body>

<div id="splashScreen">
  <div class="splash-logo">B</div>
  <h2 class="fw-bold mb-2">Borrower Dashboard</h2>
  <div>Loading your account...</div>
  <div class="loader"></div>
</div>

<div id="pageContent" class="container py-4">
  <div class="card dashboard-card mb-4">
    <div class="card-body p-4">
      <h2 class="fw-bold mb-2">Borrower Dashboard</h2>
      <p class="mb-0 text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Borrower'); ?> ✅</p>
    </div>
  </div>

  <div class="card wallet-card mb-4">
    <div class="card-body p-4">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
          <h4 class="fw-bold mb-1">Wallet</h4>
          <p id="walletMsg" class="mb-0">Loading wallet balance...</p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-dark" onclick="depositWallet()">Deposit</button>
          <button class="btn btn-outline-secondary" onclick="withdrawWallet()">Withdraw</button>
          <button class="btn btn-outline-primary" onclick="loadWallet(true)">Refresh</button>
        </div>
      </div>

      <p class="text-muted mb-2">Recent wallet transactions</p>

      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody id="walletHistoryBody">
            <tr><td colspan="4">Loading history...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card kyc-card mb-4">
    <div class="card-body p-4">
      <h4 class="fw-bold mb-3">KYC Status</h4>

      <?php if ($kyc_status === 'not_uploaded'): ?>
        <p><b>Status:</b> Not uploaded</p>
        <a class="btn btn-primary btn-sm" href="upload_kyc.php">Upload KYC</a>

      <?php elseif ($kyc_status === 'pending'): ?>
        <p><b>Status:</b> Pending review</p>
        <p><small>Uploaded: <?= htmlspecialchars($kyc['uploaded_at'] ?? '') ?></small></p>

      <?php elseif ($kyc_status === 'approved'): ?>
        <p><b>Status:</b> ✅ Approved</p>

      <?php elseif ($kyc_status === 'rejected'): ?>
        <p><b>Status:</b> ❌ Rejected</p>
        <?php if (!empty($kyc['admin_comment'])): ?>
          <p><b>Reason:</b> <?= htmlspecialchars($kyc['admin_comment']) ?></p>
        <?php endif; ?>
        <a class="btn btn-warning btn-sm" href="upload_kyc.php">Re-upload KYC</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-4">
      <div class="card quick-card h-100">
        <div class="card-body">
          <h5 class="fw-bold">Loans</h5>
          <?php if ($kyc_status === 'approved'): ?>
            <a href="apply_loan.php" class="btn btn-primary btn-sm me-2">Apply Loan</a>
          <?php else: ?>
            <button class="btn btn-secondary btn-sm me-2" disabled>Apply Loan</button>
            <div class="text-muted small mt-2">KYC not approved</div>
          <?php endif; ?>
          <a href="my_loans.php" class="btn btn-outline-dark btn-sm mt-2">My Loans</a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-4">
      <div class="card quick-card h-100">
        <div class="card-body">
          <h5 class="fw-bold">Verification</h5>
          <a href="upload_kyc.php" class="btn btn-outline-primary btn-sm">Upload KYC</a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-4">
      <div class="card quick-card h-100">
        <div class="card-body">
          <h5 class="fw-bold">Notifications</h5>
          <a href="notifications.php" class="btn btn-outline-success btn-sm">
            Open Notifications <span id="notifBadge"></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="card quick-card">
    <div class="card-body">
      <h5 class="fw-bold mb-3">Account Actions</h5>
      <div class="d-flex gap-2 flex-wrap">
        <a href="/digital-loan-system/backend/api/auth/logout.php" class="btn btn-dark btn-sm">Logout</a>
        <a href="#" class="btn btn-warning btn-sm" onclick="deactivateAccount(); return false;">Deactivate Account</a>
        <a href="#" class="btn btn-danger btn-sm" onclick="deleteAccount(); return false;">Delete Account</a>
      </div>
    </div>
  </div>

  <!-- Floating chat button -->
  <button id="chatToggleBtn" title="Chat">💬</button>

  <!-- Chat panel -->
  <div id="chatbotPanel">
    <div id="chatHeader">
      <span>Loan Assistant 🤖</span>
      <button id="chatCloseBtn" title="Close">✖</button>
    </div>

    <div id="chatBox">
      <div><b>Bot:</b> Hi! Ask me about KYC, loans, status, or notifications.</div>
    </div>

    <form id="chatForm">
      <input id="chatInput" type="text" placeholder="Type a message..." />
      <button id="chatSendBtn" type="submit">Send</button>
    </form>
  </div>
</div>

<script>
  window.addEventListener("load", function () {
    setTimeout(function () {
      document.getElementById("splashScreen").style.display = "none";
      document.getElementById("pageContent").style.display = "block";
    }, 1200);
  });

  const userName = <?php echo json_encode($_SESSION['name'] ?? 'Borrower'); ?>;

  function fmtMoney(n){
    return Number(n || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
  }

  async function postJSON(url, bodyObj = null){
    const opts = { method: "POST" };
    if (bodyObj){
      const fd = new FormData();
      Object.keys(bodyObj).forEach(k => fd.append(k, bodyObj[k]));
      opts.body = fd;
    }
    const res = await fetch(url, opts);
    const txt = await res.text();
    try { return JSON.parse(txt); }
    catch(e){
      console.error("Non-JSON response:", txt);
      return {success:false, message:"Server error (not JSON). Check API path."};
    }
  }

  async function loadWallet(showAlert = false){
    const msgEl = document.getElementById("walletMsg");

    try{
      const res = await fetch("/digital-loan-system/backend/api/wallet/get_balance.php");
      const data = await res.json();

      if(data.success){
        const currency = data.currency || "KES";
        const bal = fmtMoney(data.balance);
        msgEl.innerHTML = `Hello <b>${userName}</b>, your current balance is <b>${currency} ${bal}</b>`;
        await loadWalletHistory();

        if (showAlert) alert("Wallet updated.");
      }else{
        msgEl.innerHTML = `<span class="bad">${data.message || "Could not load wallet."}</span>`;
        if (showAlert) alert(data.message || "Could not load wallet.");
      }
    }catch(e){
      console.error("Wallet error:", e);
      msgEl.innerHTML = `<span class="bad">Wallet error.</span>`;
      if (showAlert) alert("Wallet error.");
    }
  }

  async function loadWalletHistory(){
    const tbody = document.getElementById("walletHistoryBody");
    tbody.innerHTML = `<tr><td colspan="4">Loading history...</td></tr>`;

    try{
      const res = await fetch("/digital-loan-system/backend/api/wallet/history.php?limit=10");
      const data = await res.json();

      if(!data.success){
        tbody.innerHTML = `<tr><td colspan="4">${data.message || "Could not load history."}</td></tr>`;
        return;
      }

      const txs = Array.isArray(data.transactions) ? data.transactions : [];
      if(!txs.length){
        tbody.innerHTML = `<tr><td colspan="4">No transactions yet.</td></tr>`;
        return;
      }

      tbody.innerHTML = "";
      txs.forEach(t => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${escapeHtml(t.created_at || "")}</td>
          <td>${escapeHtml(t.type || "")}</td>
          <td>${escapeHtml((t.currency || "KES") + " " + fmtMoney(t.amount))}</td>
          <td>${escapeHtml(t.description || "")}</td>
        `;
        tbody.appendChild(tr);
      });
    }catch(e){
      console.error("History error:", e);
      tbody.innerHTML = `<tr><td colspan="4">History error.</td></tr>`;
    }
  }

  async function depositWallet(){
    const amt = prompt("Enter amount to deposit:");
    if(amt === null) return;
    const amount = Number(amt);
    if(!isFinite(amount) || amount <= 0){
      alert("Invalid amount");
      return;
    }

    const data = await postJSON("/digital-loan-system/backend/api/wallet/deposit.php", { amount: amount });
    alert(data.message || (data.success ? "Deposit successful" : "Deposit failed"));

    if(data.success) loadWallet();
  }

  async function withdrawWallet(){
    const amt = prompt("Enter amount to withdraw:");
    if(amt === null) return;
    const amount = Number(amt);
    if(!isFinite(amount) || amount <= 0){
      alert("Invalid amount");
      return;
    }

    const data = await postJSON("/digital-loan-system/backend/api/wallet/withdraw.php", { amount: amount });
    alert(data.message || (data.success ? "Withdrawal successful" : "Withdrawal failed"));

    if(data.success) loadWallet();
  }

  loadWallet();
  setInterval(loadWallet, 15000);

  (async function(){
    try{
      const res = await fetch("/digital-loan-system/backend/api/notifications/unread_count.php");
      const data = await res.json();
      if(data.success && data.count > 0){
        document.getElementById("notifBadge").innerText = `(${data.count})`;
      }
    }catch(e){
      console.error("Notif badge error:", e);
    }
  })();

  async function deactivateAccount(){
    const ok = confirm("Deactivate your account? You will be logged out and cannot login until reactivated.");
    if(!ok) return;

    const data = await postJSON("/digital-loan-system/backend/api/auth/deactivate_account.php");
    alert(data.message);

    if(data.success){
      window.location.href = "login_page.php";
    }
  }

  async function deleteAccount(){
    const ok = confirm("DELETE your account permanently? This will remove your loans and KYC data. This cannot be undone.");
    if(!ok) return;

    const data = await postJSON("/digital-loan-system/backend/api/auth/delete_account.php");
    alert(data.message);

    if(data.success){
      window.location.href = "login_page.php";
    }
  }

  (function(){
    const chatToggleBtn = document.getElementById("chatToggleBtn");
    const chatbotPanel  = document.getElementById("chatbotPanel");
    const chatCloseBtn  = document.getElementById("chatCloseBtn");

    const chatBox   = document.getElementById("chatBox");
    const chatForm  = document.getElementById("chatForm");
    const chatInput = document.getElementById("chatInput");

    function openChat(){
      chatbotPanel.style.display = "block";
      chatInput.focus();
    }
    function closeChat(){
      chatbotPanel.style.display = "none";
    }

    chatToggleBtn.addEventListener("click", () => {
      if (chatbotPanel.style.display === "block") closeChat();
      else openChat();
    });

    chatCloseBtn.addEventListener("click", closeChat);

    function addMsg(who, text){
      const div = document.createElement("div");
      div.style.margin = "6px 0";
      div.innerHTML = `<b>${who}:</b> ${text}`;
      chatBox.appendChild(div);
      chatBox.scrollTop = chatBox.scrollHeight;
    }

    chatForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const msg = chatInput.value.trim();
      if(!msg) return;

      addMsg("You", msg);
      chatInput.value = "";

      try{
        const res = await fetch("/digital-loan-system/backend/api/chatbot/respond.php", {
          method: "POST",
          headers: {"Content-Type":"application/x-www-form-urlencoded"},
          body: "message=" + encodeURIComponent(msg)
        });
        const data = await res.json();
        addMsg("Bot", data.reply || "No reply.");
      }catch(err){
        console.error("Chatbot error:", err);
        addMsg("Bot", "Sorry, I’m having trouble right now.");
      }
    });
  })();

  function escapeHtml(s){
    return String(s ?? "")
      .replaceAll("&","&amp;")
      .replaceAll("<","&lt;")
      .replaceAll(">","&gt;")
      .replaceAll('"',"&quot;")
      .replaceAll("'","&#039;");
  }
</script>

</body>
</html>