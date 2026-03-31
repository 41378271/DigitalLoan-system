<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login_page.php");
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Notifications</title>
</head>
<body>
  <h2>Notifications</h2>
  <p><a href="borrower_dashboard.php">← Back to Dashboard</a></p>

  <div id="list"></div>

  <script>
  async function loadNotifs(){
    const res = await fetch("/digital-loan-system/backend/api/notifications/list.php");
    const data = await res.json();

    const el = document.getElementById("list");
    el.innerHTML = "";

    if(!data.success){
      el.innerText = data.message || "Failed to load.";
      return;
    }

    if(data.rows.length === 0){
      el.innerHTML = "<p>No notifications yet.</p>";
      return;
    }

    data.rows.forEach(n => {
      const box = document.createElement("div");
      box.style.border = "1px solid #ccc";
      box.style.padding = "10px";
      box.style.margin = "10px 0";

      box.innerHTML = `
        <b>${n.title}</b> ${n.is_read == 0 ? "<span style='color:red'>(NEW)</span>" : ""}
        <p>${n.message}</p>
        <small>${n.created_at}</small><br>
        ${n.is_read == 0 ? `<button onclick="markRead(${n.id})">Mark as read</button>` : ""}
      `;
      el.appendChild(box);
    });
  }

  async function markRead(id){
    const res = await fetch("/digital-loan-system/backend/api/notifications/mark_read.php", {
      method:"POST",
      headers: {"Content-Type":"application/x-www-form-urlencoded"},
      body: "id=" + encodeURIComponent(id)
    });
    await res.json();
    loadNotifs();
  }

  loadNotifs();
  </script>
  <?php include "../partials/chatbot_widget.php"; ?>
</body>
</html>